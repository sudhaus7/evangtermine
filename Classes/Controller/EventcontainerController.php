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

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Event;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventcontainerRepository;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventRepository;
use ArbkomEKvW\Evangtermine\Event\ModifyEvangTermineShowActionViewEvent;
use ArbkomEKvW\Evangtermine\Util\Etpager;
use ArbkomEKvW\Evangtermine\Util\ExtConf;
use ArbkomEKvW\Evangtermine\Util\SettingsUtility;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidNumberOfConstraintsException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Fluid\View\StandaloneView;

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
        $this->date = new \DateTime();
    }

    protected function initializeAction()
    {
        $this->currentPluginUid = $this->configurationManager->getContentObject()->data['uid'];
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
     * @throws NoSuchCacheException|UnexpectedTypeException
     */
    public function listAction()
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
                $requestArguments = [];
            } else {
                $this->settingsUtility->fetchParamsFromRequest($formArguments, $this->etkeys);
            }
        }

        $requestArgumentsHash = sha1(\json_encode($requestArguments));
        $cache = $this->cacheManager->getCache('evangtermine_event_list');
        $cacheKey = $this->getCacheKey($requestArgumentsHash, $this->currentPluginUid);
        $content = $cache->get($cacheKey);

        if (empty($content)) {
            list($query, $queryConstraints) = $this->eventRepository->prepareFindByEtKeysQuery($this->etkeys);
            $nrOfEvents = 0;
            if (!empty($query)) {
                try {
                    $events = $this->eventRepository->findByEtKeys($query, $this->etkeys);
                    $nrOfEvents = $this->eventRepository->getNumberOfEventsByEtKeys($query);
                } catch (\Exception $exception) {
                }
            }

            // pager
            $this->pager->up(
                $nrOfEvents,
                $this->etkeys->getItemsPerPage(),
                $this->etkeys->getPageID()
            );

            $data = $this->configurationManager->getContentObject()->data;
            $this->view->assignMultiple([
                'events' => $events ?? [],
                'nrOfEvents' => $nrOfEvents,
                'etkeys' => $this->etkeys,
                'pageId' => $GLOBALS['TSFE']->id,
                'pluginUid' => $this->currentPluginUid,
                'categoryList' => $this->eventRepository->findAllCategoriesWithEtKeys($this->settings, $this->currentPluginUid),
                'groupList' => $this->eventRepository->findAllGroupsWithEtKeys($this->settings, $this->currentPluginUid),
                'placeList' => $this->eventRepository->findAllPlacesWithEtKeys($this->settings, $this->currentPluginUid),
                'regionList' => $this->eventRepository->findAllRegionsWithEtKeys($this->settings, $this->currentPluginUid),
                'pagerdata' => $this->pager->getPgr(),
                'data' => $data,
                'detailPage' => $this->getDetailPage(),
                'detailPagePluginUid' => $this->getDetailPagePluginUid($data),
            ]);

            $content = $this->view->render();
            if ($this->ifContentIsNotEmpty($content, $events ?? [])) {
                $cache->set($cacheKey, $content);
            }
        }
        return $content;
    }

    /**
     * @return mixed|string
     * @throws DBALException
     * @throws Exception
     * @throws InvalidNumberOfConstraintsException
     * @throws NoSuchCacheException
     * @throws UnexpectedTypeException
     */
    public function teaserAction()
    {
        $this->etkeys = $this->getNewFromSettings();
        $data = $this->configurationManager->getContentObject()->data;

        $cache = $this->cacheManager->getCache('evangtermine_event_teaser');
        $cacheKey = $this->getCacheKey('', $data['uid']);
        $content = $cache->get($cacheKey);

        if (empty($content)) {
            list($query, $queryConstraints) = $this->eventRepository->prepareFindByEtKeysQuery($this->etkeys);
            $events = $this->eventRepository->findByEtKeys($query, $this->etkeys);

            $this->view->assign('events', $events);
            $this->view->assign('pageId', $GLOBALS['TSFE']->id);
            $this->view->assign('data', $data);
            $this->view->assign('detailPage', $this->getDetailPage());
            $this->view->assign('detailPagePluginUid', $this->getDetailPagePluginUid($data));
            $content = $this->view->render();
            $cache->set($cacheKey, $content);
        }
        return $content;
    }

    /**
     * action show
     * @return mixed|string|void
     * @throws DBALException
     * @throws Exception
     * @throws InvalidNumberOfConstraintsException
     * @throws NoSuchCacheException
     * @throws StopActionException
     * @throws UnexpectedTypeException
     */
    public function showAction()
    {
        $data = $this->configurationManager->getContentObject()->data;

        // If the current plugin is a 'detail' plugin, or if it is the plugin in which the user clicked on a link.
        // We need this for multiple evang. Termine plugins on one site.
        if ($this->pluginIsDetailPlugin($data)) {
            $extconf = GeneralUtility::makeInstance(ExtConf::class);
            $uid = $this->request->getArguments()['uid'] ?? null;
            if (!empty($uid)) {
                /** @var Event $event */
                $event = $this->eventRepository->findByUid($uid);

                // hand model data to the view
                $this->view->assign('event', $event);
                $this->view->assign('eventhost', $extconf->getExtConfArray()['host']);
                $this->view->assign('categoryList', $this->eventRepository->findAllCategoriesWithEtKeys($this->settings));
                $this->view->assign('groupList', $this->eventRepository->findAllGroupsWithEtKeys($this->settings));
                $this->view->assign('data', $this->configurationManager->getContentObject()->data);

                if (!empty($event)) {
                    $this->eventDispatcher->dispatch(
                        new ModifyEvangTermineShowActionViewEvent($this->view, $event)
                    );
                }
            } else {
                $this->addFlashMessage('Keine Event-ID übergeben', '', AbstractMessage::ERROR);
                $this->redirect('genericinfo');
            }
        } else {
            // render content of teaser or list
            if ($data['list_type'] == 'evangtermine_teaser') {
                $content = $this->teaserAction();
            } else {
                $this->request->setArguments([]);
                $this->view = $this->setView('listAction');
                $content = $this->listAction();
            }
            return $content;
        }
    }

    /**
     * action genericinfo
     */
    public function genericinfoAction() {}

    protected function setView(string $actionName)
    {
        $backendConfigurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
        $typoscript = $backendConfigurationManager->getTypoScriptSetup();
        $pluginConfiguration = $typoscript['plugin.']['tx_evangtermine.']['view.'] ?? [];
        if (empty($pluginConfiguration)) {
            return $this->view;
        }
        $templateRootPaths = $pluginConfiguration['templateRootPaths.'] ?? [];
        $partialRootPaths = $pluginConfiguration['partialRootPaths.'] ?? [];
        $layoutRootPaths = $pluginConfiguration['layoutRootPaths.'] ?? [];

        if (empty($templateRootPaths) || empty($partialRootPaths) || empty($layoutRootPaths)) {
            return $this->view;
        }
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplate($actionName);
        $this->view->setTemplateRootPaths($templateRootPaths);
        $this->view->setPartialRootPaths($partialRootPaths);
        $this->view->setLayoutRootPaths($layoutRootPaths);
        return $this->view;
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    protected function getDetailPage(): int
    {
        $detailPage = $this->settings['opmode_detailpage'] ?? 0;
        if (empty($detailPage)) {
            return 0;
        }

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter((int)$detailPage, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('evangtermine_detail'))
            );
        $result = $queryBuilder->executeQuery()->fetchAssociative();

        if (empty($result)) {
            $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
            $queryBuilder->select('*')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter((int)$detailPage, \PDO::PARAM_INT)),
                    $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('evangtermine_list'))
                );
            $result = $queryBuilder->executeQuery()->fetchAssociative();

            if (empty($result)) {
                return 0;
            }

            $flexFormArray = GeneralUtility::xml2array($result['pi_flexform']);
            $detailPage = $flexFormArray['data']['opmode']['lDEF']['settings.opmode_detailpage']['vDEF'] ?? null;
            if (is_numeric($detailPage)) {
                return (int)$detailPage;
            }
        }
        return (int)$detailPage;
    }

    /**
     * @throws Exception
     * @throws DBALException
     */
    protected function getDetailPagePluginUid(array $data): int
    {
        $detailPage = $this->settings['opmode_detailpage'] ?? 0;
        if (empty($detailPage)) {
            return $data['uid'];
        }

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter((int)$detailPage, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('evangtermine_detail'))
            );
        $result = $queryBuilder->executeQuery()->fetchAssociative();

        if (!empty($result)) {
            return (int)$result['uid'];
        }

        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter((int)$detailPage, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('evangtermine_list'))
            );
        $result = $queryBuilder->executeQuery()->fetchAssociative();

        if (!empty($result)) {
            $flexFormArray = GeneralUtility::xml2array($result['pi_flexform']);
            $detailPage = $flexFormArray['data']['opmode']['lDEF']['settings.opmode_detailpage']['vDEF'] ?? null;
            if (is_numeric($detailPage)) {
                return (int)$detailPage;
            }
            return (int)$result['uid'];
        }
        return 0;
    }

    protected function ifContentIsNotEmpty(string $content, array $events): bool
    {
        if (empty($events)) {
            return false;
        }
        if (!empty($content) && !str_contains($content, 'Diese Veranstaltung existiert nicht.')) {
            return true;
        }
        return false;
    }

    protected function pluginIsDetailPlugin(array $data): bool
    {
        $uidCurrentPlugin = $data['uid'];

        $cookies = [];
        foreach ($_COOKIE as $key => $cookie) {
            if (str_starts_with($key, 'etpluginuid')) {
                $cookies[] = $cookie;
            }
        }
        $uidDetailPlugin = $_COOKIE['etpluginuid' . $uidCurrentPlugin] ?? null;

        // delete cookie
        setcookie('etpluginuid' . $uidCurrentPlugin, '', -1, '/');

        if (empty($cookies)) {
            return true;
        }

        if (!is_numeric($uidCurrentPlugin)) {
            return true;
        }

        if ($data['list_type'] == 'evangtermine_detail') {
            return true;
        }

        if (empty($uidDetailPlugin)) {
            return false;
        }

        if ($uidCurrentPlugin == $uidDetailPlugin || $uidDetailPlugin == -1) {
            return true;
        }
        return false;
    }

    /**
     * @param string $requestArgumentsHash
     * @param int $uid
     * @return string
     */
    protected function getCacheKey(string $requestArgumentsHash, int $uid): string
    {
        // cache key is valid for 0.5h
        return 'argumentshash-' . $requestArgumentsHash . '-'
            . $this->date->format('YmdH') . '-'
            . ($this->date->format('i') - 30 > 0 ? '1' : '0')
            . '-' . $uid;
    }
}
