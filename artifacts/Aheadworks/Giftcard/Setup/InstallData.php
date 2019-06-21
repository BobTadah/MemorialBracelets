<?php
namespace Aheadworks\Giftcard\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetup
     */
    protected $eavSetup;

    /**
     * @var string
     */
    protected $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;

    /**
     * @var string
     */
    protected $giftCardInfoGroupName = 'Gift Card Information';

    /**
     * @var int
     */
    protected $giftCardInfoGroupSortOrder = 100;

    /**
     * @var string
     */
    protected $giftCardTypeCode = \Aheadworks\Giftcard\Model\Product\Type\Giftcard::TYPE_CODE;

    /**
     * @var \Aheadworks\Giftcard\Model\Email\Sample
     */
    protected $sampleEmailTemplates;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $emailTemplateFactory;

    /**
     * @param EavSetup $eavSetup
     * @param \Magento\Email\Model\TemplateFactory $emailTemplateFactory
     * @param \Aheadworks\Giftcard\Model\Email\Sample $sampleEmailTemplates
     */
    public function __construct(
        EavSetup $eavSetup,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Aheadworks\Giftcard\Model\Email\Sample $sampleEmailTemplates
    ) {
        $this->eavSetup = $eavSetup;
        $this->sampleEmailTemplates = $sampleEmailTemplates;
        $this->emailTemplateFactory = $emailTemplateFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($attributeSetId = $this->eavSetup->getAttributeSet($this->entityTypeId, 'Default', 'attribute_set_id')) {
            $this->eavSetup
                ->addAttributeGroup(
                    $this->entityTypeId,
                    $attributeSetId,
                    $this->giftCardInfoGroupName,
                    $this->giftCardInfoGroupSortOrder
                )
                ->updateAttributeGroup(
                    $this->entityTypeId,
                    $attributeSetId,
                    $this->giftCardInfoGroupName,
                    'attribute_group_code',
                    'aw-giftcard-info'
                )
                ->updateAttributeGroup(
                    $this->entityTypeId,
                    $attributeSetId,
                    $this->giftCardInfoGroupName,
                    'tab_group_code',
                    'basic'
                )
            ;
        }
        $this->eavSetup
            ->addAttribute(
                $this->entityTypeId,
                TypeGiftCard::ATTRIBUTE_CODE_TYPE,
                [
                    'type' => 'int',
                    'label' => 'Card Type',
                    'input' => 'select',
                    'required' => true,
                    'frontend' => 'Aheadworks\Giftcard\Model\Product\Entity\Attribute\Frontend\CardType',
                    'source' => 'Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'used_for_sort_by' => false,
                    'apply_to' => $this->giftCardTypeCode,
                    'group' => $this->giftCardInfoGroupName,
                    'sort_order' => 1,
                ]
            )
            ->addAttribute(
                $this->entityTypeId,
                TypeGiftCard::ATTRIBUTE_CODE_DESCRIPTION,
                [
                    'type' => 'text',
                    'label' => 'Card Description',
                    'input' => 'textarea',
                    'wysiwyg_enabled' => true,
                    'required' => false,
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'used_for_sort_by' => false,
                    'apply_to' => $this->giftCardTypeCode,
                    'group' => $this->giftCardInfoGroupName,
                    'sort_order' => 2,
                ]
            )
            ->addAttribute(
                $this->entityTypeId,
                TypeGiftCard::ATTRIBUTE_CODE_EXPIRE,
                [
                    'type' => 'int',
                    'label' => 'Expires After (days)',
                    'input' => 'text',
                    'required' => false,
                    'default' => 0,
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'used_for_sort_by' => false,
                    'apply_to' => $this->giftCardTypeCode,
                    'group' => $this->giftCardInfoGroupName,
                    'sort_order' => 3,
                ]
            )
            ->addAttribute(
                $this->entityTypeId,
                TypeGiftCard::ATTRIBUTE_CODE_ALLOW_MESSAGE,
                [
                    'type' => 'int',
                    'label' => 'Allow Message',
                    'input' => 'select',
                    'required' => false,
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'default' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::VALUE_YES,
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'used_for_sort_by' => false,
                    'apply_to' => $this->giftCardTypeCode,
                    'group' => $this->giftCardInfoGroupName,
                    'sort_order' => 4,
                ]
            )
            ->addAttribute(
                $this->entityTypeId,
                TypeGiftCard::ATTRIBUTE_CODE_EMAIL_TEMPLATES,
                [
                    'type' => 'static',
                    'label' => 'Email Templates',
                    'input' => 'select',
                    'backend' => 'Aheadworks\Giftcard\Model\Product\Entity\Attribute\Backend\Templates',
                    'frontend' => 'Aheadworks\Giftcard\Model\Product\Entity\Attribute\Frontend\Templates',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'used_for_sort_by' => false,
                    'apply_to' => $this->giftCardTypeCode,
                    'group' => $this->giftCardInfoGroupName,
                    'sort_order' => 5,
                ]
            )

            ->addAttribute(
                $this->entityTypeId,
                TypeGiftCard::ATTRIBUTE_CODE_AMOUNTS,
                [
                    'type' => 'static',
                    'label' => 'Amounts',
                    'input' => 'select',
                    'backend' => 'Aheadworks\Giftcard\Model\Product\Entity\Attribute\Backend\Amounts',
                    'frontend' => 'Aheadworks\Giftcard\Model\Product\Entity\Attribute\Frontend\Amounts',
                    'required' => false,
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'used_for_sort_by' => false,
                    'apply_to' => $this->giftCardTypeCode,
                    'group' => $this->giftCardInfoGroupName,
                    'sort_order' => 6,
                ]
            )
            ->addAttribute(
                $this->entityTypeId,
                TypeGiftCard::ATTRIBUTE_CODE_ALLOW_OPEN_AMOUNT,
                [
                    'type' => 'int',
                    'label' => 'Allow Open Amount',
                    'input' => 'boolean',
                    'required' => false,
                    'frontend' => 'Aheadworks\Giftcard\Model\Product\Entity\Attribute\Frontend\AllowOpenAmount',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_WEBSITE,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'used_for_sort_by' => false,
                    'apply_to' => $this->giftCardTypeCode,
                    'group' => $this->giftCardInfoGroupName,
                    'sort_order' => 7,
                ]
            )
            ->addAttribute(
                $this->entityTypeId,
                TypeGiftCard::ATTRIBUTE_CODE_OPEN_AMOUNT_MIN,
                [
                    'type' => 'decimal',
                    'label' => 'Open Amount Min Value',
                    'input' => 'price',
                    'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                    'required' => false,
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'used_for_sort_by' => false,
                    'apply_to' => $this->giftCardTypeCode,
                    'group' => $this->giftCardInfoGroupName,
                    'sort_order' => 8,
                ]
            )
            ->addAttribute(
                $this->entityTypeId,
                TypeGiftCard::ATTRIBUTE_CODE_OPEN_AMOUNT_MAX,
                [
                    'type' => 'decimal',
                    'label' => 'Open Amount Max Value',
                    'input' => 'price',
                    'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                    'required' => false,
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'used_for_sort_by' => false,
                    'apply_to' => $this->giftCardTypeCode,
                    'group' => $this->giftCardInfoGroupName,
                    'sort_order' => 9,
                ]
            )
        ;

        $fieldListToUpdate = [
            'weight',
            'tax_class_id'
        ];
        foreach ($fieldListToUpdate as $field) {
            $applyTo = explode(
                ',',
                $this->eavSetup->getAttribute($this->entityTypeId, $field, 'apply_to')
            );
            if (!in_array($this->giftCardTypeCode, $applyTo)) {
                $applyTo[] = $this->giftCardTypeCode;
                $this->eavSetup->updateAttribute(
                    $this->entityTypeId,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }

        foreach ($this->sampleEmailTemplates->get() as $data) {
            try {
                /** @var \Magento\Email\Model\Template $template */
                $template = $this->emailTemplateFactory->create()
                    ->load($data['template_code'], 'template_code')
                ;
                if (!$template->getId()) {
                    $template
                        ->setData($data)
                        ->setTemplateType(\Magento\Framework\App\TemplateTypesInterface::TYPE_HTML)
                        ->save()
                    ;
                }
            } catch (\Exception $e) {
            }
        }
    }
}
