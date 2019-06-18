<?php

namespace MemorialBracelets\SwatchOption\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->addSwatchOptions($setup);
        $setup->endSetup();
    }

    protected function addSwatchOptions(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('mb_swatch_product_option_type_swatch'))
            ->addColumn(
                'option_type_swatch_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Type Swatch ID'
            )
            ->addColumn(
                'option_type_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option Type ID'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'swatch_abbr',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Swatch Option Abbreviation'
            )
            ->addColumn(
                'swatch_color',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Swatch Option Color'
            )
            ->addColumn(
                'image',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'Swatch Option Image'
            )
            ->addForeignKey(
                $setup->getFkName(
                    'mb_swatch_product_option_type_swatch',
                    'option_type_id',
                    'catalog_product_option_type_value',
                    'option_type_id'
                ),
                'option_type_id',
                $setup->getTable('catalog_product_option_type_value'),
                'option_type_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName('mb_swatch_product_option_type_swatch', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment(
                'Catalog Product Option Type Title Table'
            );

        $setup->getConnection()->createTable($table);
    }
}
