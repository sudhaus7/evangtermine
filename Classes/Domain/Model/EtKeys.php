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

namespace ArbkomEKvW\Evangtermine\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractValueObject;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015,2021 Christoph Roth <christoph.roth@ekvw.de>, Evangelische Kirche von Westfalen
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

/**
 * EtKeys: Collection of key-value-pairs for requesting events
 */
class EtKeys extends AbstractValueObject
{
    /**
     * array of allowed keys
     * @var array
     */
    private array $allowedKeys = [
            'vid',
            'region',
            'aid',
            'kk',
            'eventtype',
            'highlight',
            'people',
            'person',
            'place',
            'bf',
            'ipm',
            'cha',
            'lang',
            'itemsPerPage',
            'pageID',
            'q',
            'd',
            'month',
            'date',
            'year',
            'start',
            'end',
            'dest',
            'own',
            'menue1',
            'menue2',
            'zip',
            'yesno1',
            'yesno2',
            'until',
            'encoding',
            'redius',
    ];

    /**
     * veranstalter id
     * @var string
     */
    protected string $vid = '';

    /**
     * region
     * @var string
     */
    protected string $region = '';

    /**
     * regions
     * @var string
     */
    protected string $regions = '';

    /**
     * aid (group admin)
     * @var string
     */
    protected string $aid = '';

    /**
     * kk
     * @var string
     */
    protected string $kk = '';

    /**
     *eventtype
     * @var string
     */
    protected string $eventtype = '';

    /**
     * highlight
     * @var string
     */
    protected string $highlight = '';

    /**
     * people
     * @var string
     */
    protected string $people = '';

    /**
     * person
     * @var string
     */
    protected string $person = '';

    /**
     * place
     * @var string
     */
    protected string $place = '';

    /**
     * places
     * @var string
     */
    protected string $places = '';

    /**
     * bf
     * @var string
     */
    protected string $bf = '';

    /**
     * inputmask
     * @var string
     */
    protected string $ipm = '';

    /**
     * channel
     * @var string
     */
    protected string $cha = '';

    /**
     * lang
     * @var string
     */
    protected string $lang = '';

    /**
     * itemsPerPage
     * @var string
     */
    protected string $itemsPerPage = '';

    /**
     * pageID
     * @var string
     */
    protected string $pageID = '';

    /**
     * searchword
     * @var string
     */
    protected string $q = '';

    /**
     * day
     * @var string
     */
    protected string $d = '';

    /**
     * month
     * @var string
     */
    protected string $month = '';

    /**
     * date
     * @var string
     */
    protected string $date = '';

    /**
     * year
     * @var string
     */
    protected string $year = '';

    /**
     * start
     * @var string
     */
    protected string $start = '';

    /**
     * end
     * @var string
     */
    protected string $end = '';

    /**
     * dest
     * @var string
     */
    protected string $dest = '';

    /**
     * own
     * @var string
     */
    protected string $own = '';

    /**
     * menue1
     * @var string
     */
    protected string $menue1 = '';

    /**
     * menue2
     * @var string
     */
    protected string $menue2 = '';

    /**
     * zip
     * @var string
     */
    protected string $zip = '';

    /**
     * yesno1
     * @var string
     */
    protected string $yesno1 = '';

    /**
     * yesno2
     * @var string
     */
    protected string $yesno2 = '';

    /**
     * until
     * @var string
     */
    protected string $until = '';

    /**
     * encoding
     * @var string
     */
    protected string $encoding = '';

    /**
     * ID param for a single event
     * @var string
     */
    protected string $ID = '';

    /**
     * radius
     * @var int
     */
    protected int $radius = 0;

    public function getVid(): string
    {
        return $this->vid;
    }

