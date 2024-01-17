<?php

namespace ArbkomEKvW\Evangtermine\Domain\Repository;

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Event;
use ArbkomEKvW\Evangtermine\Services\OsmService;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidNumberOfConstraintsException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * EventRepository
 */
class EventRepository extends Repository
{
    const LAT_IN_KM = 111.1;
    const LON_IN_KM = 73;

    /**
     * @throws InvalidNumberOfConstraintsException
     */
    public function findByEtKeys(EtKeys $etKeys): array
    {
        if (!empty($etKeys->getQ()) && $etKeys->getQ() != 'none') {
            $eventsWithSearchWord = $this->filterWithSearchWord($etKeys);
        }

        $query = $this->createQuery();
        $settings = $query->getQuerySettings();
        $settings->setRespectStoragePage(false);
        $query->setQuerySettings($settings);

        $queryConstraints = $this->setConstraints($query, $etKeys);
        if (!empty($queryConstraints)) {
            $query->matching($query->logicalAnd(...$queryConstraints));
        }

        // get events
        $query->setLimit((int)$etKeys->getItemsPerPage());
        $query->setOffset((int)$etKeys->getItemsPerPage() * ((int)$etKeys->getPageID() - 1));
        $events = $query->execute();

        $events = $this->findWithinDistance($etKeys, $events);

        //return $this->filterWithSearchWord($etKeys, $events);
        return $events->toArray();
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     */
    public function getNumberOfEventsByEtKeys(EtKeys $etKeys): int
    {
        $query = $this->createQuery();
        $settings = $query->getQuerySettings();
        $settings->setRespectStoragePage(false);
        $query->setQuerySettings($settings);

        $queryConstraints = $this->setConstraints($query, $etKeys);
        if (!empty($queryConstraints)) {
            $query->matching($query->logicalAnd(...$queryConstraints));
        }
        return $query->execute()->count();
    }

    public function filterWithSearchWord(EtKeys $etKeys): array
    {
        /*$placesForApiQuery = [];
        / * * @var Event $event * /
        foreach ($events as $event) {
            $placesForApiQuery[$event->getPlaceId()] = $event->getPlaceId();
        }

        $personsForApiQuery = [];
        $personsForApiQuery[0] = 0;
        $personsForApiQuery[999] = '';
        / ** @var Event $event * /
        foreach ($events as $event) {
            $peopleOfEventArray = explode('|', $event->getPeople());
            foreach ($peopleOfEventArray as $id) {
                if (!empty($id)) {
                    $personsForApiQuery[$id] = $id;
                }
            }
        }

        $etKeysForApiQuery = $etKeys;
        $etKeysForApiQuery->setPlaces(implode(',', $placesForApiQuery));
        $etKeysForApiQuery->setPeople(implode(',', $personsForApiQuery));*/

        $etKeysForApiQuery = $etKeys;
        $etKeysForApiQuery->setItemsPerPage(99999);
        $eventContainerRepository = GeneralUtility::makeInstance(EventcontainerRepository::class);
        $result = $eventContainerRepository->findByEtKeys($etKeys);

        $eventsWithSearchWord = [];
        foreach ($result->getItems() as $item) {
            $id = ((array)$item->ID)[0];
            $eventsWithSearchWord[] = $id;
            /*foreach ($events as $event) {
                if ($event->getId() == $id) {
                    $eventsWithSearchWord[] = $event;
                }
            }*/
        }
        return $eventsWithSearchWord;
    }

    public function findWithinDistance(EtKeys $etKeys, QueryResultInterface $events): QueryResultInterface
    {
        $zip = $etKeys->getZip();
        $radius = $etKeys->getRadius();
        if (empty($zip) || empty($radius) || empty($events->toArray())) {
            return $events;
        }

        $uids = [];
        /** @var Event $event */
        foreach ($events as $event) {
            $uids[] = $event->getUid();
        }
        $osmService = GeneralUtility::makeInstance(OsmService::class);
        list($lat, $lon) = $osmService->determineCoordinates($zip);

        $query = $this->createQuery();
        $query->statement('SELECT * FROM tx_evangtermine_domain_model_event
            WHERE uid in (' . implode(",", $uids) . ')
            AND (
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
        ');
        return $query->execute();
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     */
    public function setConstraints(QueryInterface $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];

        $queryConstraints = array_merge($queryConstraints, $this->setHighlightConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setCategoryConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setPeopleConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setRegionConstraint($query, $etKeys));
        $queryConstraints = array_merge($queryConstraints, $this->setPlaceConstraint($query, $etKeys));
        return array_merge($queryConstraints, $this->setTimeConstraint($query, $etKeys));
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

    public function setCategoryConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $category = $etKeys->getEventtype();
        if (empty($category) || $category == 'all') {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->like('categories', '%|' . $category . '|%');
        return $queryConstraints;
    }

    public function setPeopleConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];
        $person = $etKeys->getPeople();
        if (empty($person) || $person == 'all') {
            return $queryConstraints;
        }
        $queryConstraints[] = $query->like('people', '%|' . $person . '|%');
        return $queryConstraints;
    }

    /**
     * @throws InvalidNumberOfConstraintsException
     */
    public function setRegionConstraint(Query $query, EtKeys $etKeys): array
    {
        $queryConstraints = [];

        $regions = $etKeys->getRegions();
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
     *
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
                        $query->lessThan('start',$timestampEnd),
                    ),
                    $query->logicalAnd(
                        $query->lessThan('start', $timestampStart),
                        $query->greaterThan('end',$timestampStart),
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

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function findAllPlaces(?array $settings = null): array
    {
        $places = [];
        $places['all'] = 'Alle Orte';

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');

        if (!empty($settings['etkey_places'] ?? '') && ($settings['etkey_places'] ?? '') !== 'all') {
            $queryBuilder->select('place_id','place_zip','place_city')
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
            $statement = $queryBuilder->executeQuery();
            $regionsFromDB = $statement->fetchAllAssociative();

            foreach ($regionsFromDB as $place) {
                $places[$place['place_id']] = $place['place_zip'] . ' ' . $place['place_city'];
            }
            return $places;
        }

        $statement = $queryBuilder->select('place_id','place_zip','place_city')
            ->from('tx_evangtermine_domain_model_event')
            ->where(
                $queryBuilder->expr()->neq('place_zip', $queryBuilder->createNamedParameter('')),
                $queryBuilder->expr()->neq('place_city', $queryBuilder->createNamedParameter('')),
                $queryBuilder->expr()->neq('place_zip', $queryBuilder->createNamedParameter('-')),
                $queryBuilder->expr()->neq('place_city', $queryBuilder->createNamedParameter('-')),
                $queryBuilder->expr()->neq('place_zip', $queryBuilder->createNamedParameter('.')),
                $queryBuilder->expr()->neq('place_city', $queryBuilder->createNamedParameter('.')),
                $queryBuilder->expr()->neq('place_zip', $queryBuilder->createNamedParameter('00000')),
            )
            ->groupBy('place_city')
            ->orderBy('place_zip')
            ->executeQuery();
        $regionsFromDB = $statement->fetchAllAssociative();
        foreach ($regionsFromDB as $place) {
            $places[$place['place_id']] = $place['place_zip'] . ' ' . $place['place_city'];
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

        if (!empty($settings['etkey_regions'] ?? '') && ($settings['etkey_regions'] ?? '') !== 'all') {
            foreach (explode(',', $settings['etkey_regions']) as $region) {
                $regions[$region] = $region;
            }
            return $regions;
        }

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_evangtermine_domain_model_event');
        $queryBuilder->select('region')
            ->from('tx_evangtermine_domain_model_event')
            ->where(
                $queryBuilder->expr()->neq('region', $queryBuilder->createNamedParameter(''))
            );

        $queryBuilder->groupBy('region')
            ->orderBy('region');
        $statement = $queryBuilder->executeQuery();
        $regionsFromDB = $statement->fetchAllAssociative();
        foreach ($regionsFromDB as $region) {
            $regions[$region['region']] = $region['region'];
        }
        return $regions;
    }
}
