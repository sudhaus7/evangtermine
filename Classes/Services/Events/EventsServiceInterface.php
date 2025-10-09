<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Services\Events;

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Util\Etpager;

interface EventsServiceInterface
{
    public function getEvents(EtKeys $etKeys, array $settings): array;

    public function findByUid(int|string $uid): array;

    public function createPager(EtKeys $etKeys, int $nrOfEvents): Etpager;

    public function getGroupList(array $settings, int $pluginUid): array;

    public function getPlaceList(array $settings, int $pluginUid): array;

    public function getCategoryList(array $settings, int $pluginUid): array;

    public function getRegionList(array $settings, int $pluginUid): array;
}