    public function setVid($vid)
    {
        $this->vid = $vid;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function setRegion($region)
    {
        $this->region = $region;
    }

    public function getRegions(): string
    {
        return $this->regions;
    }

    public function setRegions($regions)
    {
        $this->regions = $regions;
    }

    public function getPlace(): string
    {
        return $this->place;
    }

    public function setPlace($place)
    {
        $this->place = $place;
    }

    public function getPlaces(): string
    {
        return $this->places;
    }

    public function setPlaces($places)
    {
        $this->places = $places;
    }

    public function getAid(): string
    {
        return $this->aid;
    }

    public function setAid($aid)
    {
        $this->aid = $aid;
    }

    public function getKk(): string
    {
        return $this->kk;
    }

    public function setKk($kk)
    {
        $this->kk = $kk;
    }

    public function getEventtype(): string
    {
        return $this->eventtype;
    }

    public function setEventtype($eventtype)
    {
        $this->eventtype = $eventtype;
    }

    public function getHighlight(): string
    {
        return $this->highlight;
    }

    public function setHighlight($highlight)
    {
        $this->highlight = $highlight;
    }

    public function getPeople(): string
    {
        return $this->people;
    }

    public function setPeople($people)
    {
        $this->people = $people;
    }

    public function getPerson(): string
    {
        return $this->person;
    }

    public function setPerson($person)
    {
        $this->person = $person;
    }

    public function getBf(): string
    {
        return $this->bf;
    }

    public function setBf($bf)
    {
        $this->bf = $bf;
    }

    public function getIpm(): string
    {
        return $this->ipm;
    }

    public function setIpm($ipm)
    {
        $this->ipm = $ipm;
    }

    public function getCha(): string
    {
        return $this->cha;
    }

    public function setCha($cha)
    {
        $this->cha = $cha;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    public function getItemsPerPage(): string
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    public function getPageID(): string
    {
        return $this->pageID;
    }

    public function setPageID($pageID)
    {
        $this->pageID = $pageID;
    }

    public function getQ(): string
    {
        return $this->q;
    }

    public function setQ($q)
    {
        $this->q = $q;
    }

    public function getD(): string
    {
        return $this->d;
    }

    public function setD($d)
    {
        $this->d = $d;
    }

    public function getMonth(): string
    {
        return $this->month;
    }

    public function setMonth($month)
    {
        $this->month = $month;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
        if ($date != '') {
            // keep params 'd' and 'month' in sync with 'date'
            $dateTokens = explode('-', $date);
            if (!empty($dateTokens[0]) && !empty($dateTokens[1]) && !empty($dateTokens[2])) {
                $this->setD($dateTokens[2]);
                $this->setMonth($dateTokens[1] . '.' . substr($dateTokens[0], -2));
            }
        }
    }

    public function getYear(): string
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getStart(): string
    {
        return $this->start;
    }

    public function setStart($start)
    {
        $this->start = $start;
    }

    public function getEnd(): string
    {
        return $this->end;
    }

    public function setEnd($end)
    {
        $this->end = $end;
    }

    public function getDest(): string
    {
        return $this->dest;
    }

    public function setDest($dest)
    {
        $this->dest = $dest;
    }

    public function getOwn(): string
    {
        return $this->own;
    }

    public function setOwn($own)
    {
        $this->own = $own;
    }

    public function getMenue1(): string
    {
        return $this->menue1;
    }

    public function setMenue1($menue1)
    {
        $this->menue1 = $menue1;
    }

    public function getMenue2(): string
    {
        return $this->menue2;
    }

    public function setMenue2($menue2)
    {
        $this->menue2 = $menue2;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    public function getYesno1(): string
    {
        return $this->yesno1;
    }

    public function setYesno1($yesno1)
    {
        $this->yesno1 = $yesno1;
    }

    public function getYesno2(): string
    {
        return $this->yesno2;
    }

    public function setYesno2($yesno2)
    {
        $this->yesno2 = $yesno2;
    }

    public function getUntil(): string
    {
        return $this->until;
    }

    public function setUntil($until)
    {
        $this->until = $until;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    public function getID(): string
    {
        return $this->ID;
    }

    public function setID($ID)
    {
        $this->ID = $ID;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function setRadius($radius): void
    {
        $this->radius = (int)$radius;
    }

    public function getAllowedKeys(): array
    {
        return $this->allowedKeys;
    }

    public function setAllowedKeys(array $allowedKeys): void
    {
        $this->allowedKeys = $allowedKeys;
    }

    public function getItemsPerPageList(): array
    {
        return [
            '5' => '5',
            '10' => '10',
            '20' => '20',
            '30' => '30',
            '50' => '50',
            '100' => '100',
        ];
    }

    /**
     * (non-PHPdoc)
     * @see \TYPO3\CMS\Extbase\DomainObject\AbstractValueObject::getValue()
     */
    public function getValue(): string
    {
        foreach (get_object_vars($this) as $key => $value) {
            if (in_array($key, $this->allowedKeys) && $value !== null) {
                $parBlocks[] = $key . '=' . urlencode(utf8_decode($value));
            }
        }

        if (isset($parBlocks)) {
            return implode('&', $parBlocks);
        }
        return '';
    }

    /**
     * Set all reset values
     */
    public function setResetValues()
    {
        $this->setVid('all');
        $this->setRegion('all');
        $this->setEventtype('all');
        $this->setHighlight('all');
        $this->setPeople('0');
        $this->setPageID('1');
        $this->setQ('none');
        $this->setDate('');
        $this->setOwn('all');
        $this->setBf('all');
        $this->setLang('all');
    }

    /**
     * make my own json representation of Etkeys object
     * which is safer for session storage
     * @return string|false
     */
    public function toJson()
    {
        $valueArray = [];

        foreach ($this->allowedKeys as $key) {
            $mthd = 'get' . ucfirst($key);
            $valueArray[$key] = '' . $this->$mthd();
        }

        return json_encode($valueArray);
    }

    /**
     * set all values from json string
     * @param string $jsString
     */
    public function initFromJson(string $jsString)
    {
        $values = (array)json_decode($jsString);

        foreach ($values as $key => $val) {
            $mthd = 'set' . ucfirst($key);
            $this->$mthd($val);
        }
    }
}
