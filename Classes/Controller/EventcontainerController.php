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

namespace ArbkomEKvW\Evangtermine\Controller;

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

use ArbkomEKvW\Evangtermine\Domain\Model\Categorylist;
use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Grouplist;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventcontainerRepository;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventRepository;
use ArbkomEKvW\Evangtermine\Util\Etpager;
use ArbkomEKvW\Evangtermine\Util\ExtConf;
use ArbkomEKvW\Evangtermine\Util\SettingsUtility;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidNumberOfConstraintsException;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * EventcontainerController
 */
class EventcontainerController extends ActionController
{
    protected CacheManager $cacheManager;
    protected \DateTime $date;
    protected EventcontainerRepository $eventcontainerRepository;
    protected EventRepository $eventRepository;

    /**
     * Uid value of current tt_content record
     * serves as unique id of this plugin instance, used for session identification
     */
    private int $currentPluginUid;

    private SettingsUtility $settingsUtility;

    private Categorylist $categorylist;

    private Grouplist $grouplist;

    private EtKeys $etkeys;

    private Etpager $pager;

    /**
     * @param SettingsUtility $settingsUtility
     */
    public function injectSettingsUtility(SettingsUtility $settingsUtility)
    {
        $this->settingsUtility = $settingsUtility;
    }

    /**
     * @param EventcontainerRepository $eventcontainerRepository
     */
    public function injectEventcontainerRepository(EventcontainerRepository $eventcontainerRepository)
    {
        $this->eventcontainerRepository = $eventcontainerRepository;
    }

    /**
     * @param EventRepository $eventRepository
     */
    public function injectEventRepository(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
        $this->date = new \DateTime('today midnight');
    }

    protected function initializeAction()
    {
        $this->currentPluginUid = $this->configurationManager->getContentObject()->data['uid'];
        $this->categorylist = GeneralUtility::makeInstance(Categorylist::class);
        $this->grouplist = GeneralUtility::makeInstance(Grouplist::class);
        $this->etkeys = GeneralUtility::makeInstance(EtKeys::class);
        $this->pager = GeneralUtility::makeInstance(Etpager::class);
    }

    /**
     * create new Etkeys object and load Settings
     * @return EtKeys $etkeys
     */
    private function getNewFromSettings(): EtKeys
    {
        $etkeys = GeneralUtility::makeInstance(EtKeys::class);

        $etkeys->setResetValues();

        $this->settingsUtility->fetchParamsFromSettings($this->settings, $etkeys);

        return $etkeys;
    }

    /**
     * action list
     * - must collect all parameters (etKeys) from config-settings, session and request
     * - update session
     * - retrieve XML data
     * - hand it to view
     * @throws Exception
     * @throws DBALException
     * @throws InvalidNumberOfConstraintsException
     * @throws NoSuchCacheException
     */
    public function listAction(): ResponseInterface
    {
        $requestArguments = $this->request->getArguments();
        $formArguments = $requestArguments['etkeysForm'] ?? [];
        $this->etkeys = $this->getNewFromSettings();

        // collect params from request
        $this->settingsUtility->fetchParamsFromRequest($requestArguments, $this->etkeys);

        // check if params are coming in from (search-) form
        if (!empty($formArguments) && $formArguments['pluginUid'] == $this->currentPluginUid) {
            // did user trigger form parameter reset?
            if (isset($requestArguments['sf_reset'])) {
                $this->etkeys = $this->getNewFromSettings(); // do reset
                $formArguments = [];
            } else {
                $this->settingsUtility->fetchParamsFromRequest($formArguments, $this->etkeys);
            }
        }

        $formArgumentsHash = sha1(\json_encode($formArguments));
        $cache = $this->cacheManager->getCache('evangtermine_event_list');
        $cacheKey = 'argumentshash-' . $formArgumentsHash . '-' . $this->date->format('Ymd');
        $content = $cache->get($cacheKey);

        if (empty($content)) {
            $events = $this->eventRepository->findByEtKeys($this->etkeys);
            $nrOfEvents = $this->eventRepository->getNumberOfEventsByEtKeys($this->etkeys);

            // pager
            $this->pager->up(
                $nrOfEvents,
                $this->etkeys->getItemsPerPage(),
                $this->etkeys->getPageID()
            );

            $this->view->assignMultiple([
                'events' => $events,
                'nrOfEvents' => $nrOfEvents,
                'etkeys' => $this->etkeys,
                'pageId' => $GLOBALS['TSFE']->id,
                'pluginUid' => $this->currentPluginUid,
                'categoryList' => $this->categorylist->getItemslist(),
                'groupList' => $this->grouplist->getItemslist(),
                'placeList' => $this->eventRepository->findAllPlaces($this->settings),
                'regionList' => $this->eventRepository->findAllRegions($this->settings),
                'pagerdata' => $this->pager->getPgr(),
            ]);

            $content = $this->view->render();
            $cache->set($cacheKey, $content);

            /*
            // retrieve XML
            $evntContainer = $this->eventcontainerRepository->findByEtKeys($this->etkeys);
            DebuggerUtility::var_dump($this->etkeys,'kks');
    DebuggerUtility::var_dump($evntContainer,'sger');exit;
            // fine tune and save parameters to session
            if ($this->etkeys->getQ() == 'none') {
                $this->etkeys->setQ('');
            }

            // Set up pager widget (no Widgets in Fluid ViewHelpers since TYPO3 v11!)
            $this->pager->up(
                $evntContainer->getMetaData()->totalItems,
                $this->etkeys->getItemsPerPage(),
                $this->etkeys->getPageID()
            );

            // hand model data to the view
            $this->view->assignMultiple([
                'events' => $evntContainer,
                'etkeys' => $this->etkeys,
                'pageId' => $GLOBALS['TSFE']->id,
                'pluginUid' => $this->currentPluginUid,
                'categoryList' => $this->categorylist->getItemslist(),
                'groupList' => $this->grouplist->getItemslist(),
                'pagerdata' => $this->pager->getPgr(),
            ]);
            */
        }
        return $this->htmlResponse($content);
    }

    /**
     * action teaser
     */
    public function teaserAction()
    {
        // teaser needs no session, just params from the settings array
        $etkeysTs = $this->getNewFromSettings();

        // retrieve XML
        $evntContainer = $this->eventcontainerRepository->findByEtKeys($etkeysTs);

        // hand model data to the view
        $this->view->assign('events', $evntContainer);
        $this->view->assign('pageId', $GLOBALS['TSFE']->id);
    }

    /**
     * action show
     * @throws StopActionException
     */
    public function showAction()
    {
        $etkeys = GeneralUtility::makeInstance(EtKeys::class);
        $extconf = GeneralUtility::makeInstance(ExtConf::class);
        $uid = $this->request->getArguments()['uid'] ?? null;
        if (!empty($uid)) {

            //$etkeys->setID($this->request->getArguments()['uid']);

            // retrieve XML
            //$evntContainer = $this->eventcontainerRepository->findByEtKeys($etkeys);

            // hand model data to the view
            $this->view->assign('event', $this->eventRepository->findByUid($uid));
            /*$this->view->assign('meta', $evntContainer->getMetaData());*/
            /*$this->view->assign('detailitems', $evntContainer->getDetail());*/
            $this->view->assign('eventhost', $extconf->getExtConfArray()['host']);
        } else {
            $this->addFlashMessage('Keine Event-ID übergeben', '', AbstractMessage::ERROR);
            $this->redirect('genericinfo');
        }
    }

    /**
     * action genericinfo
     */
    public function genericinfoAction()
    {
    }
}
