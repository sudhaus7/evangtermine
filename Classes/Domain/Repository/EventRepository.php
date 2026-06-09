<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Domain\Repository;

use TYPO3\CMS\Core\Http\ApplicationType;
use ArbkomEKvW\Evangtermine\Domain\Model\Categorylist;
use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Grouplist;
use ArbkomEKvW\Evangtermine\Services\OsmService;
use ArbkomEKvW\Evangtermine\Util\SettingsUtility;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * EventRepository
 */
class EventRepository extends Repository
{
    public const LAT_IN_KM = 111.1;
    public const LON_IN_KM = 73;
    public const REGION_FIELDS = [
        /*'region',*/
        'event_subregion_id',
        /*'event_region2_id',
        'event_region3_id',
        'place_region'*/
    ];
    /**
     * Constructs a new Repository
     */
    public function __construct(private readonly ConnectionPool $connectionPool, private readonly CacheManager $cacheManager)
    {
        parent::__construct();
    }

    /**
     * @param EtKeys $etKeys
     * @param string $additionParams
     * @return array|null
     * @throws Exception
     * @throws UnexpectedTypeException
     * @throws \Doctrine\DBAL\Exception
     */
    public function prepareFindByEtKeysQuery(EtKeys $etKeys, string $additionParams = ''): ?array
    {
        [$eventUids, $filtered] = $this->preSelect($etKeys);

        if (empty($eventUids) && $filtered) {
            return null;
        }

        $query = $this->createQuery();

        $settings = $query->getQuerySettings();
        $settings->setRespectStoragePage(false);
        $query->setQuerySettings($settings);

        $queryConstraints = $this->setConstraints($query, $etKeys, $eventUids);

        // if search word or vid
        $searchWordConstraint = [];
        if ((!in_array($etKeys->getQ(), ['', '0'], true) && $etKeys->getQ() !== 'none') || (!in_array($etKeys->getVid(), ['', '0'], true) && $etKeys->getVid() !== 'all') || $additionParams !== '' && $additionParams !== '0') {
            $eventsWithSearchWord = $this->filterWithSearchWordAndVidAndAdditionalParams($etKeys, $additionParams);
            $searchWordConstraint = $this->setSearchWordAndVidConstraint($query, $eventsWithSearchWord);
        }
        $queryConstraints = array_merge($queryConstraints, $searchWordConstraint);

        if ($queryConstraints !== []) {
            $query->matching($query->logicalAnd(...$queryConstraints));
        }
        return [$query, $queryConstraints];
    }

    public function findByEtKeys(QueryInterface $query, EtKeys $etKeys): array
    {
        $itemsPerPage = (int)$etKeys->getItemsPerPage() ?: 20;
        $query->setLimit($itemsPerPage);
        $query->setOffset($itemsPerPage * ((int)($etKeys->getPageID() ?: 1) - 1));
        $query->setOrderings(
            [
                'start' => QueryInterface::ORDER_ASCENDING,
                'end' => QueryInterface::ORDER_ASCENDING,
                'title' => QueryInterface::ORDER_ASCENDING,
            ]
        );
        // get events
        $events = $query->execute();
        return $events->toArray();
    }

    public function getNumberOfEventsByEtKeys(QueryInterface $query, int $limit = 999999): int
    {
        $query->setLimit($limit);
        $query->setOffset(0);
        return $query->execute()->count();
    }

    public function filterWithSearchWordAndVidAndAdditionalParams(EtKeys $etKeys, string $additionParams = ''): array
    {
        $etKeysForApiQuery = clone $etKeys;
        $etKeysForApiQuery->setItemsPerPage(99999);
        $etKeysForApiQuery->setPageID(1);

        $additionParamsArray = explode('&', $additionParams);
        foreach ($additionParamsArray as $additionParam) {
            if (str_contains($additionParam, '=')) {
                [$key, $value] = explode('=', $additionParam);
                $targetMethod = 'set' . ucfirst(trim($key));
                if (method_exists($etKeysForApiQuery, $targetMethod)) {
                    $etKeysForApiQuery->{$targetMethod}(trim($value));
                }
            }
        }
        $eventContainerRepository = GeneralUtility::makeInstance(EventcontainerRepository::class);
        $result = $eventContainerRepository->findByEtKeys($etKeysForApiQuery);

        $eventsWithSearchWord = [];
        foreach ($result->getItems() as $item) {
            $id = ((array)$item->ID)[0];
            $eventsWithSearchWord[] = $id;
        }
        return $eventsWithSearchWord;
    }

