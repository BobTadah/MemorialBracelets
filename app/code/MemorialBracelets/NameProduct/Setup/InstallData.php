<?php

namespace MemorialBracelets\NameProduct\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use MemorialBracelets\NameProduct\Model\ResourceModel\Product\Link;

class InstallData implements InstallDataInterface
{
    private $setupFactory;

    public function __construct(EavSetupFactory $setupFactory)
    {
        $this->setupFactory = $setupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $data = [
            'link_type_id' => Link::LINK_TYPE_NAME,
            'code' => 'name',
        ];

        $setup->getConnection()
            ->insertOnDuplicate($setup->getTable('catalog_product_link_type'), $data);

        $select = $setup->getConnection()
            ->select()
            ->from(['c' => $setup->getTable('catalog_product_link_attribute')])
            ->where('c.link_type_id=?', Link::LINK_TYPE_NAME);

        $result = $setup->getConnection()->fetchAll($select);

        if (!$result) {
            $data = [
                [
                    'link_type_id' => Link::LINK_TYPE_NAME,
                    'product_link_attribute_code' => 'position',
                    'data_type' => 'int',
                ],
                [
                    'link_type_id' => Link::LINK_TYPE_NAME,
                    'product_link_attribute_code' => 'qty',
                    'data_type' => 'decimal',
                ],
            ];

            $setup->getConnection()->insertMultiple($setup->getTable('catalog_product_link_attribute'), $data);
        }
    }
}
