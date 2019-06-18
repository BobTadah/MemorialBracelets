<?php

namespace MemorialBracelets\IconOption\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /** {@inheritdoc} */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $iconTableName = $setup->getTable('option_icon');

        $iconTable = $setup->getConnection()
            ->newTable($iconTableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Entity ID'
            )->addColumn(
                'position',
                Table::TYPE_INTEGER,
                null,
                [],
                'Position in List'
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ],
                'Title'
            )->addColumn(
                'icon',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true,
                ],
                'Icon'
            )->addColumn(
                'creation_time',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT,
                ],
                'Creation Time'
            )->addColumn(
                'update_time',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT_UPDATE,
                ],
                'Modification Time'
            )->addColumn(
                'is_active',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default' => '1',
                ],
                'Is Active'
            )->addIndex(
                'option_icon_fulltext',
                ['title'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            );

        $setup->getConnection()->createTable($iconTable);

        $setup->endSetup();
    }
}
