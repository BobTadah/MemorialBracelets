<?php

namespace MemorialBracelets\EngravingDisplay\Setup;

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
        $this->runAt('100.0.2', $context->getVersion(), function () use ($setup) {
            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_option'),
                'supportive_message_price',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => false,
                    'length' => '12,4',
                    'default' => 0.0000,
                    'comment' => 'Supportive Message Price'
                ]
            );
        });

        $this->runAt('100.0.3', $context->getVersion(), function () use ($setup) {
            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_option'),
                'name_engraving_price',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => false,
                    'length' => '12,4',
                    'default' => 0.0000,
                    'comment' => 'Name Engraving Price'
                ]
            );
        });
    }
}
