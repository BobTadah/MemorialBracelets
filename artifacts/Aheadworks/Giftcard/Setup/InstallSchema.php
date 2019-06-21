<?php
namespace Aheadworks\Giftcard\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'aw_giftcard'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_giftcard'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Giftcard Id'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Giftcard Code'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Giftcard Type'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'expire_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => null],
                'Expired At'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Website Id'
            )
            ->addColumn(
                'balance',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'default' => '0.00'],
                'Balance'
            )
            ->addColumn(
                'initial_balance',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'default' => '0.00'],
                'Initial Balance'
            )
            ->addColumn(
                'state',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'State'
            )
            ->addColumn(
                'is_used',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => \Aheadworks\Giftcard\Model\Source\Giftcard\IsUsed::NO_VALUE],
                'Is Used'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Order ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product ID'
            )
            ->addColumn(
                'email_template',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => '0'],
                'Email Template'
            )
            ->addColumn(
                'sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Sender Name'
            )
            ->addColumn(
                'sender_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Sender Email'
            )
            ->addColumn(
                'recipient_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Recipient Name'
            )
            ->addColumn(
                'recipient_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Recipient Email'
            )
            ->addIndex(
                $installer->getIdxName('aw_giftcard', ['website_id']),
                ['website_id']
            )
            ->addForeignKey(
                $installer->getFkName('aw_giftcard', 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Giftcard');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_giftcard_history'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_giftcard_history'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'History Id'
            )
            ->addColumn(
                'giftcard_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Giftcard Id'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Updated At'
            )
            ->addColumn(
                'action',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Action'
            )
            ->addColumn(
                'balance_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'default' => '0.00'],
                'Balance Amount'
            )
            ->addColumn(
                'balance_delta',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false, 'default' => '0.00'],
                'Balance Delta'
            )
            ->addColumn(
                'additional_info',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Additional Info'
            )
            ->addIndex(
                $installer->getIdxName('aw_giftcard_history', ['giftcard_id']),
                ['giftcard_id']
            )
            ->addForeignKey(
                $installer->getFkName('aw_giftcard_history', 'giftcard_id', 'aw_giftcard', 'id'),
                'giftcard_id',
                $installer->getTable('aw_giftcard'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Giftcard History');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_giftcard_quote'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_giftcard_quote'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'giftcard_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Giftcard Id'
            )
            ->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Quote Id'
            )
            ->addColumn(
                'base_giftcard_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['unsigned' => true, 'default' => null],
                'Base Giftcard Amount'
            )
            ->addColumn(
                'giftcard_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['unsigned' => true, 'default' => null],
                'Giftcard Amount'
            )
            ->addIndex(
                $installer->getIdxName('aw_giftcard_quote', ['giftcard_id']),
                ['giftcard_id']
            )
            ->setComment('Giftcard To Quote Linkage Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_giftcard_invoice'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_giftcard_invoice'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'giftcard_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Giftcard Id'
            )
            ->addColumn(
                'invoice_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Invoice Id'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Order ID'
            )
            ->addColumn(
                'base_giftcard_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['unsigned' => true, 'default' => null],
                'Base Giftcard Amount'
            )
            ->addColumn(
                'giftcard_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['unsigned' => true, 'default' => null],
                'Giftcard Amount'
            )
            ->addIndex(
                $installer->getIdxName('aw_giftcard_invoice', ['giftcard_id']),
                ['giftcard_id']
            )
            ->setComment('Giftcard To Invoice Linkage Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_giftcard_creditmemo'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_giftcard_creditmemo'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'giftcard_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Giftcard Id'
            )
            ->addColumn(
                'creditmemo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Creditmemo Id'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Order ID'
            )
            ->addColumn(
                'base_giftcard_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['unsigned' => true, 'default' => null],
                'Base Giftcard Amount'
            )
            ->addColumn(
                'giftcard_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['unsigned' => true, 'default' => null],
                'Giftcard Amount'
            )
            ->addIndex(
                $installer->getIdxName('aw_giftcard_creditmemo', ['giftcard_id']),
                ['giftcard_id']
            )
            ->setComment('Giftcard To Creditmemo Linkage Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_giftcard_product_entity_amounts'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_giftcard_product_entity_amounts'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ValueId'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Value'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Website ID'
            )
            ->addIndex(
                $installer->getIdxName('aw_giftcard_product_entity_amounts', ['website_id']),
                ['website_id']
            )
            ->addForeignKey(
                $installer->getFkName('aw_giftcard_product_entity_amounts', 'entity_id', 'catalog_product_entity', 'entity_id'),
                'entity_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('aw_giftcard_product_entity_amounts', 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Giftcard Product Amounts Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_giftcard_product_entity_templates'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_giftcard_product_entity_templates'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ValueId'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Value'
            )
            ->addColumn(
                'image',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Image'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store ID'
            )
            ->addIndex(
                $installer->getIdxName('aw_giftcard_product_entity_templates', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('aw_giftcard_product_entity_templates', 'entity_id', 'catalog_product_entity', 'entity_id'),
                'entity_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('aw_giftcard_product_entity_templates', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Giftcard Product Email Templates Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_giftcard_statistics'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_giftcard_statistics'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product ID'
            )
            ->addColumn(
                'purchased_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0],
                'Purchased Qty'
            )
            ->addColumn(
                'purchased_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['default' => '0.00'],
                'Purchased Amount'
            )
            ->addColumn(
                'used_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0],
                'Used Qty'
            )
            ->addColumn(
                'used_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['default' => '0.00'],
                'Used Amount'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store ID'
            )
            ->addForeignKey(
                $installer->getFkName('aw_giftcard_statistics', 'product_id', 'catalog_product_entity', 'entity_id'),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('aw_giftcard_statistics', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Giftcard Statictics Table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
