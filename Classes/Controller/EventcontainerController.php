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

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;
use ArbkomEKvW\Evangtermine\Domain\Model\Event;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventcontainerRepository;
use ArbkomEKvW\Evangtermine\Domain\Repository\EventRepository;
use ArbkomEKvW\Evangtermine\Event\ModifyEvangTermineShowActionViewEvent;
use ArbkomEKvW\Evangtermine\Util\Etpager;
use ArbkomEKvW\Evangtermine\Util\ExtConf;
use ArbkomEKvW\Evangtermine\Util\SettingsUtility;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidControllerNameException;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;

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

    public function __construct(CacheManager $cacheManager, SettingsUtility $settingsUtility, EventcontainerRepository $eventcontainerRepository, EventRepository $eventRepository)
    {
        $this->cacheManager = $cacheManager;
        $this->date = new \DateTime();
        $this->settingsUtility = $settingsUtility;
        $this->eventcontainerRepository = $eventcontainerRepository;
        $this->eventRepository = $eventRepository;
    }

    protected function initializeAction(): void
    {
        $this->currentPluginUid = $this->request->getAttribute('currentContentObject')->data['uid'];
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
     * @return ResponseInterface
     * @throws Exception
     * @throws NoSuchCacheException
     * @throws UnexpectedTypeException
     * @throws \Doctrine\DBAL\Exception
     */
    public function listAction(): \Psr\Http\Message\ResponseInterface
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

            $data = $this->request->getAttribute('currentContentObject')->data;
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
        return $this->htmlResponse($content);
    }

    /**
     * @throws Exception
     * @throws NoSuchCacheException
     * @throws UnexpectedTypeException
     * @throws \Doctrine\DBAL\Exception
     */
    public function teaserAction(): \Psr\Http\Message\ResponseInterface
    {
        $this->etkeys = $this->getNewFromSettings();
        $data = $this->request->getAttribute('currentContentObject')->data;

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
        return $this->htmlResponse($content);
    }

    /**
     * action show
     * @throws Exception
     * @throws NoSuchCacheException
     * @throws UnexpectedTypeException
     * @throws InvalidControllerNameException
     * @throws \Doctrine\DBAL\Exception
     */
    public function showAction(): \Psr\Http\Message\ResponseInterface
    {
        $data = $this->request->getAttribute('currentContentObject')->data;

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
                $this->view->assign('data', $this->request->getAttribute('currentContentObject')->data);

                if (!empty($event)) {
                    $this->eventDispatcher->dispatch(
                        new ModifyEvangTermineShowActionViewEvent($this->view, $event)
                    );
                }
            } else {
                $this->addFlashMessage('Keine Event-ID übergeben', '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
                $this->redirect('genericinfo');
            }
        } else {
            // render content of teaser or list
            if ($data['list_type'] == 'evangtermine_teaser') {
                return $this->teaserAction();
            } else {
                $this->view = $this->setView('list');
                return $this->listAction();
            }
        }
        return $this->htmlResponse();
    }

    /**
     * action genericinfo
     */
    public function genericinfoAction(): \Psr\Http\Message\ResponseInterface
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

        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
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

    /**
     * @throws \Doctrine\DBAL\Exception
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
     * @throws \Doctrine\DBAL\Exception
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
