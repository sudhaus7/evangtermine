<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Routing\Aspect;

use ArbkomEKvW\Evangtermine\Domain\Repository\EventRepository;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RegionStaticValueMapper extends AbstractStaticValueMapper
{
    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);

        $eventRepository = GeneralUtility::makeInstance(EventRepository::class);
        $regionsFromDB = $eventRepository->getRegionsFromDB();

        foreach ($regionsFromDB as $key => $item) {
            $region = $item['region'];
            $region = $this->changeString($region);
            $this->map[$region] = $item['region'];
        }
    }
}
