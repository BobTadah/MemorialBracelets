<?php

namespace MemorialBracelets\FavoriteProducts\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogAttribute;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 *
 * @package MemorialBracelets\FavoriteProducts\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var categorySetupFactory
     */
    private $catalogSetupFactory;

    /**
     * InstallData constructor.
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->catalogSetupFactory = $categorySetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $catalogSetup */
        $catalogSetup = $this->catalogSetupFactory->create(['setup' => $setup]);

        $productTypes = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ];
        $productTypes = join(',', $productTypes);

        /** if this script needs to be re-run, uncomment this line to remove the attribute before re-running. */
        /*$catalogSetup->removeAttribute(Product::ENTITY, 'favorite_product');*/

        /** Add attributes to the eav/attribute*/
        $catalogSetup->addAttribute(
            Product::ENTITY,
            'favorite_product',
            [
                'type' => 'int',
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Boolean',
                'frontend' => '',
                'input' =>  'boolean',
                'label' => 'Favorite Products',
                'class' => '',
                'source' => '',
                'group' => 'General',
                'global' => CatalogAttribute::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => $productTypes
            ]
        );
    }
}
