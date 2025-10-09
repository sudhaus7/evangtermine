<?php

namespace ArbkomEKvW\Evangtermine\Services;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DetailPageService
{
    /**
     * @throws Exception
     */
    public function getUid(): int
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
     */
    public function getPluginUid(array $data): int
    {
        $detailPage = $this->settings['opmode_detailpage'] ?? 0;
        if (empty($detailPage)) {
            if (isset($data['uid'])) {
                return $data['uid'];
            }
            return 0;
        }

        /** @var ConnectionPool $connectionPool */
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
}
