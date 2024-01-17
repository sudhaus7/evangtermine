<?php

/*
 * This file is part of the TYPO3 project.
 *
 * @author Frank Berger <fberger@sudhaus7.de>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace ArbkomEKvW\Evangtermine\Util;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2021 Christoph Roth <christoph.roth@ekvw.de>, Evangelische Kirche von Westfalen
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use ArbkomEKvW\Evangtermine\Domain\Repository\EventRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use RuntimeException;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
* class CategoryUtil
* Helper class for retrieving Categories and Target Groups
*/
class CategoryUtil
{
    private string $host = '';
    protected CacheManager $cacheManager;
    protected string $dateString;
    protected VariableFrontend $cache;

    /**
     * Constructor fetches name of foreign host for category retrieval
     * @throws NoSuchCacheException
     */
    public function __construct()
    {
        $extconf = GeneralUtility::makeInstance(ExtConf::class);
        $this->host = $extconf->getExtConfArray()['host'];
        $this->cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $this->dateString = (new \DateTime('today midnight'))->format('Ymd');
        $this->cache = $this->cacheManager->getCache('evangtermine');
    }

    /**
     * Get categories list from foreign host. Method used as itemsProcFunc in Flexform
     *
     * @param array $configuration
     */
    public function getCategories(array &$configuration)
    {
        $cacheKey = 'categories-' . $this->dateString;
        $categories = $this->cache->get($cacheKey);

        if (empty($categories)) {
            $url = 'https://' . $this->host . '/service/eventtypes.json';
            $rawCategories = $this->getUrlContent($url);

            $categories = [];
            $categories[] = ['Alle Kategorien', 'all'];

            foreach ($rawCategories as $item) {
                $categories[] = [ $item->name, $item->id ];
            }
            $this->cache->set($cacheKey, $categories);
        }
        $configuration['items'] = $categories;
    }

    /**
     * Get groups list from foreign host. Method used as itemsProcFunc in Flexform
     *
     * @param array $configuration
     */
    public function getGroups(array &$configuration)
    {
        $cacheKey = 'groups-' . $this->dateString;
        $groups = $this->cache->get($cacheKey);

        if (empty($groups)) {
            $url = 'https://' . $this->host . '/service/people.json';
            $rawGroups = $this->getUrlContent($url);

            $groups = [];

            foreach ($rawGroups as $item) {
                $groups[] = [$item->name, $item->id];
            }
            $this->cache->set($cacheKey, $groups);
        }
        $configuration['items'] = $groups;
    }

    /**
     * @param array $configuration
     * @throws DBALException
     * @throws Exception
     */
    public function getRegions(array &$configuration)
    {
        $cacheKey = 'regions-' . $this->dateString;
        $regions = $this->cache->get($cacheKey);

        if (empty($regions)) {
            $eventRepo = GeneralUtility::makeInstance(EventRepository::class);
            $regionsFromEvents = $eventRepo->findAllRegions();
            foreach ($regionsFromEvents as $key => $region) {
                $regions[] = [
                    0 => $region,
                    1 => $key,
                ];
            }
            $this->cache->set($cacheKey, $regions);
        }
        $configuration['items'] = $regions;
    }

    /**
     * @param array $configuration
     * @throws DBALException
     * @throws Exception
     */
    public function getPlaces(array &$configuration)
    {
        $cacheKey = 'places-' . $this->dateString;
        $places = $this->cache->get($cacheKey);

        if (empty($places)) {
            $eventRepository = GeneralUtility::makeInstance(EventRepository::class);
            $placesFromEvents = $eventRepository->findAllPlaces();
            foreach ($placesFromEvents as $key => $place) {
                $places[] = [
                    0 => $place,
                    1 => $key,
                ];
            }
            $this->cache->set($cacheKey, $places);
        }
        $configuration['items'] = $places;
    }

    /**
     * Get JSON String with categories or audience from URL.
     *
     * @param string $url
     * @return array Decoded JSON
     */
    private function getUrlContent(string $url): array
    {
        // URL abfragen, nur IPv4 Auflösung
        $contentString = UrlUtility::loadUrl($url);

        $contentArray = json_decode($contentString);
        if ($contentArray === null) {
            throw new RuntimeException('No valid JSON in ' . $url);
        }

        return $contentArray;
    }
}
