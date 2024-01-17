<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Routing\Aspect;

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Eventcontainer;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventcontainerRepository;
use SimpleXMLElement;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Routing\Aspect\StaticValueMapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 *
 */
class EventStaticValueMapper extends StaticValueMapper
{
    protected CacheManager $cacheManager;

    /**
     * @var string
     */
    protected string $secret;

    /**
     * @var string
     */
    protected string $token;

    /**
     * @param array $settings
     * @throws \InvalidArgumentException
     * @throws NoSuchCacheException
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);

        /*$date = new \DateTime('today midnight');
        $this->cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cache = $this->cacheManager->getCache('evangtermine_eventstaticvaluemapper');
        $cacheKey = 'eventstaticvaluemapper-' . $date->format('Ymd');
        $content = $cache->get($cacheKey);

        if (empty($content)) {
            $etkeys = GeneralUtility::makeInstance(EtKeys::class);
            $etkeys->setItemsPerPage(10);
            $eventcontainerRepository = GeneralUtility::makeInstance(EventcontainerRepository::class);
            $evntContainer = $eventcontainerRepository->findByEtKeys($etkeys);

            foreach ($evntContainer->getItems() as $item) {
                $id = ((array)$item->ID)[0];
                if (!empty($id)) {
                    $this->stringToSlug($item);
                }
            }
            $cache->set($cacheKey, \json_encode(['map' => $this->map]));
        }*/
    }

    protected function stringToSlug(SimpleXMLElement $item): void
    {
        $max = 50;
        $title = ((array)$item->_event_TITLE)[0];
        $array = explode(' ', $title);
        $title = '';
        foreach ($array as $value) {
            if (strlen($title) < $max) {
                if (!empty($title)) {
                    $title .= ' ' . $value;
                } else {
                    $title .= $value;
                }
            } else {
                break;
            }
        }

        $title = str_replace(' ', '-', $title);
        $title = str_replace('--', '', $title);
        $title = str_replace('ß', 'ss', $title);
        $title = str_replace('"', '', $title);
        $title = str_replace("'", '', $title);
        $title = str_replace('.', '', $title);
        $title = str_replace(':', '', $title);
        $title = str_replace('?', '', $title);
        $title = str_replace('!', '', $title);
        $title = str_replace('(', '', $title);
        $title = str_replace(')', '', $title);
        $title = mb_strtolower($title);
        $title = str_replace('ä', 'ae', $title);
        $title = str_replace('ö', 'oe', $title);
        $title = str_replace('ü', 'ue', $title);
        $title = str_replace("\r\n", '', $title);
        $title = !empty($title) ? $title : $item['id'];
        $this->map[$title] = (string)$item['id'];
    }
}
