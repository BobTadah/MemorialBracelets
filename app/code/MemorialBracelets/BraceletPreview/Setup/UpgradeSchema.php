<?php

namespace MemorialBracelets\BraceletPreview\Setup;

use EasyUpgrade\CallableUpdateTrait;
use Magento\Framework\DB\Ddl\Table;
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
        $this->runAt('100.1.0', $context->getVersion(), function () use ($setup) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('mb_preview_product_option_preview_piece'))
                ->addColumn(
                    'option_preview_piece_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Option Type Preview Piece ID'
                )
                ->addColumn(
                    'option_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Option ID'
                )
                ->addColumn(
                    'piece',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Option Preview Piece'
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'mb_preview_product_option_preview_piece',
                        'option_type_id',
                        'catalog_product_option',
                        'option_id'
                    ),
                    'option_id',
                    $setup->getTable('catalog_product_option'),
                    'option_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Catalog Product Option Preview Piece Table');

            $setup->getConnection()->createTable($table);
        });
    }
}
