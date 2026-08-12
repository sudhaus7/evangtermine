<?php

declare(strict_types=1);

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

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Event;
use ArbkomEKvW\Evangtermine\Event\ModifyEvangTermineShowActionViewEvent;
use ArbkomEKvW\Evangtermine\Services\DetailPageService;
use ArbkomEKvW\Evangtermine\Services\Events\EventsServiceInterface;
use ArbkomEKvW\Evangtermine\Util\ExtConf;
use ArbkomEKvW\Evangtermine\Util\SettingsUtility;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * EventcontainerController
 */
class EventcontainerController extends ActionController
{
    protected CacheManager $cacheManager;
    protected \DateTime $date;
    protected RenderingContextFactory $renderingContextFactory;

    /**
     * Uid value of current tt_content record
     * serves as unique id of this plugin instance, used for session identification
     */
    private int $currentPluginUid;

    private SettingsUtility $settingsUtility;

    private EtKeys $etkeys;
    private ExtConf $extconf;
    private bool $importEvents;

    public function __construct(private readonly EventsServiceInterface $eventsService, private readonly DetailPageService $detailPageService, CacheManager $cacheManager, SettingsUtility $settingsUtility, RenderingContextFactory $renderingContextFactory)
    {
        $this->cacheManager = $cacheManager;
        $this->date = new \DateTime();
        $this->settingsUtility = $settingsUtility;
        $this->renderingContextFactory = $renderingContextFactory;
        $this->extconf = GeneralUtility::makeInstance(ExtConf::class);
        $this->importEvents = (bool)$this->extconf->getExtConfArray()['importEvents'] ?? false;
    }

    protected function initializeAction(): void
    {
        $this->currentPluginUid = $this->request->getAttribute('currentContentObject')->data['uid'];
    }

    protected function initializeListAction(): void
    {
        $this->settingsUtility->setDestination($this->settings);
    }

    protected function initializeTeaserAction(): void
    {
        $this->settingsUtility->setDestination($this->settings);
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
     * - must collect all parameters (etkeys) from config-settings, session and request
     * - update session
     * - retrieve Event data (from XML or DB)
     * - hand it to view
     * @return ResponseInterface
     * @throws NoSuchCacheException
     * @throws Exception
     */
    public function listAction(): ResponseInterface
    {
        $requestArguments = $this->request->getArguments();
        $formArguments = $requestArguments['etkeysForm'] ?? [];
        $this->etkeys = $this->getNewFromSettings();

        // if it's a search, redirect to switch from "POST" to "GET
        // we do this to prevent ERR_CACHE_MISS errors
        if (!empty($formArguments) && empty($requestArguments['redirect'])) {
            if (empty($requestArguments['sf_reset'])) {
                return $this->redirect(
                    'list',
                    'Eventcontainer',
                    'Evangtermine',
                    array_merge($formArguments, ['redirect' => 1]),
                    $data['pid'] ?? NULL,
                );
            } else {
                return $this->redirect(
                    'list',
                    'Eventcontainer',
                    'Evangtermine',
                    ['redirect' => 1],
                    $data['pid'] ?? NULL,
                );
            }
        }

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
            $data = $this->request->getAttribute('currentContentObject')->data;

            [$events, $nrOfEvents] = $this->eventsService->getEvents($this->etkeys, $this->settings);
            $pager = $this->eventsService->createPager($this->etkeys, $nrOfEvents);

            // hand model data to the view
            $this->view->assignMultiple([
                'events' => $events ?? [],
                'nrOfEvents' => $nrOfEvents,
                'etkeys' => $this->etkeys,
                'pageId' => $GLOBALS['TSFE']->id,
                'pluginUid' => $this->currentPluginUid,
                'categoryList' => $this->eventsService->getCategoryList($this->settings, $this->currentPluginUid),
                'groupList' => $this->eventsService->getGroupList($this->settings, $this->currentPluginUid),
                'placeList' => $this->eventsService->getPlaceList($this->settings, $this->currentPluginUid),
                'regionList' => $this->eventsService->getRegionList($this->settings, $this->currentPluginUid),
                'pagerdata' => $pager->getPgr(),
                'data' => $data,
                'detailPage' => $this->detailPageService->getUid(),
                'detailPagePluginUid' => $this->detailPageService->getPluginUid($data),
                'importEvents' => $this->importEvents,
            ]);

            $content = $this->view->render();
            $this->setCache($content, $events, $cache, $cacheKey);
        }
        return $this->htmlResponse($content);
    }

