<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;

class Prevent404ValueMapper extends PersistedAliasMapper
{
    public function resolve(string $value): ?string
    {
        $value = parent::resolve($value);

        // prevent returning of "null" because it would display the '404 error' page then
        if ($value === null) {
            return '-1';
        }
        return $value;
    }
}
