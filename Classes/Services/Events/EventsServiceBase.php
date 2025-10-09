<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Services\Events;

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Util\Etpager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class EventsServiceBase
{
    public function createPager(EtKeys $etKeys, mixed $nrOfEvents): Etpager
    {
        $pager = GeneralUtility::makeInstance(Etpager::class);
        $pager->up(
            $nrOfEvents,
            $etKeys->getItemsPerPage(),
            $etKeys->getPageID()
        );
        return $pager;
    }
}
