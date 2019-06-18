<?php

namespace MemorialBracelets\CharmOption\Setup;

use EasyUpgrade\CallableUpdateTrait;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    use CallableUpdateTrait;

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->runAt('100.0.1', $context->getVersion(), function () use ($setup) {
            $setup->getConnection()->addIndex(
                $setup->getTable('option_charm'),
                'title_fulltext',
                [
                    'title'
                ],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        });
    }
}
