<?php

/*
 * This file is part of the TYPO3 project.
 * (c) 2022 B-Factor GmbH
 *          Sudhaus7
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 * @copyright 2022 B-Factor GmbH https://b-factor.de/
 * @author Frank Berger <fberger@b-factor.de>
 * @author Daniel Simon <dsimon@b-factor.de>
 */

namespace ArbkomEKvW\Evangtermine\Command;

use ArbkomEKvW\Evangtermine\Domain\Model\Categorylist;
use ArbkomEKvW\Evangtermine\Domain\Model\Eventcontainer;
use ArbkomEKvW\Evangtermine\Domain\Model\Grouplist;
use ArbkomEKvW\Evangtermine\Util\FieldMapping;
use ArbkomEKvW\Evangtermine\Util\Fields;
use ArbkomEKvW\Evangtermine\Util\UrlUtility;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\ExistingTargetFolderException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderWritePermissionsException;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImportEventsCommand extends Command
{
    protected ConnectionPool $connectionPool;
    protected RequestFactory $requestFactory;
    protected array $categoryList;
    protected array $groupList;
    protected DataHandler $dataHandler;
    protected StorageRepository $storageRepository;
    protected ResourceStorage $storage;
    protected array $extConfig;
    protected string $imageFolder;

    /**
     * @throws ExistingTargetFolderException
     * @throws InsufficientFolderAccessPermissionsException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws InsufficientFolderWritePermissionsException
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $this->requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $this->categoryList = GeneralUtility::makeInstance(Categorylist::class)->getItemslist();
        $this->groupList = GeneralUtility::makeInstance(Grouplist::class)->getItemslist();
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $this->storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $this->storage = $this->storageRepository->getDefaultStorage();
        $this->extConfig  = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('evangtermine');
        $this->imageFolder = $this->extConfig['imageFolder'];
        // create image folder if not there yet
        try {
            $this->storage->getFolder($this->imageFolder);
        } catch (\Exception $e) {
            $this->storage->createFolder($this->imageFolder);
        }
    }

    public function configure()
    {
        $this->setDescription('Add pattern to a set of reports')
             ->setHelp('vendor/bin/typo3 evangtermine:importevents');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importAllEvents($output);

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @throws DBALException
     * @throws Exception
     */
    protected function importAllEvents(OutputInterface $output)
    {
        $items = $this->getItems();

        $this->deleteEvents($items);

        $progressBar = new ProgressBar($output, count($items));

        foreach ($items as $item) {
            $item = (array)$item;

            if (str_contains($item['END'], '0000-00-00')) {
                $startArray = explode(' ', $item['START']);
                if (str_contains($item['END'], '0000-00-00 00:00:00')) {
                    $item['END'] = null;
                } else {
                    $endArray = explode(' ', $item['END']);
                    $item['END'] = $startArray[0] . ' ' . $endArray[1];
                }
            }

            $event = [
                'pid' => 0,
                'tstamp' => time(),
                'crdate' => time(),
                'id' => $item['ID'] ?? 0,
                'start' => \DateTime::createFromFormat('Y-m-d H:i:s', $item['START'])->getTimestamp(),
                'end' => !empty($item['END']) ? \DateTime::createFromFormat('Y-m-d H:i:s', $item['END'])->getTimestamp() : 0,
            ];

            /** @var FieldMapping $fieldMapping */
            $fieldMapping = GeneralUtility::makeInstance(FieldMapping::class);
            $fields = $fieldMapping->getFields();

            foreach ($fields as $key => $field) {
                $value = $item[$field];
                if (!empty($value)) {
                    switch ($field) {
                        case '_event_EVENTTYPE':
                            $event[$key] =  $this->setCategories($value);
                            break;
                        case '_event_PEOPLE':
                            $event[$key] =  $this->setPeople($value);
                            break;
                        case '_event_HIGHLIGHT':
                            $event[$key] =  $this->setHighlight($value);
                            break;
                        default:
                            $event[$key] = $value;
                            break;
                    }
                }
            }

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
            $count = $queryBuilder->count('uid')
                ->from('tx_evangtermine_domain_model_event')
                ->where(
                    $queryBuilder->expr()->eq('id', $queryBuilder->createNamedParameter($event['id']))
                )
                ->executeQuery()
                ->fetchOne();

            if ($count > 0) {
                $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
                $queryBuilder->update('tx_evangtermine_domain_model_event')
                    ->where(
                        $queryBuilder->expr()->eq('id', $queryBuilder->createNamedParameter($event['id']))
                    );
                foreach ($event as $key => $eventItem) {
                    if ($key == 'id' || $key == 'image') {
                        continue;
                    }
                    $queryBuilder->set($key, $eventItem);
                }
                $queryBuilder->executeStatement();

            } else {
                $this->connectionPool->getConnectionForTable('tx_evangtermine_domain_model_event')
                    ->insert(
                        'tx_evangtermine_domain_model_event',
                        $event
                    );
            }

            $this->insertImage($event, $item['_event_IMAGE'], 'image');
            $this->insertImage($event, $item['_place_IMAGE'], 'place_image');
            $this->insertImage($event, $item['_user_IMAGE'], 'user_image');

            $progressBar->advance();
        }
        $progressBar->finish();
    }

    protected function getItems()
    {
        $host = $this->extConfig['host'];
        $url = 'https://' . $host . '/Veranstalter/xml.php?itemsPerPage=9999&highlight=all';

        // URL abfragen, nur IPv4 Auflösung
        $rawXml = UrlUtility::loadUrl($url);

        // XML im Eventcontainer wandeln
        $eventContainer = GeneralUtility::makeInstance(EventContainer::class);
        $eventContainer->loadXML($rawXml);

        return $eventContainer->getItems() ?? [];
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    protected function insertImage(array $event, string $itemField, string $eventField)
    {
        if (!empty($itemField)) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
            $statement = $queryBuilder->select('*')
                ->from('tx_evangtermine_domain_model_event')
                ->where(
                    $queryBuilder->expr()->eq('id', $queryBuilder->createNamedParameter($event['id']))
                )
                ->executeQuery();
            $eventFromDB = $statement->fetchAssociative();

            if ($eventFromDB[$eventField] == 0) {
                if (str_starts_with($itemField, '//')) {
                    $itemField = 'https:' . $itemField;
                }
                $options = [
                    'allow_redirects' => false,
                ];

                try {
                    $response = $this->requestFactory->request($itemField, 'GET', $options);
                    if ($response->getStatusCode() === 200) {
                        $contents = $response->getBody()->getContents();
                        $imageNameArray = explode('/', $itemField);
                        $imageName = end($imageNameArray);
                        $tmpFileName = '/tmp/' . $imageName;
                        file_put_contents('/tmp/' . $imageName, print_r($contents, true));

                        $folder = $this->storage->getFolder($this->extConfig['imageFolder']);

                        $newFile = $this->storage->addFile(
                            $tmpFileName,
                            $folder,
                            $imageName,
                            DuplicationBehavior::REPLACE
                        );

                        $newId = 'NEW1234';
                        $data = [];
                        $data['sys_file_reference'][$newId] = [
                            'uid_local' => $newFile->getUid(),
                            'tablenames' => 'tx_evangtermine_domain_model_event',
                            'uid_foreign' => $eventFromDB['uid'],
                            'fieldname' => $eventField,
                            'pid' => $eventFromDB['pid'],
                        ];
                        $data['tx_evangtermine_domain_model_event'][$eventFromDB['uid']] = [
                            $eventField => $newId,
                        ];

                        // Process the DataHandler data
                        $this->dataHandler->start($data, []);
                        $this->dataHandler->process_datamap();

                        unlink($tmpFileName);
                    }
                } catch (\Exception $e) {

                }
            }
        }
    }

    protected function setHighlight(string $highlight): int
    {
        switch ($highlight) {
            case 'low':
                return 1;
            case 'high':
                return 2;
            case 'rhigh':
                return 3;
            default:
                return 0;
        }
    }

    protected function setCategories(string $categories): string
    {
        $categoryIds = [];
        $categoriesArray = explode(',', $categories);
        foreach ($categoriesArray as $category) {
            $category = trim($category);
            $categoryId = array_search($category, $this->categoryList) ?? 0;
            if (!empty($categoryId)) {
                $categoryIds[] = '|' . $categoryId . '|';
            }
        }
        return implode('', $categoryIds);
    }

    protected function setPeople(string $people): string
    {
        $peopleIds = [];
        $peopleArray = explode(',', $people);
        foreach ($peopleArray as $person) {
            $person = trim($person);
            $personId = array_search($person, $this->groupList);
            if (!empty($personId)) {
                $peopleIds[] = '|' . $personId . '|';
            }
        }
        return implode('', $peopleIds);
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    protected function deleteEvents(array $items)
    {
        $ids = [];
        foreach ($items as $item) {
            $item = (array)$item;
            $ids[] = $item['ID'];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
        $statement = $queryBuilder->select('uid')
            ->from('tx_evangtermine_domain_model_event')
            ->where(
                $queryBuilder->expr()->notIn('id', $ids)
            )
            ->executeQuery();
        $events = $statement->fetchAllAssociative();

        foreach ($events as $event) {
            $this->connectionPool->getConnectionForTable('tx_evangtermine_domain_model_event')
                ->delete(
                    'tx_evangtermine_domain_model_event', // from
                    ['uid' => $event['uid']]  // where
                );
        }
    }
}
