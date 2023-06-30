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

namespace ArbkomEKvW\Evangtermine\Domain\Repository;

/**
 * *************************************************************
 *
 * Copyright notice
 *
 * (c) 2015-2019 Christoph Roth <christoph.roth@lka.ekvw.de>, Evangelische Kirche von Westfalen
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\EventcontainerInterface;
use ArbkomEKvW\Evangtermine\Util\ExtConf;
use ArbkomEKvW\Evangtermine\Util\UrlUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * EventcontainerRepository
 */
class EventcontainerRepository extends Repository implements SingletonInterface
{
    /**
     * @var ArbkomEKvW\Evangtermine\Util\ExtConf
     */
    private $extConf;

    /**
     * Url of xml script on remote server
     *
     * @var string
     */
    protected $xmlSourceUrl = '';

    /**
     * @param ExtConf
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * returns xml Source Url
     *
     * @return string
     */
    public function getXmlSourceUrl()
    {
        if ($this->xmlSourceUrl === '') {
            $this->xmlSourceUrl = 'https://' . $this->extConf->getExtConfArray()['host'] . '/' . $this->extConf->getExtConfArray()['mode'];
        }
        return $this->xmlSourceUrl;
    }

    /**
     * Main method for looking up events.
     *
     * @param EtKeys $etKeys
     * @return EventcontainerInterface
     */
    public function findByEtKeys(EtKeys $etKeys)
    {
        // URL zusammenbauen: SourceURL plus $etKeys->getValue
        $query = ($etKeys->getValue()) ? '?' . $etKeys->getValue() : '';
        $url = $this->getXmlSourceUrl() . $query;

        // URL abfragen, nur IPv4 Auflösung
        $rawXml = UrlUtility::loadUrl($url);

        // XML im Eventcontainer wandeln
        $result = GeneralUtility::makeInstance('ArbkomEKvW\Evangtermine\Domain\Model\Eventcontainer');
        $result->loadXML($rawXml);

        return $result;
    }
}
