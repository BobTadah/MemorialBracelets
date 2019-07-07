<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */


namespace Amasty\Shiprestriction\Setup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @var \Amasty\Base\Setup\SerializedFieldDataConverter
     */
    private $fieldDataConverter;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        \Amasty\Base\Setup\SerializedFieldDataConverter $fieldDataConverter
    ) {
        $this->productMetaData = $productMetaData;
        $this->fieldDataConverter = $fieldDataConverter;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1', '<')
            && $this->productMetaData->getVersion() >= "2.2.0"
        ) {
            $this->fieldDataConverter->convertSerializedDataToJson(
                'amasty_shiprestriction_rule',
                'rule_id',
                ['conditions_serialized']
            );
        }

        $setup->endSetup();
    }
}
