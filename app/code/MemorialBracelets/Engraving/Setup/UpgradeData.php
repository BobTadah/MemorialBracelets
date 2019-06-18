<?php

namespace MemorialBracelets\Engraving\Setup;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package MemorialBracelets\Engraving\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * UpgradeData constructor.
     * @param ConfigInterface $configInterface
     */
    public function __construct(
        ConfigInterface $configInterface
    ) {
        $this->configInterface = $configInterface;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '100.0.1', '<=')) {
            $nonVietnamValue = 'All Engraving on Vietnam War bracelets is centered both vertically and horizontally on the traditional narrower 1/2” band. Status or Wall Number and State are engraved on the ends of the bracelet. Engraving on Vietnam War bracelets and dog tags is not editable.';
            $this->configInterface
                ->saveConfig('catalog/engravable/pdp_message', $nonVietnamValue, 'default', 0);

            $vietnamValue = 'All engraving on Non Vietnam War bracelets is centered both vertically and horizontally on the wider 5/8” band.';
            $this->configInterface
                ->saveConfig('catalog/engravable/pdp_message_vietnam', $vietnamValue, 'default', 0);

            $vietnamBracelets = '12,71,496';
            $this->configInterface
                ->saveConfig('catalog/engravable/excluded_events', $vietnamBracelets, 'default', 0);
        }

        $setup->endSetup();
    }
}
