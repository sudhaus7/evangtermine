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
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\ExistingTargetFolderException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderWritePermissionsException;
use TYPO3\CMS\Core\Resource\File;
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
    protected SlugHelper $slugHelper;
    protected array $extConfig;
    protected string $host;
    protected string $fileNameForRunCheck;
    protected string $imageFolder;
    protected array $months = [];
    protected array $monthsArray = [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember',
    ];
    protected array $allIds = [];

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
        $this->extConfig  = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('evangtermine');
        if (version_compare(TYPO3_version, '11.0.0', '<')) {
            $this->storageRepository = GeneralUtility::makeInstance(\ArbkomEKvW\Evangtermine\Resource\StorageRepository::class);
        } else {
            $this->storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        }
        if (!method_exists($this->storageRepository, 'getDefaultStorage')) {
            return;
        }
        $this->storage = $this->storageRepository->getDefaultStorage();
        $this->slugHelper = GeneralUtility::makeInstance(
            SlugHelper::class,
            'tx_evangtermine_domain_model_event',
            'slug',
            $GLOBALS['TCA']['tx_evangtermine_domain_model_event']['columns']['slug']['config']
        );
        $this->imageFolder = $this->extConfig['imageFolder'];
        // create image folder if not there yet
        try {
            $this->storage->getFolder($this->imageFolder);
        } catch (\Exception $e) {
            $this->storage->createFolder($this->imageFolder);
        }
        $this->host = $this->extConfig['host'];
        $this->fileNameForRunCheck = '/tmp/evangelischeTermine_' . sha1($this->host) . '.txt';
    }

    public function configure()
    {
        $this->setDescription('Import events from one of the APIs of the Evangelische Kirche')
             ->setHelp('vendor/bin/typo3 evangtermine:importevents');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DBALException
     * @throws Exception
     * @throws InsufficientFolderAccessPermissionsException
     * @throws SiteNotFoundException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->thisCommandIsStillRunning()) {
            return 0;
        }

        $this->importAllEvents($output);

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @throws DBALException
     * @throws Exception
     * @throws InsufficientFolderAccessPermissionsException
     * @throws SiteNotFoundException
     */
    protected function importAllEvents(OutputInterface $output)
    {
        $items = $this->getItems($output);

        $this->deleteEvents($output);

        $progressBar = new ProgressBar($output, count($items));

        foreach ($items as $item) {
            $attributes = $this->addAttributesToItems($item);
            $item = (array)$item;
            $item['attributes'] = json_encode($attributes);

            if (strpos($item['END'], '0000-00-00') !== false) {
                $startArray = explode(' ', $item['START']);
                if (strpos($item['END'], '0000-00-00 00:00:00') !== false) {
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
            $uid = $queryBuilder->select('uid')
                ->from('tx_evangtermine_domain_model_event')
                ->where(
                    $queryBuilder->expr()->eq('id', $queryBuilder->createNamedParameter($event['id']))
                )
                ->execute()
                ->fetchOne();

            if (!empty($uid)) {
                $event['slug'] = $this->createSlug($event, (int)$uid);

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
                $queryBuilder->execute();
            } else {
                $event['slug'] = $this->createSlug($event, 'id' . mt_rand());
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

        $this->deleteImages();

        $progressBar->finish();
    }

    /**
     * @throws SiteNotFoundException
     */
    protected function createSlug(array $event, $uid)
    {
        $state = RecordStateFactory::forName('tx_evangtermine_domain_model_event')
            ->fromArray($event, $event['pid'], $uid);
        $slug = $this->slugHelper->generate($event, $event['pid']);
        return $this->slugHelper->buildSlugForUniqueInTable($slug, $state);
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    protected function getItems(OutputInterface $output): array
    {
        $urlForMetaData = 'https://' . $this->host . '/Veranstalter/xml.php?itemsPerPage=1&highlight=all';
        $urlMainPart = 'https://' . $this->host . '/Veranstalter/xml.php?itemsPerPage=9999&highlight=all';

        // URL abfragen, nur IPv4 Auflösung
        $rawXml = UrlUtility::loadUrl($urlForMetaData);

        // XML im Eventcontainer wandeln
        $eventContainer = GeneralUtility::makeInstance(EventContainer::class);
        $eventContainer->loadXML($rawXml);

        $metaData = $eventContainer->getMetaData();
        $this->months = (array)$metaData->months->month;

        $newItems = [];
        foreach ($this->months as $month) {
            if (is_string($month)) {
                $urls = [];
                foreach ($this->monthsArray as $key => $monthsArrayItem) {
                    if (str_contains($month, $monthsArrayItem)) {
                        $monthArray = explode(' ', $month);
                        $m = $key;
                        $y = mb_substr($monthArray[1], -2);

                        for ($d = 1; $d < 32; $d++) {
                            $urls[$d . '-' . $m . '-' . $y] = $urlMainPart . '&d=' . $d . '&month=' . $m . '.' . $y;
                        }
                        $newItems = $this->getEventsFromApi($urls, $output, $newItems);
                    }
                }
            }
        }

        return $newItems;
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    protected function getNewItems(array $items, string $key): array
    {
        $keyArray = explode('-', $key);
        $day = $keyArray[0];
        $month = $keyArray[1];
        $year = $keyArray[2];

        $eventModified = [];
        foreach ($items as $item) {
            $item = (array)$item;
            $eventModified[] = $item['ID'] . ',' . $item['_event_MODIFIED'];

            $this->allIds[] = $item['ID'];
        }

        $hash = sha1(json_encode($eventModified));

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_hash');
        $queryBuilder->select('*')
            ->from('tx_evangtermine_domain_model_hash')
            ->where(
                $queryBuilder->expr()->eq('day', $queryBuilder->createNamedParameter($day)),
                $queryBuilder->expr()->eq('month', $queryBuilder->createNamedParameter($month)),
                $queryBuilder->expr()->eq('year', $queryBuilder->createNamedParameter($year)),
            );
        $record = $queryBuilder->executeQuery()->fetchAssociative();

        if (empty($record)) {
            $this->connectionPool
                ->getConnectionForTable('tx_evangtermine_domain_model_hash')
                ->insert(
                    'tx_evangtermine_domain_model_hash',
                    [
                        'pid' => 0,
                        'tstamp' => time(),
                        'crdate' => time(),
                        'day' => $day,
                        'month' => $month,
                        'year' => $year,
                        'hash' => $hash,
                        'events' => json_encode($eventModified),
                    ],
                );
            return $items;
        }
        if (empty($record['hash']) || $record['hash'] !== $hash) {
            $this->connectionPool
                ->getConnectionForTable('tx_evangtermine_domain_model_hash')
                ->update(
                    'tx_evangtermine_domain_model_hash',
                    [
                        'hash' => $hash,
                        'events' => json_encode($eventModified),
                    ],
                    [ 'uid' => $record['uid'] ]
                );

            $oldEvents = json_decode($record['events'], true);
            $changedAndNewItems = [];
            foreach ($items as $item) {
                $itemArray = (array)$item;
                $itemModified = $itemArray['ID'] . ',' . $itemArray['_event_MODIFIED'];
                foreach ($oldEvents ?? [] as $oldEvent) {
                    if ($itemModified == $oldEvent) {
                        continue 2;
                    }
                }
                $changedAndNewItems[] = $item;
            }

            return $changedAndNewItems;
        }
        return [];
    }

    protected function addAttributesToItems(\SimpleXMLElement $item): array
    {
        /** @var FieldMapping $fieldMapping */
        $fieldMapping = GeneralUtility::makeInstance(FieldMapping::class);
        $fields = $fieldMapping->getFields();

        $attributes = [];
        foreach ($item->children() as $key => $value) {
            $json = json_encode($value);
            // "@attributes" would disappear when we transform the object or json to an array,
            // therefore we rename it to "attributes"
            $json = str_replace('@attributes', 'attributes', $json);
            $array = json_decode($json, true);
            $label = $array['attributes']['Label'] ?? '';
            if (!empty($label)) {
                $attributes[array_search($key, $fields)]['label'] = $label;
            }
            $db = $array['attributes']['db'] ?? '';
            if (!empty($db)) {
                $attributes[array_search($key, $fields)]['db'] = $db;
            }
        }
        return $attributes;
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
                ->execute();
            $eventFromDB = $statement->fetchAssociative();

            if ($eventFromDB[$eventField] == 0) {
                if (substr($itemField, 0, 2) === '//') {
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

                        $this->connectionPool->getConnectionForTable('sys_file_reference')
                            ->insert(
                                'sys_file_reference',
                                [
                                    'uid_local' => $newFile->getUid(),
                                    'uid_foreign' => $eventFromDB['uid'],
                                    'tablenames' => 'tx_evangtermine_domain_model_event',
                                    'fieldname' => $eventField,
                                    'pid' => $eventFromDB['pid'],
                                ],
                            );

                        $this->connectionPool->getConnectionForTable('tx_evangtermine_domain_model_event')
                            ->update(
                                'tx_evangtermine_domain_model_event',
                                [$eventField => $newFile->getUid()],
                                ['uid' => $eventFromDB['uid']]
                            );
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
        $categoriesString = implode('', $categoryIds);
        $categoriesString = str_replace('||', ',', $categoriesString);
        return str_replace('|', '', $categoriesString);
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
        $peopleString = implode('', $peopleIds);
        $peopleString = str_replace('||', ',', $peopleString);
        return str_replace('|', '', $peopleString);
    }

    /**
     * @throws InsufficientFolderAccessPermissionsException
     * @throws Exception
     * @throws DBALException
     */
    protected function deleteImages()
    {
        $folder = $this->storage->getFolder($this->extConfig['imageFolder']);
        $files = $this->storage->getFilesInFolder($folder);

        /** @var File $file */
        foreach ($files as $file) {
            $uid = $file->getUid();
            if (empty($uid)) {
                continue;
            }

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
            $queryBuilder->select('tx_evangtermine_domain_model_event.*')
                ->from('tx_evangtermine_domain_model_event')
                ->join(
                    'tx_evangtermine_domain_model_event',
                    'sys_file_reference',
                    'sys_file_reference',
                    $queryBuilder->expr()->eq('sys_file_reference.uid_foreign', $queryBuilder->quoteIdentifier('tx_evangtermine_domain_model_event.uid'))
                )
                ->where(
                    $queryBuilder->expr()->eq('sys_file_reference.uid_local', $queryBuilder->createNamedParameter($uid)),
                    $queryBuilder->expr()->eq('sys_file_reference.tablenames', $queryBuilder->createNamedParameter('tx_evangtermine_domain_model_event'))
                );

            $statement = $queryBuilder->execute();
            $result = $statement->fetchAssociative();

            if (!$result) {
                $file->delete();
            }
        }
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    protected function deleteEvents(OutputInterface $output): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
        $statement = $queryBuilder->select('uid')
            ->from('tx_evangtermine_domain_model_event')
            ->where(
                $queryBuilder->expr()->lte('start', time()),
                $queryBuilder->expr()->lte('end', time())
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
        $this->deleteEventsThatAreNotInApiAnymore($output);
    }

    /**
     * @param OutputInterface $output
     * @throws DBALException
     * @throws Exception
     */
    protected function deleteEventsThatAreNotInApiAnymore(OutputInterface $output): void
    {
        $ids = implode(',', $this->allIds);
        // save events that may need to be deleted
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
        $queryBuilder->select('*')
            ->from('tx_evangtermine_domain_model_event')
            ->where(
                $queryBuilder->expr()->notIn('id', $ids)
            );
        $events = $queryBuilder->executeQuery()->fetchAllAssociative();

        $progressBar = new ProgressBar($output, count($events));

        $count = 0;
        foreach ($events as $event) {
            // Check max. 100 events.
            // If it's more than 100 events, the curl probably gathered not all events.
            if ($count > 100) {
                continue;
            }
            $count++;

            $id = $event['id'];
            $date = new \DateTime();
            $date->setTimestamp($event['start']);
            $d = $date->format('d');
            $m = $date->format('m');
            $y = $date->format('y');
            $url = 'https://' . $this->extConfig['host'] . '/Veranstalter/xml.php?itemsPerPage=99&highlight=all';
            $url .= '&q=' . urlencode($event['title']) . '&d=' . $d . '&month=' . $m . '.' . $y;

            // check if the event is really not in the API anymore
            $rawXml = UrlUtility::loadUrl($url);

            // XML im Eventcontainer wandeln
            $eventContainer = GeneralUtility::makeInstance(EventContainer::class);
            $eventContainer->loadXML($rawXml);
            $items = $eventContainer->getItems();

            foreach ($items as $item) {
                $item = (array)$item;
                if ($item['ID'] == $id) {
                    continue 2;
                }
            }

            // delete the event if it is not found in the API
            $this->connectionPool->getConnectionForTable('tx_evangtermine_domain_model_event')
                ->delete(
                    'tx_evangtermine_domain_model_event', // from
                    ['uid' => $event['uid']]  // where
                );
            $progressBar->advance();
        }
        $progressBar->finish();
    }

    /**
     * @param array $urls
     * @param OutputInterface $output
     * @param array $newItems
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    protected function getEventsFromApi(array $urls, OutputInterface $output, array $newItems): array
    {
        $curls = [];
        $mh = curl_multi_init();
        foreach ($urls as $key => $url) {
            $curls[$key] = curl_init();
            curl_setopt($curls[$key], CURLOPT_URL, $url);
            curl_setopt($curls[$key], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curls[$key], CURLOPT_HEADER, false);
            curl_multi_add_handle($mh, $curls[$key]);
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);

        $progressBar = new ProgressBar($output, count($curls));

        $eventContainer = GeneralUtility::makeInstance(EventContainer::class);

        foreach ($curls as $key => $curl) {
            $rawXml = curl_multi_getcontent($curl);
            $eventContainer->loadXML($rawXml);
            $items = $eventContainer->getItems();
            $itemsFromDay = $this->getNewItems($items ?? [], $key);
            $newItems = array_merge($newItems, $itemsFromDay);
            curl_multi_remove_handle($mh, $curl);

            $progressBar->advance();
        }
        curl_multi_close($mh);
        $progressBar->finish();
        return $newItems;
    }

    protected function thisCommandIsStillRunning(): bool
    {
        if (file_exists($this->fileNameForRunCheck)) {
            return true;
        }

        file_put_contents($this->fileNameForRunCheck, print_r($this->host, true));
        register_shutdown_function(function () {
            \ArbkomEKvW\Evangtermine\Command\ImportEventsCommand::removeFileForRunCheck();
        });
        return false;
    }

    protected function removeFileForRunCheck()
    {
        unlink($this->fileNameForRunCheck);
    }
}
