<?php

namespace MemorialBracelets\SupportiveMessages\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('memorialbracelets_supportivemessage')
        )->addColumn(
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
            'message',
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
            ],
            'Supportive Message'
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
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
