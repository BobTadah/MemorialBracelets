<?php

namespace MemorialBracelets\CheckHandling\Setup;

use EasyUpgrade\CallableUpdateTrait;
use Magento\Cms\Model\PageFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Model\Order;

class UpgradeData implements UpgradeDataInterface
{
    use CallableUpdateTrait;

    /** @var  ModuleDataSetupInterface */
    private $setup;

    /** @var  ModuleContextInterface */
    private $context;

    /** @var  Config */
    private $config;

    /** @var PageFactory */
    private $pageFactory;

    public function __construct(Config $scopeConfig, PageFactory $pageFactory)
    {
        $this->config = $scopeConfig;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @noinspection PhpMissingBreakStatementInspection
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;
        $this->context = $context;

        $setup->startSetup();
        $this->runAt('100.0.1', $this->context->getVersion(), function () {
            // This isn't careful as there are no orders with this stuff at this point
            $this->removeStatusState('payment_waiting', Order::STATE_HOLDED);
            $this->addStatusState('payment_waiting', Order::STATE_NEW, 0, 1);
        });

        $this->runAt('100.0.2', $this->context->getVersion(), function () {
            $this->removeStatusState('payment_received', Order::STATE_HOLDED);
            $this->addStatusState('payment_received', Order::STATE_NEW, 0, 1);
        });

        $this->runAt('100.0.4', $this->context->getVersion(), function () {
            $this->config->saveConfig('payment/checkmo/order_status', 'payment_waiting', 'default', 0);
        });

        $this->runAt('100.0.5', $this->context->getVersion(), function () {
            $page = $this->pageFactory->create();
            $page->setTitle('Check Order Instructions')
                ->setIdentifier('check-order-instructions')
                ->setIsActive(true)
                ->setPageLayout('1column')
                ->setContent('Please mail your check with the following pages:')
                ->setStores([0])
                ->save();
        });

        $setup->endSetup();
    }

    /**
     * Removes a connection between an Order status and a state from the DB
     *
     * @param string $status
     * @param string $state
     */
    protected function removeStatusState($status, $state)
    {
        $select = $this->setup->getConnection()->select();
        $select->where('status = ?', $status)->where('state = ?', $state);

        $this->setup->getConnection()
            ->deleteFromSelect($select, $this->setup->getTable('sales_order_status_state'));
    }

    /**
     * Adds a connection between an Order status and state in the DB
     *
     * @param string $status
     * @param string $state
     * @param int    $isDefault
     * @param int    $visibleOnFront
     */
    protected function addStatusState($status, $state, $isDefault, $visibleOnFront)
    {
        $data = [
            'status'           => $status,
            'state'            => $state,
            'is_default'       => $isDefault,
            'visible_on_front' => $visibleOnFront,
        ];

        $this->setup->getConnection()
            ->insertOnDuplicate($this->setup->getTable('sales_order_status_state'), $data);
    }
}
