<?php

namespace ArbkomEKvW\Evangtermine\Solr;

use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\Domain\Site\Site as SolrSite;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteRepository;
use ApacheSolrForTypo3\Solr\IndexQueue\Queue;
use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Event;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventRepository;
use ArbkomEKvW\Evangtermine\Util\SettingsUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class IndexService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected EventRepository $eventRepository;
    protected SiteFinder $siteFinder;
    protected FlexFormService $flexFormService;
    protected SettingsUtility $settingsUtility;
    protected Queue $queue;
    protected SiteRepository $siteRepository;
    protected ConnectionManager $connectionManager;
    public function __construct(
        EventRepository $eventRepository,
        SiteFinder $siteFinder,
        FlexFormService $flexFormService,
        SettingsUtility $settingsUtility,
        Queue $queue,
        SiteRepository $siteRepository,
        ConnectionManager $connectionManager
    ) {
        $this->eventRepository = $eventRepository;
        $this->siteFinder = $siteFinder;
        $this->flexFormService = $flexFormService;
        $this->settingsUtility = $settingsUtility;
        $this->queue = $queue;
        $this->siteRepository = $siteRepository;
        $this->connectionManager = $connectionManager;
    }

    public function indexForAllSites()
    {
        $sites = $this->siteFinder->getAllSites();
        foreach ($sites as $site) {
            try {
                $this->indexForSite($site);
            } catch (\InvalidArgumentException $e) {
                $this->logger->error('Site ' . $site->getRootPageId() . ' ' . $site->getIdentifier() . ' has no Solr configuration');
            } catch (\Throwable $e) {
                $this->logger->error('Site ' . $site->getRootPageId() . ' ' . $site->getIdentifier() . ' ' . $e->getMessage());
            }
        }
    }

    public function indexForSite(Site $site): void
    {
        /** @var SolrSite $solrSite */
        $solrSite = $this->siteRepository->getSiteByRootPageId($site->getRootPageId());

        if ($solrSite->isEnabled() && $solrSite->getSolrConfiguration()->isValidPath('plugin.tx_solr.index.queue.tx_evangtermine_domain_model_event') && isset($site->getConfiguration()['evangtermineTargetPage']) && !empty($site->getConfiguration()['evangtermineTargetPage'])) {
            $this->logger->info('Indexing Site ' . $site->getRootPageId() . ' ' . $site->getIdentifier());

            $idsToIndex = [];
            $plugins    = $this->getAllPluginsInSite($site);
            foreach ($plugins as $plugin) {
                $flex   = $this->flexFormService->convertFlexFormContentToArray($plugin['pi_flexform']);
                $etkeys = GeneralUtility::makeInstance(EtKeys::class);
                $etkeys->setResetValues();
                $this->settingsUtility->fetchParamsFromSettings($flex['settings'], $etkeys);

                /** @var QueryInterface $query */
                [ $query, $queryConstraints ] = $this->eventRepository->prepareFindByEtKeysQuery($etkeys);
                $result = $query->execute();
                /** @var Event $event */
                foreach ($result as $event) {
                    if (! in_array($event->getUid(), $idsToIndex)) {
                        $idsToIndex[] = $event->getUid();
                    }
                }
            }

            $this->collectGarbage($idsToIndex, $solrSite);
            foreach ($idsToIndex as $itemUid) {
                if ($this->queue->containsItem('tx_evangtermine_domain_model_event', $itemUid)) {
                    GeneralUtility::makeInstance(ConnectionPool::class)
                                  ->getConnectionForTable('tx_solr_indexqueue_item')
                                  ->update(
                                      'tx_solr_indexqueue_item',
                                      [
                                          'changed' => time(),

                                      ],
                                      [
                                          'item_uid'  => $itemUid,
                                          'item_type' => 'tx_evangtermine_domain_model_event',
                                          'root'      => $solrSite->getRootPageId(),
                                      ]
                                  );
                } else {
                    GeneralUtility::makeInstance(ConnectionPool::class)
                                  ->getConnectionForTable('tx_solr_indexqueue_item')
                                  ->insert(
                                      'tx_solr_indexqueue_item',
                                      [
                                          'item_uid'               => $itemUid,
                                          'item_type'              => 'tx_evangtermine_domain_model_event',
                                          'indexing_configuration' => 'tx_evangtermine_domain_model_event',
                                          'root'                   => $solrSite->getRootPageId(),
                                          'changed'                => time(),
                                      ]
                                  );
                }
            }
        }
    }

    protected function collectGarbage(array $idsToIndex, SolrSite $solrSite): void
    {
        if (!empty($idsToIndex)) {
            $query = GeneralUtility::makeInstance(ConnectionPool::class)
                                   ->getQueryBuilderForTable('tx_solr_indexqueue_item');
            $stmt  = $query->select('*')
                           ->from('tx_solr_indexqueue_item')
                           ->where(
                               $query->expr()->notIn('item_uid', $idsToIndex),
                               $query->expr()->eq(
                                   'item_type',
                                   $query->createNamedParameter('tx_evangtermine_domain_model_event')
                               ),
                               $query->expr()->eq('root', $solrSite->getRootPageId())
                           )
                           ->execute();

            while ($itemToDelete = $stmt->fetchAssociative()) {
                GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_solr_indexqueue_item')
                              ->delete(
                                  'tx_solr_indexqueue_item',
                                  ['uid'=>$itemToDelete['uid']]
                              );
                try {
                    $strategy = GeneralUtility::makeInstance(EventStrategy::class);
                    $strategy->setMySolrConnections($this->connectionManager->getConnectionsBySite($solrSite));
                    $strategy->setMySiteHash($solrSite->getSiteHash());
                    $strategy->setMyEnableCommitsSetting($solrSite->getSolrConfiguration()->getEnableCommits());
                    $strategy->removeGarbageOf('', $itemToDelete['item_uid']);
                } catch(\Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
    }

    protected function getAllPluginsInSite(Site $site): array
    {
        $pageIds = $this->getAllPagesInSite($site);
        $query = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $stmt = $query->select('*')
                      ->from('tt_content')
                      ->where(
                          $query->expr()->eq('CType', $query->createNamedParameter('list')),
                          $query->expr()->eq('list_type', $query->createNamedParameter('evangtermine_list')),
                          $query->expr()->in('pid', $pageIds)
                      )
                      ->execute();
        return $stmt->fetchAllAssociative();
    }

    protected function getAllPagesInSite(Site $site): array
    {
        $pageIds = [];

        $query = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $query->executeQuery(sprintf('
		select pages.uid  from pages
left join pages p1 on pages.pid=p1.uid
left join pages p2 on p1.pid=p2.uid
left join pages p3 on p2.pid=p3.uid
left join pages p4 on p3.pid=p4.uid
left join pages p5 on p4.pid=p5.uid
left join pages p6 on p5.pid=p6.uid
where (pages.uid=%1$d or p1.uid=%1$d or p2.uid=%1$d or p3.uid=%1$d or p4.uid=%1$d or p5.uid=%1$d or p6.uid=%1$d) and pages.deleted=0;
		', (int)$site->getRootPageId()));

        while ($row = $result->fetchAssociative()) {
            $pageIds[]=$row['uid'];
        }
        return $pageIds;
    }
}
