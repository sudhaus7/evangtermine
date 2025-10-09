<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Services\Events\Api;

use ArbkomEKvW\Evangtermine\Domain\Model\Categorylist;
use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Grouplist;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventcontainerRepository;
use ArbkomEKvW\Evangtermine\Services\Events\EventsServiceBase;
use ArbkomEKvW\Evangtermine\Services\Events\EventsServiceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EventsService extends EventsServiceBase implements EventsServiceInterface
{
    protected ?EventcontainerRepository $eventRepository = null;
    protected Grouplist $grouplist;
    protected Categorylist $categorylist;

    public function __construct()
    {
        $this->eventRepository = GeneralUtility::makeInstance(EventcontainerRepository::class);
        $this->grouplist = GeneralUtility::makeInstance(Grouplist::class);
        $this->categorylist = GeneralUtility::makeInstance(Categorylist::class);
    }

    public function getEvents(EtKeys $etKeys, array $settings): array
    {
        // retrieve XML
        $eventContainer = $this->eventRepository->findByEtKeys($etKeys);

        // fine tune and save parameters to session
        if ($etKeys->getQ() == 'none') {
            $etKeys->setQ('');
        }
        return [$eventContainer->getItems(), $eventContainer->getMetaData()->totalItems];
    }

    public function findByUid(int|string $uid): array
    {
        /** @var EtKeys $etkeys */
        $etkeys = GeneralUtility::makeInstance(EtKeys::class);
        $etkeys->setID($uid);

        // retrieve XML
        $eventContainer = $this->eventRepository->findByEtKeys($etkeys);
        return [
            $eventContainer->getItems()[0],
            $eventContainer->getMetaData(),
            $eventContainer->getDetail()
        ];
    }

    public function getGroupList(array $settings, int $pluginUid): array
    {
        return $this->grouplist->getItemslist();
    }

    public function getPlaceList(array $settings, int $pluginUid): array
    {
        return [];
    }

    public function getCategoryList(array $settings, int $pluginUid): array
    {
        return $this->categorylist->getItemslist();
    }

    public function getRegionList(array $settings, int $pluginUid): array
    {
        return [];
    }
}
