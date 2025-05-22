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
use ArbkomEKvW\Evangtermine\Solr\IndexService;
use ArbkomEKvW\Evangtermine\Util\FieldMapping;
use ArbkomEKvW\Evangtermine\Util\UrlUtility;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;
use SplObjectStorage;
use Sudhaus7\Logformatter\Logger\ConsoleLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use TYPO3\CMS\Core\Resource\Exception\ExistingTargetFolderException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderWritePermissionsException;
use TYPO3\CMS\Core\Site\SiteFinder;
use function sys_get_temp_dir;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImportEventsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const ITEMS_PER_PAGE = 100;

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
    protected array $allIds = [];
    protected bool $setRedirects = false;
    protected array $pagesWithPlugin = [];
    protected string $detailPageSlugPart = '/termindetails';

    public function __construct(
        private readonly SiteFinder $siteFinder
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws DBALException
     * @throws InsufficientFolderWritePermissionsException
     * @throws ExistingTargetFolderException
     * @throws InsufficientFolderAccessPermissionsException
     * @throws SiteNotFoundException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws \Doctrine\DBAL\Exception
     */
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
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
        $this->fileNameForRunCheck = sys_get_temp_dir() . '/evangelischeTermine_' . sha1($this->host) . '.txt';

        $this->setRedirects = $this->extConfig['setRedirects'] ?? false;
        $this->pagesWithPlugin = $this->getPagesWithPlugin();

        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    public function configure(): void
    {
        $this->setDescription('Import events from one of the APIs of the Evangelische Kirche')
            ->addOption('vids', null, InputOption::VALUE_OPTIONAL, 'Only import events with these vids ("Veranstalter-Ids", comma-separated)')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Use the Console Logger (add -vv or -vvv to actually get the messages)')
            ->addOption('removelock', null, InputOption::VALUE_NONE, 'Remove the lock file')
            ->setHelp('vendor/bin/typo3 evangtermine:importevents');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DBALException
     * @throws Exception
     * @throws SiteNotFoundException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('removelock')) {
            $this->removeFileForRunCheck();
        }
        if ($this->thisCommandIsStillRunning()) {
            return 0;
        }
        if ($input->getOption('debug')) {
            $this->logger = new ConsoleLogger($output);
        }

        $this->importAllEvents($input, $output);

        if (ExtensionManagementUtility::isLoaded('solr')) {
            $this->logger->info('starting Solr Index');
            $solrIndexer = GeneralUtility::makeInstance(IndexService::class);
            $solrIndexer->setLogger($this->logger);
            $solrIndexer->indexForAllSites();
            $this->logger->info('finish Solr Index');
        }

        return 0;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws DBALException
     * @throws Exception
     * @throws SiteNotFoundException
     */
    protected function importAllEvents(InputInterface $input, OutputInterface $output): void
    {
        $this->logger->info('Fetching Items');
        $items = $this->getItems($input, $output);

        if ($items->count() == 0) {
            return;
        }

        $this->logger->info('Cleanup Items');
        $this->deleteEvents($output);

        $this->logger->debug(sprintf('Host %s: Number of events that changed in API: %d', $this->host, count($items)));

        $progressBar = new ProgressBar($output, count($items));

        foreach ($items as $item) {
            //$hash = sha1((string)$item);
            $hash = $items[$item]['hash'];
            $attributes = $this->addAttributesToItems($item);
            $item = (array)$item;
            $item['attributes'] = json_encode($attributes);
            $item['hash'] = $hash;
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
                'start' => DateTime::createFromFormat('Y-m-d H:i:s', $item['START'], new DateTimeZone('Europe/Berlin'))->getTimestamp(),
                'end' => !empty($item['END']) ? DateTime::createFromFormat('Y-m-d H:i:s', $item['END'], new DateTimeZone('Europe/Berlin'))->getTimestamp() : 0,
                'hash' => $item['hash'],
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

        //$this->deleteImages();

        $this->logger->debug(sprintf('Host %s: Import finished', $this->host));
        $progressBar->finish();
    }

    /**
     * @throws SiteNotFoundException
     * @throws DBALException
     */
    protected function createSlug(array $event, $uid): string
    {
        $state = RecordStateFactory::forName('tx_evangtermine_domain_model_event')
            ->fromArray($event, $event['pid'], $uid);
        $slug = $this->slugHelper->generate($event, $event['pid']);

        $this->deleteRedirectEntries($slug);
        return $this->slugHelper->buildSlugForUniqueInTable($slug, $state);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return SplObjectStorage<SimpleXMLElement>
     * @throws Exception
     */
    protected function getItems(InputInterface $input, OutputInterface $output): SplObjectStorage
    {
        $urlForMetaData = 'https://' . $this->host . '/Veranstalter/xml.php?itemsPerPage=0&highlight=all&dest=all';
        $urlMainPart = 'https://' . $this->host . '/Veranstalter/xml.php?itemsPerPage=' . self::ITEMS_PER_PAGE . '&highlight=all&dest=all';

        list($urlForMetaData, $urlMainPart) = $this->limitRequestToVids($input, $urlForMetaData, $urlMainPart);

        // URL abfragen, nur IPv4 Auflösung
        $rawXml = UrlUtility::loadUrl($urlForMetaData);

        // XML im Eventcontainer wandeln
        $eventContainer = GeneralUtility::makeInstance(Eventcontainer::class);
        $eventContainer->loadXML($rawXml);

        $metaData = $eventContainer->getMetaData();
        $newItems = new SplObjectStorage();

        $totalItems = $metaData->totalItems;
        $pages = ceil($totalItems / self::ITEMS_PER_PAGE);
        $this->logger->info(sprintf('Fetching %d items in %d pages ', $totalItems, $pages));
        $progressBar = new ProgressBar($output, $pages);
        $urlset = [];
        for ($i = 1; $i <= $pages; $i++) {
            $this->logger->debug(sprintf('Fetching page %d with url %s', $i, $urlMainPart . '&pageID=' . $i));

            $urlset[] = $urlMainPart . '&pageID=' . $i;
            if (count($urlset) === 10) {
                $this->getEventsFromApi($urlset, $output, $newItems);
                $urlset = [];
            }
            $progressBar->advance();
        }
        if (count($urlset) > 0) { // get the rest
            $this->getEventsFromApi($urlset, $output, $newItems);
        }
        $progressBar->finish();

        return $newItems;
    }

    /**
     * @throws Exception
     */
    protected function getNewItems(SplObjectStorage $newItems, array $items, string $key): void
    {
        foreach ($items as $item) {
            $id = $item->ID;
            if (in_array($id, $this->allIds)) {
                $this->logger->alert('skipping ' . $id);
                continue;
            }

            $this->allIds[] = $id;
            $hash = sha1($item->asXML());
            $res = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_evangtermine_domain_model_event')
                                 ->select(
                                     [ 'hash' ],
                                     'tx_evangtermine_domain_model_event',
                                     ['id' => $id]
                                 );
            $row = $res->fetchAssociative();
            if (!$row || $row['hash'] !== $hash) {
                //new or updated
                $newItems->attach($item);
                $newItems[$item] = ['hash' => $hash];
                $this->logger->debug('adding ' . $id . ' ' . $hash);
            }
        }
    }

    protected function addAttributesToItems(SimpleXMLElement $item): array
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
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
        $statement = $queryBuilder->select('*')
                                  ->from('tx_evangtermine_domain_model_event')
                                  ->where(
                                      $queryBuilder->expr()->eq('id', $queryBuilder->createNamedParameter($event['id']))
                                  )
                                  ->execute();
        $eventFromDB = $statement->fetchAssociative();
        if (!empty($itemField)) {
            if (substr($itemField, 0, 2) === '//') {
                $itemField = 'https:' . $itemField;
            }
        }
        $this->connectionPool->getConnectionForTable('tx_evangtermine_domain_model_event')
         ->update(
             'tx_evangtermine_domain_model_event',
             [$eventField => $itemField],
             ['uid' => $eventFromDB['uid']]
         );
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
            $this->createRedirectEntries($event['slug'] ?? '');
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

        $this->logger->debug(sprintf('Host %s: Number of events that may be deleted: %d', $this->host, count($events)));
        $progressBar = new ProgressBar($output, count($events));

        foreach ($events as $event) {
            $this->createRedirectEntries($event['slug'] ?? '');
            $this->logger->debug(sprintf('Deleting %s %s', $event['uid'], $event['title']));
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

    protected function createRedirectEntries(string $slug): void
    {
        if (!$this->setRedirects) {
            return;
        }
        if (empty($slug)) {
            return;
        }
        foreach ($this->pagesWithPlugin as $page) {
            $sysRedirectData = [
                'pid' => $page['uid'],
                'updatedon' => time(),
                'createdon' => time(),
                'createdby' => 0,
                'target_statuscode' => 301,
                'source_host' => $page['domain'],
                'source_path' => $page['slug'] . $page['detailPageSlugPart'] . $slug,
                'target' => sprintf('t3://page?uid=%d&_language=0', $page['uid']),
            ];
            $this->connectionPool->getConnectionForTable('sys_redirect')
                ->insert(
                    'sys_redirect',
                    $sysRedirectData,
                );
        }
    }

    /**
     * @throws DBALException
     */
    protected function deleteRedirectEntries(string $slug): void
    {
        if (!$this->setRedirects) {
            return;
        }
        if (empty($slug)) {
            return;
        }
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_redirect');
        $queryBuilder->delete('sys_redirect')
            ->where(
                $queryBuilder->expr()->like('source_path', $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($this->detailPageSlugPart . $slug))),
            );
        $queryBuilder->executeStatement();
    }

    /**
     * @param array $urls
     * @param OutputInterface $output
     * @param SplObjectStorage $newItems
     * @throws Exception
     */
    protected function getEventsFromApi(array $urls, OutputInterface $output, SplObjectStorage $newItems): void
    {
        $curls = [];
        $mh = curl_multi_init();
        foreach ($urls as $key => $url) {
            $this->logger->debug(sprintf('Processing url: %s', $url));
            $curls[$key] = curl_init();
            curl_setopt($curls[$key], CURLOPT_URL, $url);
            curl_setopt($curls[$key], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curls[$key], CURLOPT_HEADER, false);
            curl_multi_add_handle($mh, $curls[$key]);
        }

        $running = null;
        do {
            $status = curl_multi_exec($mh, $running);
        } while ($running && $status === CURLM_OK);

        foreach ($curls as $key => $curl) {
            $this->logger->debug(sprintf('Parsing response from url: %s', $urls[$key]));
            $rawXml = curl_multi_getcontent($curl);
            $eventContainer = GeneralUtility::makeInstance(Eventcontainer::class);
            $eventContainer->loadXML($rawXml);
            $items = $eventContainer->getItems();
            $this->getNewItems($newItems, $items ?? [], $key);
            curl_multi_remove_handle($mh, $curl);
        }
        curl_multi_close($mh);
    }

    protected function thisCommandIsStillRunning(): bool
    {
        if (file_exists($this->fileNameForRunCheck)) {
            return true;
        }

        file_put_contents($this->fileNameForRunCheck, print_r($this->host, true));
        register_shutdown_function(function () {
            ImportEventsCommand::removeFileForRunCheck();
        });
        return false;
    }

    protected function removeFileForRunCheck(): void
    {
        unlink($this->fileNameForRunCheck);
    }

    /**
     * @param InputInterface $input
     * @param string $urlForMetaData
     * @param string $urlMainPart
     * @return string[]
     */
    protected function limitRequestToVids(InputInterface $input, string $urlForMetaData, string $urlMainPart): array
    {
        $vids = $input->getOption('vids');
        $vidsArray = explode(',', $vids ?? '');
        $vidString = '';
        foreach ($vidsArray as $vid) {
            if (is_numeric(trim($vid))) {
                $vidString .= $vid . ',';
            }
        }
        if (!empty($vidString)) {
            $vidString = '&vid=' . rtrim($vidString, ',');
            $urlForMetaData .= $vidString;
            $urlMainPart .= $vidString;
        }
        return array($urlForMetaData, $urlMainPart);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws SiteNotFoundException
     * @throws DBALException
     * @throws Exception
     */
    protected function getPagesWithPlugin(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('evangtermine_list'))
            );
        $plugins = $queryBuilder->executeQuery()->fetchAllAssociative();
        $pages = [];
        foreach ($plugins as $plugin) {
            try {
                $site = $this->siteFinder->getSiteByPageId($plugin['pid']);
                $host = $site->getBase()->getHost();
                if (!empty($host)) {
                    $pages[$plugin['pid']] = [
                        'uid' => $plugin['pid'],
                        'rootPageUid' => $site->getRootPageId(),
                        'domain' => $host,
                        'detailPageSlugPart' => $this->detailPageSlugPart,
                    ];
                }
            } catch (\Exception $e) {

            }
        }
        foreach ($pages as $pageUid => $page) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
            $queryBuilder->select('*')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid))
                );
            $pageData = $queryBuilder->executeQuery()->fetchAssociative();
            $pages[$pageUid]['slug'] = $pageData['slug'];
        }
        return $pages;
    }
}