    public function preSelect(EtKeys $etKeys): array
    {
        [$eventUids, $filtered] = $this->findWithinDistance($etKeys);
        return $this->hideOngoingEvents($etKeys, $eventUids, $filtered);
    }

    public function findWithinDistance(EtKeys $etKeys): array
    {
        $zip = $etKeys->getZip();
        $radius = $etKeys->getRadius();

        $query = $this->createQuery();
        if ($zip === '' || $zip === '0' || $radius === 0) {
            return [null, false];
        }

        $osmService = GeneralUtility::makeInstance(OsmService::class);
        [$lat, $lon] = $osmService->determineCoordinates($zip);

        $statement = 'SELECT uid FROM tx_evangtermine_domain_model_event
            WHERE (
                power((? - tx_evangtermine_domain_model_event.lat) * ' . EventRepository::LAT_IN_KM . ', 2)
              + power((? - tx_evangtermine_domain_model_event.lon) * ' . EventRepository::LON_IN_KM . ', 2)
              < power(?, 2)
              OR
                tx_evangtermine_domain_model_event.place_zip = ?
            )
            ORDER BY
                power((? - tx_evangtermine_domain_model_event.lat) * ' . EventRepository::LAT_IN_KM . ', 2)
              + power((? - tx_evangtermine_domain_model_event.lon) * ' . EventRepository::LON_IN_KM . ', 2),
                tx_evangtermine_domain_model_event.place_zip
        ';
        $query->statement($statement, [$lat, $lon, $radius, $zip, $lat, $lon]);
        // only return array of uids
        return [$query->execute(true), true];
    }

    /**
     * @param QueryInterface $query
     * @param EtKeys $etKeys
     * @param array|null $eventUids
     * @return array
     * @throws Exception
     * @throws UnexpectedTypeException
     * @throws \Doctrine\DBAL\Exception
     */
    public function setConstraints(QueryInterface $query, EtKeys $etKeys, ?array $eventUids): array
    {
        $queryConstraints = [];
        if ($eventUids !== null && $eventUids !== []) {
            $queryConstraints = array_merge($queryConstraints, $this->setUidConstraint($query, $eventUids));
        }
        $queryConstraints = array_merge($queryConstraints, $this->setHighlightConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setCategoryConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setPeopleConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setRegionConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setSubregionsConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setRegion2Constraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setRegion3Constraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setPlaceConstraint($query, $etKeys));
        return array_merge($queryConstraints, $this->setTimeConstraint($query, $etKeys));
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function setSearchWordAndVidConstraint(Query $query, array $ids): array
    {
        $queryConstraints = [];
        if ($ids === []) {
            // set to -9999 to prevent an error but still find no events
            $queryConstraints[] = $query->in('id', [-9999]);
        } else {
            $queryConstraints[] = $query->in('id', $ids);
        }
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
        if ($vidValue === 'all') {
            return $queryConstraints;
        }
        $vids = explode(',', $vidValue);
        if ($vids === []) {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->in('event_user_id', $vids);
        return $queryConstraints;
    }

    public function setHighlightConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $highlight = $etKeys->getHighlight();
        if (in_array($highlight, ['', '0', 'all'], true)) {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->greaterThan('highlight', 1);
        return $queryConstraints;
    }

    public function setCategoryConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $category = $etKeys->getEventtype();

        $categoryArray = explode(',', $category);
        $categoryConstraints = [];
        foreach ($categoryArray as $category) {
            if (in_array($category, ['', '0', 'all'], true)) {
                continue;
            }
            $categoryConstraints[] = $query->equals('categories', $category);
            $categoryConstraints[] = $query->like('categories', '%,' . $category . ',%');
            $categoryConstraints[] = $query->like('categories', $category . ',%');
            $categoryConstraints[] = $query->like('categories', '%,' . $category);
        }
        if ($categoryConstraints !== []) {
            $queryConstraints[] = $query->logicalOr(...$categoryConstraints);
        }
        return $queryConstraints;
    }

    public function setPeopleConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $person = $etKeys->getPeople();

        $personArray = explode(',', $person);
        if (empty($personArray[0]) || $personArray[0] == 'all') {
            $personArray = explode(',', $etKeys->getPeople());
        }

        $personConstraints = [];
        foreach ($personArray as $person) {
            if (in_array($person, ['', '0', 'all'], true)) {
                continue;
            }
            $personConstraints[] = $query->equals('people', $person);
            $personConstraints[] = $query->like('people', '%,' . $person . ',%');
            $personConstraints[] = $query->like('people', $person . ',%');
            $personConstraints[] = $query->like('people', '%,' . $person);
        }
        if ($personConstraints !== []) {
            $queryConstraints[] = $query->logicalOr(...$personConstraints);
        }
        return $queryConstraints;
    }

