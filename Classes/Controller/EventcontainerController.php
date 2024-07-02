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
            [$query, $queryConstraints] = $this->eventRepository->prepareFindByEtKeysQuery($this->etkeys);
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
            [$query, $queryConstraints] = $this->eventRepository->prepareFindByEtKeysQuery($this->etkeys);
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
				// Das wirft massiv Exceptions:
	            /**
	             * Tue, 02 Jul 2024 17:29:55 +0200 [ALERT] request="3964001d42a78" component="TYPO3.CMS.Frontend.ContentObject.Exception.ProductionExceptionHandler": Oops, an error occurred! Code: 20240702172954ec9a59fe- InvalidTemplateResourceException: Tried resolving a template file for controller action "Standard->listAction" in format ".html", but none of the paths contained the expected template file (Standard/ListAction.html). The following paths were checked: /var/www/vhosts/ekbo/releases/488/vendor/sudhaus7/evangtermine/Resources/Private/Templates/, /var/www/vhosts/ekbo/releases/488/vendor/ekd/plugin-evangelischetermine/Resources/Private/Templates/, in file /var/www/vhosts/ekbo/releases/488/vendor/typo3fluid/fluid/src/View/TemplatePaths.php:595 - {"exception":"TYPO3Fluid\\Fluid\\View\\Exception\\InvalidTemplateResourceException: Tried resolving a template file for controller action \"Standard->listAction\" in format \".html\", but none of the paths contained the expected template file (Standard/ListAction.html). The following paths were checked: /var/www/vhosts/ekbo/releases/488/vendor/sudhaus7/evangtermine/Resources/Private/Templates/, /var/www/vhosts/ekbo/releases/488/vendor/ekd/plugin-evangelischetermine/Resources/Private/Templates/ in /var/www/vhosts/ekbo/releases/488/vendor/typo3fluid/fluid/src/View/TemplatePaths.php:595\nStack trace:\n#0 /var/www/vhosts/ekbo/releases/488/vendor/typo3fluid/fluid/src/View/AbstractTemplateView.php(385): TYPO3Fluid\\Fluid\\View\\TemplatePaths->getTemplateSource('Standard', 'listAction')\n#1 /var/www/vhosts/ekbo/releases/488/vendor/typo3fluid/fluid/src/Core/Parser/TemplateParser.php(215): TYPO3Fluid\\Fluid\\View\\AbstractTemplateView->TYPO3Fluid\\Fluid\\View\\{closure}(Object(TYPO3Fluid\\Fluid\\Core\\Parser\\TemplateParser), Object(TYPO3\\CMS\\Fluid\\View\\TemplatePaths))\n#2 /var/www/vhosts/ekbo/releases/488/vendor/typo3fluid/fluid/src/Core/Parser/TemplateParser.php(195): TYPO3Fluid\\Fluid\\Core\\Parser\\TemplateParser->parseTemplateSource('Standard_action...', Object(Closure))\n#3 /var/www/vhosts/ekbo/releases/488/vendor/typo3fluid/fluid/src/View/AbstractTemplateView.php(386): TYPO3Fluid\\Fluid\\Core\\Parser\\TemplateParser->getOrParseAndStoreTemplate('Standard_action...', Object(Closure))\n#4 /var/www/vhosts/ekbo/releases/488/vendor/typo3fluid/fluid/src/View/AbstractTemplateView.php(177): TYPO3Fluid\\Fluid\\View\\AbstractTemplateView->getCurrentParsedTemplate()\n#5 /var/www/vhosts/ekbo/releases/488/vendor/sudhaus7/evangtermine/Classes/Controller/EventcontainerController.php(207): TYPO3Fluid\\Fluid\\View\\AbstractTemplateView->render()\n#6 /var/www/vhosts/ekbo/releases/488/vendor/sudhaus7/evangtermine/Classes/Controller/EventcontainerController.php(293): ArbkomEKvW\\Evangtermine\\Controller\\EventcontainerController->listAction()\n#7 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-extbase/Classes/Mvc/Controller/ActionController.php(571): ArbkomEKvW\\Evangtermine\\Controller\\EventcontainerController->showAction()\n#8 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-extbase/Classes/Mvc/Controller/ActionController.php(488): TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController->callActionMethod(Object(TYPO3\\CMS\\Extbase\\Mvc\\Request))\n#9 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-extbase/Classes/Mvc/Dispatcher.php(96): TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController->processRequest(Object(TYPO3\\CMS\\Extbase\\Mvc\\Request))\n#10 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-extbase/Classes/Mvc/Web/FrontendRequestHandler.php(46): TYPO3\\CMS\\Extbase\\Mvc\\Dispatcher->dispatch(Object(TYPO3\\CMS\\Extbase\\Mvc\\Request))\n#11 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-extbase/Classes/Core/Bootstrap.php(165): TYPO3\\CMS\\Extbase\\Mvc\\Web\\FrontendRequestHandler->handleRequest(Object(TYPO3\\CMS\\Extbase\\Mvc\\Request))\n#12 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-extbase/Classes/Core/Bootstrap.php(148): TYPO3\\CMS\\Extbase\\Core\\Bootstrap->handleFrontendRequest(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#13 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/ContentObject/ContentObjectRenderer.php(5431): TYPO3\\CMS\\Extbase\\Core\\Bootstrap->run('', Array, Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#14 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/ContentObject/UserContentObject.php(44): TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer->callUserFunction('TYPO3\\\\CMS\\\\Extba...', Array, '')\n#15 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/ContentObject/ContentObjectRenderer.php(815): TYPO3\\CMS\\Frontend\\ContentObject\\UserContentObject->render(Array)\n#16 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/ContentObject/ContentObjectRenderer.php(751): TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer->render(Object(TYPO3\\CMS\\Frontend\\ContentObject\\UserContentObject), Array)\n#17 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Controller/TypoScriptFrontendController.php(2853): TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer->cObjGetSingle('USER', Array)\n#18 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Controller/TypoScriptFrontendController.php(2811): TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController->processNonCacheableContentPartsAndSubstituteContentMarkers(Array, Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#19 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Controller/TypoScriptFrontendController.php(2780): TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController->recursivelyReplaceIntPlaceholdersInContent(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#20 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Http/RequestHandler.php(165): TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController->INTincScript(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#21 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Middleware/ResponsePropagation.php(34): TYPO3\\CMS\\Frontend\\Http\\RequestHandler->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#22 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Core\\Middleware\\ResponsePropagation->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(TYPO3\\CMS\\Frontend\\Http\\RequestHandler))\n#23 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/OutputCompression.php(48): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#24 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\OutputCompression->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#25 /var/www/vhosts/ekbo/releases/488/vendor/opsone-ch/varnish/Classes/Middleware/FrontendSendHeader.php(51): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#26 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): Opsone\\Varnish\\Middleware\\FrontendSendHeader->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#27 /var/www/vhosts/ekbo/releases/488/vendor/fluidtypo3/vhs/Classes/Middleware/AssetInclusion.php(18): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#28 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): FluidTYPO3\\Vhs\\Middleware\\AssetInclusion->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#29 /var/www/vhosts/ekbo/releases/488/vendor/apache-solr-for-typo3/solr/Classes/Middleware/PageIndexerFinisher.php(42): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#30 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): ApacheSolrForTypo3\\Solr\\Middleware\\PageIndexerFinisher->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#31 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/ContentLengthResponseHeader.php(45): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#32 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\ContentLengthResponseHeader->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#33 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/ShortcutAndMountPointRedirect.php(79): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#34 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\ShortcutAndMountPointRedirect->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#35 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/PrepareTypoScriptFrontendRendering.php(78): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#36 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\PrepareTypoScriptFrontendRendering->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#37 /var/www/vhosts/ekbo/releases/488/vendor/sjbr/sr-freecap/Classes/Middleware/EidHandler.php(63): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#38 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): SJBR\\SrFreecap\\Middleware\\EidHandler->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#39 /var/www/vhosts/ekbo/releases/488/vendor/ekd/plugin-form/Classes/Middleware/CaptchaMiddleware.php(119): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#40 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): EKD\\EkdPluginForm\\Middleware\\CaptchaMiddleware->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#41 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/TypoScriptFrontendInitialization.php(104): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#42 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\TypoScriptFrontendInitialization->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#43 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/PageArgumentValidator.php(132): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#44 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\PageArgumentValidator->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#45 /var/www/vhosts/ekbo/releases/488/vendor/apache-solr-for-typo3/solr/Classes/Middleware/PageIndexerInitialization.php(66): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#46 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): ApacheSolrForTypo3\\Solr\\Middleware\\PageIndexerInitialization->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#47 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/PreviewSimulator.php(66): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#48 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\PreviewSimulator->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#49 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/PageResolver.php(106): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#50 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\PageResolver->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#51 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/StaticRouteResolver.php(80): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#52 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\StaticRouteResolver->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#53 /var/www/vhosts/ekbo/releases/488/vendor/eliashaeussler/typo3-sitemap-robots/Classes/Middleware/RobotsTxtSitemapHandler.php(68): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#54 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): EliasHaeussler\\Typo3SitemapRobots\\Middleware\\RobotsTxtSitemapHandler->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#55 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/SiteBaseRedirectResolver.php(94): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#56 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\SiteBaseRedirectResolver->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#57 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-redirects/Classes/Http/Middleware/RedirectHandler.php(89): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#58 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Redirects\\Http\\Middleware\\RedirectHandler->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#59 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/FrontendUserAuthenticator.php(97): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#60 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\FrontendUserAuthenticator->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#61 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/BackendUserAuthenticator.php(78): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#62 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\BackendUserAuthenticator->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#63 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/MaintenanceMode.php(55): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#64 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\MaintenanceMode->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#65 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/EidHandler.php(64): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#66 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\EidHandler->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#67 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/SiteResolver.php(65): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#68 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\SiteResolver->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#69 /var/www/vhosts/ekbo/releases/488/vendor/apache-solr-for-typo3/solr/Classes/Middleware/SolrRoutingMiddleware.php(140): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#70 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): ApacheSolrForTypo3\\Solr\\Middleware\\SolrRoutingMiddleware->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#71 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Middleware/NormalizedParamsAttribute.php(45): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#72 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Core\\Middleware\\NormalizedParamsAttribute->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#73 /var/www/vhosts/ekbo/releases/488/vendor/sudhaus7/logformatter/Classes/MiddleWares/LogrequesturlMiddleWare.php(40): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#74 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): Sudhaus7\\Logformatter\\MiddleWares\\LogrequesturlMiddleWare->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#75 /var/www/vhosts/ekbo/releases/488/vendor/fluidtypo3/vhs/Classes/Middleware/RequestAvailability.php(14): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#76 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): FluidTYPO3\\Vhs\\Middleware\\RequestAvailability->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#77 /var/www/vhosts/ekbo/releases/488/vendor/ekd/privacystatement/Classes/Middleware/SchwingeMiddleware.php(46): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#78 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): EKD\\EkdPrivacystatement\\Middleware\\SchwingeMiddleware->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#79 /var/www/vhosts/ekbo/releases/488/vendor/ekd/privacystatement/Classes/Middleware/ReportscanMiddleware.php(38): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#80 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): EKD\\EkdPrivacystatement\\Middleware\\ReportscanMiddleware->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#81 /var/www/vhosts/ekbo/releases/488/vendor/ekd/privacystatement/Classes/Middleware/PreviewMiddleware.php(43): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#82 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): EKD\\EkdPrivacystatement\\Middleware\\PreviewMiddleware->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#83 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Middleware/VerifyHostHeader.php(55): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#84 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Core\\Middleware\\VerifyHostHeader->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#85 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Middleware/TimeTrackerInitialization.php(58): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#86 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(172): TYPO3\\CMS\\Frontend\\Middleware\\TimeTrackerInitialization->process(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest), Object(Psr\\Http\\Server\\RequestHandlerInterface@anonymous))\n#87 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/MiddlewareDispatcher.php(78): Psr\\Http\\Server\\RequestHandlerInterface@anonymous->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#88 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/AbstractApplication.php(86): TYPO3\\CMS\\Core\\Http\\MiddlewareDispatcher->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#89 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-frontend/Classes/Http/Application.php(69): TYPO3\\CMS\\Core\\Http\\AbstractApplication->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#90 /var/www/vhosts/ekbo/releases/488/vendor/typo3/cms-core/Classes/Http/AbstractApplication.php(100): TYPO3\\CMS\\Frontend\\Http\\Application->handle(Object(TYPO3\\CMS\\Core\\Http\\ServerRequest))\n#91 /var/www/vhosts/ekbo/releases/488/public/index.php(20): TYPO3\\CMS\\Core\\Http\\AbstractApplication->run()\n#92 /var/www/vhosts/ekbo/releases/488/public/index.php(21): {closure}()\n#93 {main}","code":"20240702172954ec9a59fe"}
	             */

				/* *
                $this->request->setArguments([]);
                $this->view = $this->setView('listAction');
                $content = $this->listAction();
				// */
	            $content = '';
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
			if (is_array($data) && isset($data['uid'])) {
				return $data['uid'];
			}
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