    /**
     * @throws NoSuchCacheException
     * @throws Exception
     */
    public function teaserAction(): ResponseInterface
    {
        $this->etkeys = $this->getNewFromSettings();
        $data = $this->request->getAttribute('currentContentObject')->data;

        $cache = $this->cacheManager->getCache('evangtermine_event_teaser');
        $cacheKey = $this->getCacheKey('', $data['uid']);
        $content = $cache->get($cacheKey);

        if (empty($content)) {
            [$events, $nrOfEvents] = $this->eventsService->getEvents($this->etkeys, $this->settings);

            // hand model data to the view
            $this->view->assign('events', $events ?? []);
            $this->view->assign('pageId', $GLOBALS['TSFE']->id);
            $this->view->assign('data', $data);
            $this->view->assign('detailPage', $this->detailPageService->getUid());
            $this->view->assign('detailPagePluginUid', $this->detailPageService->getPluginUid($data));
            $this->view->assign('importEvents', $this->importEvents);

            $content = $this->view->render();
            $this->setCache($content, $events, $cache, $cacheKey);
        }
        return $this->htmlResponse($content);
    }

    /**
     * action show
     * @throws NoSuchCacheException
     * @throws Exception
     */
    public function showAction(): ResponseInterface
    {
        $data = $this->request->getAttribute('currentContentObject')->data;

        // If the current plugin is a 'detail' plugin, or if it is the plugin in which the user clicked on a link.
        // We need this for multiple evang. Termine plugins on one site.
        if ($this->pluginIsDetailPlugin($data)) {
            $uid = $this->request->getArguments()['uid'] ??
                $this->request->getArguments()['ID'] ??
                $this->request->getQueryParams()['tx_evangtermine_list']['uid'] ??
                $this->request->getQueryParams()['tx_evangtermine_list']['ID'] ??
                null;

            if ($uid == -1) {
                return $this->redirectToListPage($data['pid']);
            }

            if (!empty($uid)) {
                [$event, $meta, $detailItems] = $this->eventsService->findByUid($uid);

                if (!$this->eventFitsPlugin($event)) {
                    return $this->redirectToListPage($data['pid']);
                }

                // hand model data to the view
                $this->view->assign('event', $event);
                $this->view->assign('meta', $meta);
                $this->view->assign('detailitems', $detailItems);
                $this->view->assign('eventhost', $this->extconf->getExtConfArray()['host']);
                $this->view->assign('categoryList', $this->eventsService->getCategoryList($this->settings, $this->currentPluginUid));
                $this->view->assign('groupList', $this->eventsService->getGroupList($this->settings, $this->currentPluginUid));
                $this->view->assign('data', $data);
                $this->view->assign('importEvents', $this->importEvents);

                if (!empty($event) && !empty($eventDispatcher)) {
                    $eventDispatcher->dispatch(
                        new ModifyEvangTermineShowActionViewEvent($this->view, $event)
                    );
                }
            } else {
                $this->addFlashMessage('Keine Event-ID übergeben', '', ContextualFeedbackSeverity::ERROR);
                $this->redirect('genericinfo');
            }
        } else {
            return $this->renderContentOfTeaserOrList($data['list_type']);
        }
        return $this->htmlResponse();
    }

    /**
     * action genericinfo
     */
    public function genericinfoAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

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

        $renderingContext = $this->renderingContextFactory->create();
        $renderingContext->setControllerName('Eventcontainer');
        $renderingContext->setControllerAction($actionName);

        $templatePaths = GeneralUtility::makeInstance(TemplatePaths::class);
        $templatePaths->setTemplateRootPaths($templateRootPaths);
        $templatePaths->setPartialRootPaths($partialRootPaths);
        $templatePaths->setLayoutRootPaths($layoutRootPaths);
        $renderingContext->setTemplatePaths($templatePaths);
        $this->view = GeneralUtility::makeInstance(TemplateView::class, $renderingContext);
        return $this->view;
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