    /**
     * @param Query $query
     * @param EtKeys $etKeys
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function setRegionConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];

        $regions = $etKeys->getRegions();
        if ($regions === 'alleBezirke' || $regions === 'alleKreise') {
            $etKeys->setRegions('all');
            $regions = $etKeys->getRegions();
        }
        if (!in_array($regions, ['', '0', 'all'], true)) {
            $possibleRegions = [];
            foreach (explode(',', $regions) as $possibleRegion) {
                $possibleRegions[] = $query->equals('region', $possibleRegion);
            }
            $queryConstraints[] = $query->logicalOr(...$possibleRegions);
        }

        $region = $etKeys->getRegion();

        if (in_array($region, ['', '0', 'all'], true)) {
            return $queryConstraints;
        }
        $possibleRegions = [];
        foreach (explode(',', $region) as $possibleRegion) {
            if (is_numeric($possibleRegion)) {
                $regionName = $this->getRegionById((int)$possibleRegion);
                if (!in_array($regionName, [null, '', '0'], true)) {
                    foreach (self::REGION_FIELDS as $field) {
                        $possibleRegions[] = $query->equals($field, $regionName);
                    }
                }
            } else {
                $possibleRegions[] = $query->equals('region', $possibleRegion);
            }
        }
        if ($possibleRegions !== []) {
            $queryConstraints[] = $query->logicalOr(...$possibleRegions);
        }
        return $queryConstraints;
    }

    public function setSubregionsConstraint(Query $query, EtKeys $etKeys): array
    {
        return $this->setOtherRegionsConstraint($query, $etKeys->getSubregions(), 'event_subregion_id');
    }

    public function setRegion2Constraint(Query $query, EtKeys $etKeys): array
    {
        return $this->setOtherRegionsConstraint($query, $etKeys->getRegion2(), 'event_region2_id');
    }

    public function setRegion3Constraint(Query $query, EtKeys $etKeys): array
    {
        return $this->setOtherRegionsConstraint($query, $etKeys->getRegion3(), 'event_region3_id');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getRegionById(int $id): ?string
    {
        $connectionPool = $this->connectionPool;
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
        $queryBuilder->select('region', 'event_subregion_id', 'event_region2_id', 'event_region3_id', 'place_region', 'attributes')
            ->from('tx_evangtermine_domain_model_event')
            ->where(
                $queryBuilder->expr()->like('attributes', $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards((string)$id) . '%', Connection::PARAM_STR))
            );
        $result = $queryBuilder->executeQuery()->fetchAllAssociative();
        if (empty($result)) {
            return null;
        }
        foreach ($result as $event) {
            $attributes = json_decode((string) $event['attributes'], true);
            foreach (self::REGION_FIELDS as $field) {
                if ((isset($attributes[$field]['db']) && (int)$attributes[$field]['db'] ?? 0) == $id) {
                    return $event[$field] ?? null;
                }
            }
        }
        return null;
    }

    public function setPlaceConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];

        $places = $etKeys->getPlaces();
        if (!in_array($places, ['', '0', 'all'], true)) {
            $possiblePlaces = [];
            foreach (explode(',', $places) as $possiblePlace) {
                $possiblePlaces[] = $query->equals('place_id', $possiblePlace);
            }
            $queryConstraints[] = $query->logicalOr(...$possiblePlaces);
        }

        $place = $etKeys->getPlace();
        if (in_array($place, ['', '0', 'all'], true)) {
            return $queryConstraints;
        }
        $possiblePlaces = [];
        foreach (explode(',', $place) as $possiblePlace) {
            $possiblePlaces[] = $query->equals('place_id', $possiblePlace);
        }
        $queryConstraints[] = $query->logicalOr(...$possiblePlaces);
        return $queryConstraints;
    }

    public function setTimeConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $date = $etKeys->getDate();
        if ($date !== '' && $date !== '0') {
            $dateTime = (new \DateTime())->createFromFormat('Y-m-d', $date);
            if (empty($dateTime)) {
                $dateTime = (new \DateTime())->createFromFormat('d.m.Y', $date);
            }
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

    protected function hideOngoingEvents(EtKeys $etKeys, ?array $eventUids, bool $filtered): array
    {
        if ($filtered) {
            if (
                $eventUids === null || $eventUids === [] ||
                in_array($etKeys->getHideOngoingEvents(), ['', '0'], true)
            ) {
                return [$eventUids, true];
            }
        } else {
            if (in_array($etKeys->getHideOngoingEvents(), ['', '0'], true)) {
                return [null, false];
            }
            $query = $this->createQuery();
            $eventUids = $query->execute(true);
        }
        $uids = [];
        foreach ($eventUids as $eventUid) {
            $uids[] = $eventUid['uid'];
        }

        $time = 60 * 60 * 24 * 7 * 2; // 2 weeks
        $query = $this->createQuery();
        $query->statement('Select uid from tx_evangtermine_domain_model_event
            WHERE uid in (' . implode(',', $uids) . ')
            AND (
                (tx_evangtermine_domain_model_event.end/1 - tx_evangtermine_domain_model_event.start/1) < ' . $time . '
            )
        ');
        return [$query->execute(true), true];
    }

    /**
     * @param array|null $settings
     * @param array|null $uids
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllPlaces(?array $settings = null, ?array $uids = null): array
    {
        $places = [];
        $places['all'] = 'Alle Orte';

        $connectionPool = $this->connectionPool;
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');

        $etkeyPlaces = $settings['etkey_places'] ?? '';
        if (is_array($etkeyPlaces) && isset($etkeyPlaces[0])) {
            $etkeyPlaces = $etkeyPlaces[0];
        }
        if (!empty($etkeyPlaces ?? '') && ($etkeyPlaces ?? '') !== 'all') {
            $queryBuilder->select('place_id', 'place_zip', 'place_city')
                ->from('tx_evangtermine_domain_model_event')
                ->where(
                    $queryBuilder->expr()
                        ->in(
                            'place_id',
                            $queryBuilder->createNamedParameter(explode(',', $etkeyPlaces), Connection::PARAM_STR_ARRAY)
                        )
                )
                ->orderBy('place_zip');
            $statement = $queryBuilder->executeQuery();
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
        if ($uids !== null && $uids !== []) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in('uid', $uids)
            );
        }
        $queryBuilder->orderBy('place_zip');
        $placesFromDB = $queryBuilder->executeQuery()->fetchAllAssociative();
        foreach ($placesFromDB as $place) {
            $places[$place['place_id']] = $place['place_zip'] . ' ' . $place['place_city'];
        }
        return $places;
    }

    /**
     * @param array|null $settings
     * @param int $pluginUid
     * @return array
     * @throws NoSuchCacheException
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllPlacesWithEtKeys(?array $settings = null, int $pluginUid = 0): array
    {
        $cacheManager = $this->cacheManager;
        $dateString = (new \DateTime('today midnight'))->format('Ymd');
        $cache = $cacheManager->getCache('evangtermine');
        $cacheKey = 'places-with-events-' . $dateString . '-' . $pluginUid;
        $places = $cache->get($cacheKey);

        if (empty($places)) {
            $places = $this->findAllPlaces($settings);

            // this would remove select options with no events
            /*$placesFromDb = $this->findAllPlaces($settings);
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
            }*/
            $cache->set($cacheKey, $places);
        }
        return $places;
    }

    /**
     * @throws Exception
     */
    public function findAllRegions(?array $settings = null): array
    {
        $regions = [];
        $regions['all'] = 'Alle Regionen';
        $regions['alleBezirke'] = 'Alle Kirchenbezirke';
        $regions['alleKreise'] = 'Alle Kirchenkreise';

        $etkeyRegions = $settings['etkey_regions'] ?? '';
        if (is_array($etkeyRegions) && isset($etkeyRegions[0])) {
            $etkeyRegions = $etkeyRegions[0];
        }
        if (!empty($etkeyRegions ?? '') && ($etkeyRegions ?? '') !== 'all') {
            $regionsFromSettings = explode(',', $etkeyRegions);
            $regions = [];
            foreach ($regionsFromSettings as $region) {
                $regions[$region] = $region;
            }
            if (isset($regions['all']) && ($regions['all'] !== '' && $regions['all'] !== '0')) {
                unset($regions['all']);
                unset($regions['alleBezirke']);
                unset($regions['alleKreise']);
                $regions = array_merge(['all' => 'Alle Regionen'], $regions);
            } elseif (isset($regions['alleBezirke']) && ($regions['alleBezirke'] !== '' && $regions['alleBezirke'] !== '0')) {
                unset($regions['alleBezirke']);
                if (empty($regions['alleKreise'])) {
                    $regions = array_merge(['all' => 'Alle Kirchenbezirke'], $regions);
                } else {
                    unset($regions['alleKreise']);
                    $regions = array_merge(['all' => 'Alle Regionen'], $regions);
                }
            } elseif (empty($regions['alleKreise'])) {
                $regions = array_merge(['all' => 'Alle Regionen'], $regions);
            } else {
                unset($regions['alleKreise']);
                $regions = array_merge(['all' => 'Alle Kirchenkreise'], $regions);
            }
            if (
                $etkeyRegions !== 'alleBezirke' &&
                $etkeyRegions !== 'alleKreise'
            ) {
                return $regions;
            }
        } elseif (!ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            unset($regions['alleBezirke']);
            unset($regions['alleKreise']);
        }

        $regionsFromDB = $this->getRegionsFromDB();
        foreach ($regionsFromDB as $region) {
            $regions[$region['region']] = $region['region'];
        }
        return $regions;
    }

    /**
     * @param array|null $settings
     * @param int $pluginUid
     * @return array
     * @throws Exception
     * @throws NoSuchCacheException
     */
    public function findAllRegionsWithEtKeys(?array $settings = null, int $pluginUid = 0): array
    {
        $cacheManager = $this->cacheManager;
        $dateString = (new \DateTime('today midnight'))->format('Ymd');
        $cache = $cacheManager->getCache('evangtermine');
        $cacheKey = 'regions-with-events-' . $dateString . '-' . $pluginUid;
        $regions = $cache->get($cacheKey);

        if (empty($regions)) {
            $regions = $this->findAllRegions($settings);

            // this would remove select options with no events
            /*$regionsFromDb = $this->findAllRegions($settings);
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
            }*/
            $cache->set($cacheKey, $regions);
        }
        return $regions;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getRegionsFromDB(): array
    {
        $connectionPool = $this->connectionPool;
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
        $queryBuilder->select('region')
            ->from('tx_evangtermine_domain_model_event')
            ->where(
                $queryBuilder->expr()->neq('region', $queryBuilder->createNamedParameter(''))
            );

        $queryBuilder->groupBy('region')
            ->orderBy('region');
        $statement = $queryBuilder->executeQuery();
        return $statement->fetchAllAssociative();
    }

    /**
     * @param array|null $settings
     * @param int $pluginUid
     * @return array
     * @throws NoSuchCacheException
     */
    public function findAllCategoriesWithEtKeys(?array $settings = null, int $pluginUid = 0): array
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = $this->cacheManager;
        $dateString = (new \DateTime('today midnight'))->format('Ymd');
        $cache = $cacheManager->getCache('evangtermine');
        $cacheKey = 'categories-with-events-' . $dateString . '-' . $pluginUid;
        $categories = $cache->get($cacheKey);

        if (empty($categories)) {
            $categoryList = GeneralUtility::makeInstance(Categorylist::class);
            $categories = $categoryList->getItemslist();

            // this would remove select options with no events
            /*$categoriesFromItemlist = $categoryList->getItemslist();
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
            }*/
            $cache->set($cacheKey, $categories);
        }
        return $categories;
    }

    /**
     * @param array|null $settings
     * @param int $pluginUid
     * @return array
     * @throws NoSuchCacheException
     */
    public function findAllGroupsWithEtKeys(?array $settings = null, int $pluginUid = 0): array
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = $this->cacheManager;
        $dateString = (new \DateTime('today midnight'))->format('Ymd');
        $cache = $cacheManager->getCache('evangtermine');
        $cacheKey = 'groups-with-events-' . $dateString . '-' . $pluginUid;
        $groups = $cache->get($cacheKey);

        if (empty($groups)) {
            $grouplist = GeneralUtility::makeInstance(Grouplist::class);
            $groups = $grouplist->getItemslist();
            if (!empty($settings['etkey_people']) && $settings['etkey_people'] !== 'all') {
                $allowedGroups = [];
                $allowedGroups[0] = $groups[0];
                foreach (explode(',', (string) $settings['etkey_people']) as $person) {
                    if (!empty($groups[$person])) {
                        $allowedGroups[$person] = $groups[$person];
                    }
                }
                $groups = $allowedGroups;
            }

            // this would remove select options with no events
            /*$groupsFromItemslist = $grouplist->getItemslist();
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
            }*/
            $cache->set($cacheKey, $groups);
        }
        return $groups;
    }

    /**
     * @param array|null $settings
     * @return array
     * @throws Exception
     * @throws UnexpectedTypeException
     * @throws \Doctrine\DBAL\Exception
     */
    protected function prepareQuery(?array $settings): array
    {
        /** @var EtKeys $etKeys */
        $etKeys = GeneralUtility::makeInstance(EtKeys::class);
        $etKeys->setResetValues();
        $settingsUtility = GeneralUtility::makeInstance(SettingsUtility::class);
        $settingsUtility->fetchParamsFromSettings($settings, $etKeys);

        $etKeys->setItemsPerPage(1);
        [$query, $queryConstraints] = $this->prepareFindByEtKeysQuery($etKeys);

        if (empty($query)) {
            return [null, [], null, []];
        }
        $clonedQuery = clone $query;
        $clonedQueryConstraints = $queryConstraints;
        return [$query, $queryConstraints, $clonedQuery, $clonedQueryConstraints];
    }

    /**
     * @param Query $query
     * @param string $subregions
     * @param string $field
     * @return array
     */
    protected function setOtherRegionsConstraint(Query $query, string $subregions, string $field): array
    {
        $queryConstraints = [];
        $allowedSubregions = [];
        foreach (explode(',', $subregions) as $subregion) {
            if (in_array($subregion, ['', '0', 'all', 'Alle'], true)) {
                continue;
            }
            $allowedSubregions[] = $query->equals($field, $subregion);
        }
        if ($allowedSubregions !== []) {
            $queryConstraints[] = $query->logicalOr(...$allowedSubregions);
        }
        return $queryConstraints;
    }
}
