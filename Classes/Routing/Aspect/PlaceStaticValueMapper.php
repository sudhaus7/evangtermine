<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Routing\Aspect;

use Doctrine\DBAL\Exception;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlaceStaticValueMapper extends AbstractStaticValueMapper
{
    /**
     * @throws Exception
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);

        /** @var EventRepository $eventRepository */
        $eventRepository = GeneralUtility::makeInstance(EventRepository::class);
        $places = $eventRepository->findAllPlaces();
        foreach ($places as $key => $place) {
            if ($place == 'Alle Orte') {
                continue;
            }
            $place = $this->changeString($place);
            $this->map[$place] = $key;
        }
    }
}
