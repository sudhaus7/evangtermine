<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Routing\Aspect;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Prevent404ValueMapper extends PersistedAliasMapper
{
    const TABLE = 'tx_evangtermine_domain_model_event';

    /**
     * @throws Exception
     */
    public function resolve(string $value): ?string
    {
        $urlPartArray = explode('/', $value);
        if (empty(end($urlPartArray))) {
            array_pop($urlPartArray);
        }
        $slug = end($urlPartArray);
        $value = parent::resolve($value);

        // prevent returning of "null" because it would display the '404 error' page then
        if ($value === null) {
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $queryBuilder = $connectionPool->getQueryBuilderForTable(self::TABLE);
            $queryBuilder->count('*')
                ->from(self::TABLE)
                ->where(
                    $queryBuilder->expr()->like(
                        'slug',
                        $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($slug), Connection::PARAM_STR)
                    )
                );
            $result = $queryBuilder->executeQuery()->fetchOne();
            if ($result == 0) {
                return '-1';
            }
        }
        return $value;
    }
}
