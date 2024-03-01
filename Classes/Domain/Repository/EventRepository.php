<?php

namespace ArbkomEKvW\Evangtermine\Domain\Repository;

use ArbkomEKvW\Evangtermine\Domain\Model\Categorylist;
use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Grouplist;
use ArbkomEKvW\Evangtermine\Services\OsmService;
use ArbkomEKvW\Evangtermine\Util\SettingsUtility;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidNumberOfConstraintsException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * EventRepository
 */
class EventRepository extends Repository
{
    const LAT_IN_KM = 111.1;
    const LON_IN_KM = 73;

    /**
     * @throws UnexpectedTypeException
     * @throws InvalidNumberOfConstraintsException
     */
    public function prepareFindByEtKeysQuery(EtKeys $etKeys): ?array
    {
        $eventUids = $this->findWithinDistance($etKeys);
        $eventUids = $this->hideOngoingEvents($etKeys, $eventUids);

        if (empty($eventUids)) {
            return null;
        }

        $query = $this->createQuery();

        $settings = $query->getQuerySettings();
        $settings->setRespectStoragePage(false);
        $query->setQuerySettings($settings);

        $queryConstraints = $this->setConstraints($query, $etKeys, $eventUids);

        // if search word
        $searchWordConstraint = [];
        if (!empty($etKeys->getQ()) && $etKeys->getQ() != 'none') {
            $eventsWithSearchWord = $this->filterWithSearchWord($etKeys);
            $searchWordConstraint = $this->setSearchWordConstraint($query, $eventsWithSearchWord);
        }
        $queryConstraints = array_merge($queryConstraints, $searchWordConstraint);

        if (!empty($queryConstraints)) {
            $query->matching($query->logicalAnd(...$queryConstraints));
        }
        return [$query, $queryConstraints];
    }

    public function findByEtKeys(QueryInterface $query, EtKeys $etKeys): array
    {
        $itemsPerPage = (int)$etKeys->getItemsPerPage() ?: 20;
        $query->setLimit($itemsPerPage);
        $query->setOffset($itemsPerPage * ((int)($etKeys->getPageID() ?: 1) - 1));
        // get events
        $events = $query->execute();
        return $events->toArray();
    }

    public function getNumberOfEventsByEtKeys(QueryInterface $query, int $limit = 99999): int
    {
        $query->setLimit($limit);
        $query->setOffset(0);
        return $query->execute()->count();
    }

    public function filterWithSearchWord(EtKeys $etKeys): array
    {
        $etKeysForApiQuery = $etKeys;
        $etKeysForApiQuery->setItemsPerPage(99999);
        $eventContainerRepository = GeneralUtility::makeInstance(EventcontainerRepository::class);
        $result = $eventContainerRepository->findByEtKeys($etKeys);

        $eventsWithSearchWord = [];
        foreach ($result->getItems() as $item) {
            $id = ((array)$item->ID)[0];
            $eventsWithSearchWord[] = $id;
        }
        return $eventsWithSearchWord;
    }

