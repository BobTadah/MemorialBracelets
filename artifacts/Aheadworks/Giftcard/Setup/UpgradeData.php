<?php
namespace Aheadworks\Giftcard\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var string
     */
    protected $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;

    /**
     * @var EavSetup
     */
    protected $eavSetup;

    /**
     * @param EavSetup $eavSetup
     */
    public function __construct(
        EavSetup $eavSetup
    ) {
        $this->eavSetup = $eavSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (
            $context->getVersion()
            && version_compare($context->getVersion(), '1.0.2', '<')
        ) {
            $this->eavSetup
                ->updateAttribute(
                    $this->entityTypeId,
                    TypeGiftCard::ATTRIBUTE_CODE_AMOUNTS,
                    'frontend_input',
                    'select'
                )
            ;
        }

        $setup->endSetup();
    }
}
