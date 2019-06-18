<?php

namespace MemorialBracelets\Engraving\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;

class InstallData implements InstallDataInterface
{

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $statuses = [
            'engraving' => __('Engraving'),
        ];

        $data = array_map(
            function ($key, $value) {
                return ['status' => $key, 'label' => $value];
            },
            array_keys($statuses),
            array_values($statuses)
        );

        $setup->getConnection()->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);

        $data = [
            [
                'status'           => 'engraving',
                'state'            => Order::STATE_PROCESSING,
                'is_default'       => 0,
                'visible_on_front' => 1,
            ],
        ];

        $setup->getConnection()->insertOnDuplicate($setup->getTable('sales_order_status_state'), $data);

        $setup->endSetup();
    }
}
