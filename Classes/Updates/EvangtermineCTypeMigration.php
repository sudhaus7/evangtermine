<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('arbkomEKvW_evangtermineCTypeMigration')]
final class EvangtermineCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Migrate "Evangtermine" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The "Evangtermine" plugins are now registered as content element. Update migrates existing records and backend user permissions.';
    }

    /**
     * This must return an array containing the "list_type" to "CType" mapping
     *
     *  Example:
     *
     *  [
     *      'pi_plugin1' => 'pi_plugin1',
     *      'pi_plugin2' => 'new_content_element',
     *  ]
     *
     * @return array<string, string>
     */
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'evangtermine_list' => 'evangtermine_list',
            'evangtermine_detail' => 'evangtermine_detail',
            'evangtermine_teaser' => 'evangtermine_teaser',
        ];
    }
}