    protected function eventFitsPlugin(Event $event): bool
    {
        foreach ($this->settings as $key => $setting) {
            switch ($key) {
                case 'etkey_vid':
                    if (!empty($setting) && !in_array($event->getEventUserId(), explode(',', $setting))) {
                        return false;
                    }
                    break;
                case 'etkey_highlight':
                    if ($setting == 'high' && $event->getHighlight() <= 1) {
                        return false;
                    }
                    break;
                case 'etkey_eventtype':
                    if ($setting !== 'all') {
                        $settingArray = explode(',', $setting);
                        $found = false;
                        foreach (explode(',', $event->getCategories()) as $item) {
                            if (in_array($item, $settingArray)) {
                                $found = true;
                            }
                        }
                        if (!$found) {
                            return false;
                        }
                    }
                    break;
                case 'etkey_people':
                    if ($setting != 0 && $setting != 'all') {
                        $settingArray = explode(',', $setting);
                        $found = false;
                        foreach (explode(',', $event->getPeople()) as $item) {
                            if (in_array($item, $settingArray)) {
                                $found = true;
                            }
                        }
                        if (!$found) {
                            return false;
                        }
                    }
                    break;
                case 'etkey_regions':
                    if ($setting !== 'all') {
                        $settingArray = explode(',', $setting);
                        unset($settingArray['all']);
                        unset($settingArray['alleBezirke']);
                        unset($settingArray['alleKreise']);
                        if (!empty($settingArray)) {
                            $found = false;
                            foreach (explode(',', $event->getRegion()) as $item) {
                                if (in_array($item, $settingArray)) {
                                    $found = true;
                                }
                            }
                            if (!$found) {
                                return false;
                            }
                        }
                    }
                    break;
                case 'etkey_subregions':
                    if ($setting !== 'all') {
                        $settingArray = explode(',', $setting);
                        $found = false;
                        foreach (explode(',', $event->getEventSubregionId()) as $item) {
                            if (in_array($item, $settingArray)) {
                                $found = true;
                            }
                        }
                        if (!$found) {
                            return false;
                        }
                    }
                    break;
                case 'etkey_regions2':
                    if ($setting !== 'all') {
                        $settingArray = explode(',', $setting);
                        $found = false;
                        foreach (explode(',', $event->getEventRegion2Id()) as $item) {
                            if (in_array($item, $settingArray)) {
                                $found = true;
                            }
                        }
                        if (!$found) {
                            return false;
                        }
                    }
                    break;
                case 'etkey_regions3':
                    if ($setting !== 'all') {
                        $settingArray = explode(',', $setting);
                        $found = false;
                        foreach (explode(',', $event->getEventRegion3Id()) as $item) {
                            if (in_array($item, $settingArray)) {
                                $found = true;
                            }
                        }
                        if (!$found) {
                            return false;
                        }
                    }
                    break;
                case 'etkey_places':
                    if ($setting !== 'all') {
                        $settingArray = explode(',', $setting);
                        if (!in_array($event->getEventPlaceId(), $settingArray)) {
                            return false;
                        }
                    }
                    break;
            }
        }
        return true;
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

    /**
     * @param string $content
     * @param mixed $events
     * @param FrontendInterface $cache
     * @param string $cacheKey
     */
    protected function setCache(string $content, mixed $events, FrontendInterface $cache, string $cacheKey): void
    {
        if ($this->ifContentIsNotEmpty($content, $events ?? [])) {
            $cache->set($cacheKey, $content);
        }
    }

    /**
     * @param $list_type
     * @return ResponseInterface
     * @throws Exception
     * @throws NoSuchCacheException
     */
    protected function renderContentOfTeaserOrList($list_type): ResponseInterface
    {
        if ($list_type == 'evangtermine_teaser') {
            return $this->teaserAction();
        }
        $this->view = $this->setView('list');
        return $this->listAction();
    }

    /**
     * @param $pid
     * @return ResponseInterface
     */
    protected function redirectToListPage($pid): ResponseInterface
    {
        $url = $this->uriBuilder->reset()
            ->setTargetPageUid($pid ?? 0)
            ->build();

        return $this->responseFactory->createResponse()
            ->withStatus(404, '404 Not Found')
            ->withHeader('Refresh', '0,url=' . $url);
    }
}
