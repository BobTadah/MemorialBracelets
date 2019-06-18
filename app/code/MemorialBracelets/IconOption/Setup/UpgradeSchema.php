<?php

namespace MemorialBracelets\IconOption\Setup;

use EasyUpgrade\CallableUpdateTrait;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    use CallableUpdateTrait;

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->runAt('100.0.1', $context->getVersion(), function () use ($setup) {
            $table = $setup->getTable('option_icon');

            $setup->getConnection()->addColumn(
                $table,
                'price',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0.0000',
                    'comment' => 'Price',
                ]
            );

            $setup->getConnection()->addColumn(
                $table,
                'price_type',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Price Type',
                ]
            );
        });

        $this->runAt('100.0.2', $context->getVersion(), function () use ($setup) {
            $table = $setup->getTable('option_icon');

            $setup->getConnection()->dropIndex($table, 'option_icon_fulltext');
            $setup->getConnection()->addIndex(
                $table,
                $setup->getIdxName($table, ['title'], AdapterInterface::INDEX_TYPE_FULLTEXT),
                ['title'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        });
    }
}
