<?php

namespace MemorialBracelets\NameProduct\Setup;

use EasyUpgrade\CallableUpdateTrait;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use MemorialBracelets\NameProduct\Model\Product\Type\Name;
use MemorialBracelets\NameProduct\Model\ResourceModel\Product\Link;

class UpgradeData implements UpgradeDataInterface
{
    use CallableUpdateTrait;

    /** @var EavSetupFactory */
    public $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @noinspection PhpMissingBreakStatementInspection
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->runAt(
            '100.1.0',
            $context->getVersion(),
            function () use ($setup) {
                /** @var \Magento\Framework\Db\Select $select */
                $select = $setup->getConnection()->select()
                    ->from(['c' => $setup->getTable('catalog_product_link_attribute')])
                    ->where('c.link_type_id = ?', Link::LINK_TYPE_NAME)
                    ->where('c.product_link_attribute_code = ?', 'qty');

                $setup->getConnection()->deleteFromSelect($select, $setup->getTable('catalog_product_link_attribute'));
            }
        );

        $this->runAt(
            '100.1.1',
            $context->getVersion(),
            function () use ($setup) {
                /** @var EavSetup $eavSetup */
                $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    'special_engraving',
                    [
                        'type' => 'text',
                        'backend' => DefaultBackend::class,
                        'input' =>  'textarea',
                        'label' => 'Special Engraving',
                        'class' => '',
                        'source' => '',
                        'group' => 'Name Information',
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => false,
                        'default' => '',
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => true,
                        'unique' => false,
                        'multiline_count' => 3,
                        'apply_to' => join(',', [Name::TYPE_CODE])
                    ]
                );
            }
        );
    }
}
