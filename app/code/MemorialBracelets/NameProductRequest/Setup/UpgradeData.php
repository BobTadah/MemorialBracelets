<?php

namespace MemorialBracelets\NameProductRequest\Setup;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Api\AttributeSetManagementInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\AttributeSetRepository;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use MemorialBracelets\NameProductRequest\Helper\AttributeSetLocator;
use Psr\Log\LoggerInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class UpgradeData
 * @package MemorialBracelets\NameProductRequest\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var EavSetupFactory $eavSetupFactory */
    protected $eavSetupFactory;

    /** @var AttributeSetFactory $attributeSetFactory */
    private $attributeSetFactory;

    /** @var LoggerInterface $logger */
    private $logger;

    /** @var AttributeSetRepository $attributeSetRepository */
    private $attributeSetRepository;

    /** @var CategorySetup $categorySetup */
    private $categorySetup;

    /** @var AttributeSetInterface $attributeSetInterface */
    private $attributeSetInterface;

    /** @var AttributeSetManagementInterface $attributeSetManagement */
    private $attributeSetManagement;

    /** @var AttributeSetLocator $attributeSetLocator */
    private $attributeSetLocator;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory                 $eavSetupFactory
     * @param AttributeSetFactory             $attributeSetFactory
     * @param LoggerInterface                 $logger
     * @param AttributeSetRepository          $attributeSetRepository
     * @param AttributeSetInterface           $attributeSetInterface
     * @param CategorySetup                   $categorySetup
     * @param AttributeSetManagementInterface $attributeSetManagement
     * @param AttributeSetLocator             $attributeSetLocator
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        LoggerInterface $logger,
        AttributeSetRepository $attributeSetRepository,
        AttributeSetInterface $attributeSetInterface,
        CategorySetup $categorySetup,
        AttributeSetManagementInterface $attributeSetManagement,
        AttributeSetLocator $attributeSetLocator
    ) {
        $this->eavSetupFactory        = $eavSetupFactory;
        $this->attributeSetFactory    = $attributeSetFactory;
        $this->logger                 = $logger;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->categorySetup          = $categorySetup;
        $this->attributeSetInterface  = $attributeSetInterface;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->attributeSetLocator    = $attributeSetLocator;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @return $this
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '100.0.2') < 0) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            //If Name Product attribute set exists, use it as skeleton.
            $skeletonId = null;
            try {
                //Use helper to find Name Product attribute set ID.
                $skeletonId = $this->attributeSetLocator->locate();
            } catch (\Exception $e) {
                //Name Product attribute set not found.
                return $setup->endSetup();
            }

            /** ----- CREATE NEW ATTRIBUTE SET ----- */
            // Get entity type.
            $entityTypeId = $this->categorySetup->getEntityTypeId(Product::ENTITY);

            // remove set in case script is re-running
            try { // this will fail if id not found
                $eavSetup->removeAttributeSet(
                    $entityTypeId,
                    $eavSetup->getAttributeSetId($entityTypeId, 'Special Request')
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }

            // Prepare attribute set object.
            $this->attributeSetInterface->setAttributeSetName('Special Request');
            $this->attributeSetInterface->setEntityTypeId($entityTypeId);
            $this->attributeSetInterface->setSortOrder(0);

            // Create new attribute set based on skeleton.
            $specialRequestAttributeSet = $this->attributeSetManagement->create(
                $entityTypeId,
                $this->attributeSetInterface,
                $skeletonId
            );

            /** ----- ADD NEW ATTRIBUTES TO SET ----- */
            // Group id where we want the new fields be added to.
            $groupId = $eavSetup->getAttributeGroupId(
                $entityTypeId,
                $specialRequestAttributeSet->getAttributeSetId(),
                'Name Information'
            );

            $newAttributes = [
                ['label' => 'Affiliation', 'code' => 'special_request_affiliation', 'sort'  => 10],
                ['label' => 'Event', 'code' => 'special_request_event', 'sort'  => 20],
                ['label' => 'Status', 'code' => 'special_request_status', 'sort'  => 30],
            ];

            /*
             * Here we will first remove the attribute in case it already exists (re-running script). Next
             * we will create the attribute anew. Lastly we will add it to the new attribute set,
             */
            foreach ($newAttributes as $attr) { // remove > create > add
                $eavSetup->removeAttribute(Product::ENTITY, $attr['code']);
                $eavSetup->addAttribute(
                    $entityTypeId,
                    $attr['code'],
                    [
                        'type'                    => 'varchar',
                        'label'                   => $attr['label'],
                        'backend'                 => '',
                        'input'                   => 'text',
                        'source'                  => '',
                        'required'                => false,
                        'sort_order'              => $attr['sort'],
                        'global'                  => Attribute::SCOPE_STORE,
                        'used_in_product_listing' => true,
                        'visible_on_front'        => true
                    ]
                );
                $eavSetup->addAttributeToSet(
                    $entityTypeId,
                    $specialRequestAttributeSet->getAttributeSetId(),
                    $groupId,
                    $attr['code']
                );
            }

            //  REMOVE OLD ATTRIBUTES FROM NEW SET (because it was created with the old skeleton).
            foreach (['affiliation', 'event', 'name_status'] as $attr) {
                $this->removeAttributeFromSet(
                    $entityTypeId,
                    $specialRequestAttributeSet->getAttributeSetId(),
                    $attr,
                    $eavSetup,
                    $setup
                );
            }
        }
    }

    /**
     * This function is used to remove an attribute from an specific attribute set.
     * @param                          $entityTypeId
     * @param                          $setId
     * @param                          $attributeId
     * @param EavSetup                 $eavSetup
     * @param ModuleDataSetupInterface $moduleSetup
     * @return $this
     */
    public function removeAttributeFromSet(
        $entityTypeId,
        $setId,
        $attributeId,
        EavSetup $eavSetup,
        ModuleDataSetupInterface $moduleSetup
    ) {
        $entityTypeId = $eavSetup->getEntityTypeId($entityTypeId);
        $setId        = $eavSetup->getAttributeSetId($entityTypeId, $setId);
        $attributeId  = $eavSetup->getAttributeId($entityTypeId, $attributeId);
        $table        = $moduleSetup->getTable('eav_entity_attribute');

        $bind   = ['attribute_set_id' => $setId, 'attribute_id' => $attributeId];
        $select = $moduleSetup->getConnection()
                              ->select()
                              ->from($table)
                              ->where('attribute_set_id = :attribute_set_id')
                              ->where('attribute_id = :attribute_id');
        $result = $moduleSetup->getConnection()->fetchRow($select, $bind);

        if ($result) {
            $where = ['entity_attribute_id =?' => $result['entity_attribute_id']];
            $moduleSetup->getConnection()->delete($table, $where);
        }

        return $this;
    }
}
