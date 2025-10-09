<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Services\Events\Imported;

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventRepository;
use ArbkomEKvW\Evangtermine\Services\Events\EventsServiceBase;
use ArbkomEKvW\Evangtermine\Services\Events\EventsServiceInterface;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;

class EventsService extends EventsServiceBase implements EventsServiceInterface
{
    protected ?EventRepository $eventRepository = null;

    public function __construct()
    {
        $this->eventRepository = GeneralUtility::makeInstance(EventRepository::class);
    }

    /**
     * @param EtKeys $etKeys
     * @param array $settings
     * @return array
     * @throws Exception
     * @throws UnexpectedTypeException
     * @throws \Doctrine\DBAL\Exception
     */
    public function getEvents(EtKeys $etKeys, array $settings): array
    {
        [$query, $queryConstraints] = $this->eventRepository->prepareFindByEtKeysQuery($etKeys, $settings['evt_addprms'] ?? '');
        $nrOfEvents = 0;
        if (!empty($query)) {
            try {
                $events = $this->eventRepository->findByEtKeys($query, $etKeys);
                $nrOfEvents = $this->eventRepository->getNumberOfEventsByEtKeys($query);
            } catch (\Exception $exception) {
            }
        }
        return [$events ?? [], $nrOfEvents ?? 0];
    }

    public function findByUid(int|string $uid): array
    {
        // todo: ...
        return [$this->eventRepository->findByUid((int)$uid), '', ''];
    }

    /**
     * @throws NoSuchCacheException
     */
    public function getGroupList(array $settings, int $pluginUid): array
    {
        return $this->eventRepository->findAllGroupsWithEtKeys($settings, $pluginUid);
    }

    /**
     * @throws NoSuchCacheException
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPlaceList(array $settings, int $pluginUid): array
    {
        return $this->eventRepository->findAllPlacesWithEtKeys($settings, $pluginUid);
    }

    /**
     * @throws NoSuchCacheException
     */
    public function getCategoryList(array $settings, int $pluginUid): array
    {
        return $this->eventRepository->findAllCategoriesWithEtKeys($settings, $pluginUid);
    }

    /**
     * @throws NoSuchCacheException
     * @throws Exception
     */
    public function getRegionList(array $settings, int $pluginUid): array
    {
        return $this->eventRepository->findAllRegionsWithEtKeys($settings, $pluginUid);
    }
}
