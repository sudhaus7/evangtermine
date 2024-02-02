<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Routing\Aspect;

use ArbkomEKvW\Evangtermine\Domain\Repository\EventRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlaceStaticValueMapper extends AbstractStaticValueMapper
{
    /**
     * @throws DBALException
     * @throws Exception
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);

        $eventRepository = GeneralUtility::makeInstance(EventRepository::class);
        $places = $eventRepository->findAllPlaces();
        foreach ($places as $key => $place) {
            if ($place == 'Alle Orte') {
                continue;
            }
            $place = $this->changeString($place);
            $this->map[$place] = $key;
        }
        file_put_contents('/tmp/kse.txt', print_r($this->map, true));
    }
}