    public function findWithinDistance(EtKeys $etKeys): array
    {
        $zip = $etKeys->getZip();
        $radius = $etKeys->getRadius();

        $query = $this->createQuery();
        if (empty($zip) || empty($radius)) {
            return $query->execute(true);
        }

        $osmService = GeneralUtility::makeInstance(OsmService::class);
        list($lat, $lon) = $osmService->determineCoordinates($zip);

        $statement = 'SELECT uid FROM tx_evangtermine_domain_model_event
            WHERE (
                power((' . $lat . ' - tx_evangtermine_domain_model_event.lat) * ' . EventRepository::LAT_IN_KM . ', 2)
              + power((' . $lon . ' - tx_evangtermine_domain_model_event.lon) * ' . EventRepository::LON_IN_KM . ', 2)
              < power(' . $radius . ', 2)
              OR
                tx_evangtermine_domain_model_event.place_zip = "' . $zip . '"
            )
            ORDER BY
                power((' . $lat . ' - tx_evangtermine_domain_model_event.lat) * ' . EventRepository::LAT_IN_KM . ', 2)
              + power((' . $lon . ' - tx_evangtermine_domain_model_event.lon) * ' . EventRepository::LON_IN_KM . ', 2),
                tx_evangtermine_domain_model_event.place_zip
        ';
        $query->statement($statement);
        // only return array of uids
        return $query->execute(true);
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     * @throws UnexpectedTypeException
     */
    public function setConstraints(QueryInterface $query, EtKeys $etKeys, array $eventUids): array
    {
        $queryConstraints = [];
        $queryConstraints = array_merge($queryConstraints, $this->setUidConstraint($query, $eventUids));
        $queryConstraints = array_merge($queryConstraints, $this->setVid($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setHighlightConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setCategoryConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setPeopleConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setRegionConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setPlaceConstraint($query, $etKeys));
        return array_merge($queryConstraints, $this->setTimeConstraint($query, $etKeys));
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function setSearchWordConstraint(Query $query, array $ids): array
    {
        $queryConstraints = [];
        $queryConstraints[] = $query->in('id', $ids);
        return $queryConstraints;
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function setUidConstraint(Query $query, array $eventUids): array
    {
        $queryConstraints = [];
        $uids = [];
        foreach ($eventUids as $eventUid) {
            $uids[] = $eventUid['uid'];
        }
        $queryConstraints[] = $query->in('uid', $uids);
        return $queryConstraints;
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function setVid(Query $query, EtKeys $etKeys): array
    {
        $vidValue = $etKeys->getVid();
        $queryConstraints = [];
        if ($vidValue == 'all') {
            return $queryConstraints;
        }
        $vids = explode(',', $vidValue);
        if (empty($vids)) {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->in('event_user_id', $vids);
        return $queryConstraints;
    }

    public function setHighlightConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $highlight = $etKeys->getHighlight();
        if (empty($highlight) || $highlight == 'all') {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->greaterThan('highlight', 1);
        return $queryConstraints;
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     */
    public function setCategoryConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $category = $etKeys->getEventtype();
        if (empty($category) || $category == 'all') {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->logicalOr(
            $query->equals('categories', $category),
            $query->like('categories', '%,' . $category . ',%'),
            $query->like('categories', $category . ',%'),
            $query->like('categories', '%,' . $category),
        );
        return $queryConstraints;
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     */
    public function setPeopleConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $person = $etKeys->getPeople();
        if (empty($person) || $person == 'all') {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->logicalOr(
            $query->equals('people', $person),
            $query->like('people', '%,' . $person . ',%'),
            $query->like('people', $person . ',%'),
            $query->like('people', '%,' . $person),
        );
        return $queryConstraints;
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     */
    public function setRegionConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];

        $regions = $etKeys->getRegions();
        if ($regions == 'alleBezirke' || $regions == 'alleKreise') {
            $etKeys->setRegions('all');
            $regions = $etKeys->getRegions();
        }
        if (!empty($regions) && $regions !== 'all') {
            $possibleRegions = [];
            foreach (explode(',', $regions) as $possibleRegion) {
                $possibleRegions[] = $query->equals('region', $possibleRegion);
            }
            $queryConstraints[] = $query->logicalOr(...$possibleRegions);
        }

        $region = $etKeys->getRegion();
        if (empty($region) || $region == 'all') {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->equals('region', $region);
        return $queryConstraints;
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     */
    public function setPlaceConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];

        $places = $etKeys->getPlaces();
        if (!empty($places) && $places !== 'all') {
            $possiblePlaces = [];
            foreach (explode(',', $places) as $possiblePlace) {
                $possiblePlaces[] = $query->equals('place_id', $possiblePlace);
            }
            $queryConstraints[] = $query->logicalOr(...$possiblePlaces);
        }

        $place = $etKeys->getPlace();
        if (empty($place) || $place == 'all') {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->equals('place_id', $place);
        return $queryConstraints;
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     */
    public function setTimeConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $date = $etKeys->getDate();
        if (!empty($date)) {
            $dateTime = (new \DateTime())->createFromFormat('Y-m-d', $date);
            if (!empty($dateTime)) {
                $timestampStart = strtotime(date('d.m.Y', $dateTime->getTimestamp()) . ' 00:00:00');
                $timestampEnd = $timestampStart + 24 * 60 * 60 - 1;
                $queryConstraints[] = $query->logicalOr(
                    $query->logicalAnd(
                        $query->greaterThan('start', $timestampStart),
                        $query->lessThan('start', $timestampEnd),
                    ),
                    $query->logicalAnd(
                        $query->lessThan('start', $timestampStart),
                        $query->greaterThan('end', $timestampStart),
                    ),
                );
            }
        } else {
            $queryConstraints[] = $query->logicalOr(
                $query->greaterThan('start', time()),
                $query->greaterThan('end', time()),
            );
        }
        return $queryConstraints;
    }

    protected function hideOngoingEvents(EtKeys $etKeys, array $eventUids): array
    {
        if (
            empty($eventUids) ||
            empty($etKeys->getHideOngoingEvents())
        ) {
            return $eventUids;
        }
        $uids = [];
        foreach ($eventUids as $eventUid) {
            $uids[] = $eventUid['uid'];
        }

        $time = 60 * 60 * 24 * 7 * $etKeys->getHideOngoingEvents();
        $query = $this->createQuery();
        $query->statement('Select uid from tx_evangtermine_domain_model_event
            WHERE uid in (' . implode(',', $uids) . ')
            AND (
                (tx_evangtermine_domain_model_event.end/1 - tx_evangtermine_domain_model_event.start/1) < ' . $time . '
            )
        ');
        return $query->execute(true);
    }

    /**
     * @param array|null $settings
     * @param array|null $uids
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function findAllPlaces(?array $settings = null, ?array $uids = null): array
    {
        $places = [];
        $places['all'] = 'Alle Orte';

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');

        if (!empty($settings['etkey_places'] ?? '') && ($settings['etkey_places'] ?? '') !== 'all') {
            $queryBuilder->select('place_id', 'place_zip', 'place_city')
                ->from('tx_evangtermine_domain_model_event')
                ->where(
                    $queryBuilder->expr()
                        ->in(
                            'place_id',
                            $queryBuilder->createNamedParameter(explode(',', $settings['etkey_places']), Connection::PARAM_STR_ARRAY)
                        )
                )
                ->groupBy('place_city')
                ->orderBy('place_zip');
            $statement = $queryBuilder->execute();
            $placesFromDB = $statement->fetchAllAssociative();

            foreach ($placesFromDB as $place) {
                $places[$place['place_id']] = $place['place_zip'] . ' ' . $place['place_city'];
            }
            return $places;
        }

        $queryBuilder->select('uid', 'place_id', 'place_zip', 'place_city')
            ->from('tx_evangtermine_domain_model_event')
            ->where(
                $queryBuilder->expr()->neq('place_zip', $queryBuilder->createNamedParameter('')),
                $queryBuilder->expr()->neq('place_city', $queryBuilder->createNamedParameter('')),
                $queryBuilder->expr()->neq('place_zip', $queryBuilder->createNamedParameter('-')),
                $queryBuilder->expr()->neq('place_city', $queryBuilder->createNamedParameter('-')),
                $queryBuilder->expr()->neq('place_zip', $queryBuilder->createNamedParameter('.')),
                $queryBuilder->expr()->neq('place_city', $queryBuilder->createNamedParameter('.')),
                $queryBuilder->expr()->neq('place_zip', $queryBuilder->createNamedParameter('00000')),
            );
        if (!empty($uids)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in('uid', $uids)
            );
        }
        $queryBuilder->groupBy('place_city')
            ->orderBy('place_zip');
        $placesFromDB = $queryBuilder->execute()->fetchAllAssociative();
        foreach ($placesFromDB as $place) {
            $places[$place['place_id']] = $place['place_zip'] . ' ' . $place['place_city'];
        }
        return $places;
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     * @throws UnexpectedTypeException
     * @throws Exception
     * @throws DBALException
     * @throws NoSuchCacheException
     */
    public function findAllPlacesWithEtKeys(?array $settings = null): array
    {
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $dateString = (new \DateTime('today midnight'))->format('Ymd');
        $cache = $cacheManager->getCache('evangtermine');
        $cacheKey = 'places-with-events-' . $dateString;
        $places = $cache->get($cacheKey);

        if (empty($places)) {
            $placesFromDb = $this->findAllPlaces($settings);

            /** @var EtKeys $etKeys */
            list($query, $queryConstraints, $clonedQuery, $clonedQueryConstraints)
                = $this->prepareQuery($settings);
            $places = [];
            if (empty($query)) {
                return $places;
            }
            foreach ($placesFromDb as $placeId => $place) {
                if ($placeId == 'all') {
                    $places[$placeId] = $place;
                    continue;
                }
                $queryConstraints[] = $query->equals('place_id', $placeId);
                $query->matching($query->logicalAnd(...$queryConstraints));
                $nrOfEvents = $this->getNumberOfEventsByEtKeys($query, 1);
                if ($nrOfEvents > 0) {
                    $places[$placeId] = $place;
                }
                $query = $clonedQuery;
                $queryConstraints = $clonedQueryConstraints;
            }
            $cache->set($cacheKey, $places);
        }
        return $places;
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function findAllRegions(?array $settings = null): array
    {
        $regions = [];
        $regions['all'] = 'Alle Regionen';
        $regions['alleBezirke'] = 'Alle Kirchenbezirke';
        $regions['alleKreise'] = 'Alle Kirchenkreise';

        if (!empty($settings['etkey_regions'] ?? '') && ($settings['etkey_regions'] ?? '') !== 'all') {
            $regionsFromSettings = explode(',', $settings['etkey_regions']);
            $regions = [];
            foreach ($regionsFromSettings as $region) {
                $regions[$region] = $region;
            }

            if (!empty($regions['all'])) {
                unset($regions['all']);
                unset($regions['alleBezirke']);
                unset($regions['alleKreise']);
                $regions = array_merge(['all' => 'Alle Regionen'], $regions);
            } else {
                if (!empty($regions['alleBezirke'])) {
                    unset($regions['alleBezirke']);
                    if (empty($regions['alleKreise'])) {
                        $regions = array_merge(['all' => 'Alle Kirchenbezirke'], $regions);
                    } else {
                        unset($regions['alleKreise']);
                        $regions = array_merge(['all' => 'Alle Regionen'], $regions);
                    }
                } else {
                    if (empty($regions['alleKreise'])) {
                        $regions = array_merge(['all' => 'Alle Regionen'], $regions);
                    } else {
                        unset($regions['alleKreise']);
                        $regions = array_merge(['all' => 'Alle Kirchenkreise'], $regions);
                    }
                }
            }
            if (
                $settings['etkey_regions'] !== 'alleBezirke' &&
                $settings['etkey_regions'] !== 'alleKreise'
            ) {
                return $regions;
            }
        } else {
            if (TYPO3_MODE !== 'BE') {
                unset($regions['alleBezirke']);
                unset($regions['alleKreise']);
            }
        }

        $regionsFromDB = $this->getRegionsFromDB();
        foreach ($regionsFromDB as $region) {
            $regions[$region['region']] = $region['region'];
        }
        return $regions;
    }

    /**
     * @param array|null $settings
     * @return array
     * @throws DBALException
     * @throws Exception
     * @throws InvalidNumberOfConstraintsException
     * @throws NoSuchCacheException
     * @throws UnexpectedTypeException
     */
    public function findAllRegionsWithEtKeys(?array $settings = null): array
    {
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $dateString = (new \DateTime('today midnight'))->format('Ymd');
        $cache = $cacheManager->getCache('evangtermine');
        $cacheKey = 'regions-with-events-' . $dateString;
        $regions = $cache->get($cacheKey);

        if (empty($regions)) {
            $regionsFromDb = $this->findAllRegions($settings);
            list($query, $queryConstraints, $clonedQuery, $clonedQueryConstraints)
                = $this->prepareQuery($settings);

            $regions = [];
            if (empty($query)) {
                return $regions;
            }
            foreach ($regionsFromDb as $key => $region) {
                if ($key == 'all') {
                    $regions[$key] = $region;
                    continue;
                }
                $queryConstraints[] = $query->equals('region', $region);
                $query->matching($query->logicalAnd(...$queryConstraints));
                $nrOfEvents = $this->getNumberOfEventsByEtKeys($query, 1);
                if ($nrOfEvents > 0) {
                    $regions[$region] = $region;
                }
                $query = $clonedQuery;
                $queryConstraints = $clonedQueryConstraints;
            }
            $cache->set($cacheKey, $regions);
        }
        return $regions;
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function getRegionsFromDB(): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
        $queryBuilder->select('region')
            ->from('tx_evangtermine_domain_model_event')
            ->where(
                $queryBuilder->expr()->neq('region', $queryBuilder->createNamedParameter(''))
            );

        $queryBuilder->groupBy('region')
            ->orderBy('region');
        $statement = $queryBuilder->execute();
        return $statement->fetchAllAssociative();
    }

    /**
     * @param array|null $settings
     * @return array
     * @throws InvalidNumberOfConstraintsException
     * @throws NoSuchCacheException
     * @throws UnexpectedTypeException
     */
    public function findAllCategoriesWithEtKeys(?array $settings = null): array
    {
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $dateString = (new \DateTime('today midnight'))->format('Ymd');
        $cache = $cacheManager->getCache('evangtermine');
        $cacheKey = 'categories-with-events-' . $dateString;
        $categories = $cache->get($cacheKey);

        if (empty($categories)) {
            $categoryList = GeneralUtility::makeInstance(Categorylist::class);
            $categoriesFromItemlist = $categoryList->getItemslist();
            list($query, $queryConstraints, $clonedQuery, $clonedQueryConstraints)
                = $this->prepareQuery($settings);

            $categories = [];
            if (empty($query)) {
                return $categories;
            }
            foreach ($categoriesFromItemlist as $key => $category) {
                if ($key == 'all') {
                    $categories[$key] = $category;
                    continue;
                }
                $queryConstraints[] = $query->logicalOr(
                    $query->equals('categories', $key),
                    $query->like('categories', '%,' . $key . ',%'),
                    $query->like('categories', $key . ',%'),
                    $query->like('categories', '%,' . $key),
                );
                $query->matching($query->logicalAnd(...$queryConstraints));
                $nrOfEvents = $this->getNumberOfEventsByEtKeys($query, 1);
                if ($nrOfEvents > 0) {
                    $categories[$key] = $category;
                }
                $query = $clonedQuery;
                $queryConstraints = $clonedQueryConstraints;
            }
            $cache->set($cacheKey, $categories);
        }
        return $categories;
    }

    /**
     * @param array|null $settings
     * @return array
     * @throws InvalidNumberOfConstraintsException
     * @throws NoSuchCacheException
     * @throws UnexpectedTypeException
     */
    public function findAllGroupsWithEtKeys(?array $settings = null): array
    {
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $dateString = (new \DateTime('today midnight'))->format('Ymd');
        $cache = $cacheManager->getCache('evangtermine');
        $cacheKey = 'groups-with-events-' . $dateString;
        $groups = $cache->get($cacheKey);

        if (empty($groups)) {
            $grouplist = GeneralUtility::makeInstance(Grouplist::class);

            $groupsFromItemslist = $grouplist->getItemslist();
            list($query, $queryConstraints, $clonedQuery, $clonedQueryConstraints)
                = $this->prepareQuery($settings);

            $groups = [];
            if (empty($query)) {
                return $groups;
            }
            foreach ($groupsFromItemslist as $key => $group) {
                if ($key == 0) {
                    $groups[$key] = $group;
                    continue;
                }
                $queryConstraints[] = $query->logicalOr(
                    $query->equals('people', $key),
                    $query->like('people', '%,' . $key . ',%'),
                    $query->like('people', $key . ',%'),
                    $query->like('people', '%,' . $key),
                );
                $query->matching($query->logicalAnd(...$queryConstraints));
                $nrOfEvents = $this->getNumberOfEventsByEtKeys($query, 1);
                if ($nrOfEvents > 0) {
                    $groups[$key] = $group;
                }
                $query = $clonedQuery;
                $queryConstraints = $clonedQueryConstraints;
            }
            $cache->set($cacheKey, $groups);
        }
        return $groups;
    }

    /**
     * @param array|null $settings
     * @return array
     * @throws InvalidNumberOfConstraintsException
     * @throws UnexpectedTypeException
     */
    protected function prepareQuery(?array $settings): array
    {
        /** @var EtKeys $etKeys */
        $etKeys = GeneralUtility::makeInstance(EtKeys::class);
        $etKeys->setResetValues();
        $settingsUtility = GeneralUtility::makeInstance(SettingsUtility::class);
        $settingsUtility->fetchParamsFromSettings($settings, $etKeys);

        $etKeys->setItemsPerPage(1);
        list($query, $queryConstraints) = $this->prepareFindByEtKeysQuery($etKeys);

        if (empty($query)) {
            return [null, [], null, []];
        }
        $clonedQuery = clone $query;
        $clonedQueryConstraints = $queryConstraints;
        return [$query, $queryConstraints, $clonedQuery, $clonedQueryConstraints];
    }
}
