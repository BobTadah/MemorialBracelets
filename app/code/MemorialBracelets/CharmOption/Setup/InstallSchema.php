<?php
namespace MemorialBracelets\CharmOption\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $charmTableName = $setup->getTable('option_charm');

        $charmTable = $setup->getConnection()
            ->newTable($charmTableName)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'identity' => true,
                ]
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Label for the Icon'
            )
            ->addColumn(
                'position',
                Table::TYPE_INTEGER,
                null,
                [],
                'Position in List'
            )
            ->addColumn(
                'icon',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Path in media/option_charm'
            )
            ->addColumn(
                'price',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Price'
            )
            ->addColumn(
                'price_type',
                Table::TYPE_TEXT,
                7,
                ['nullable' => false, 'default' => 'fixed'],
                'Price Type'
            )
            ->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 1],
                'Is currently active'
            );

        $setup->getConnection()->createTable($charmTable);

        $setup->endSetup();
    }
}
