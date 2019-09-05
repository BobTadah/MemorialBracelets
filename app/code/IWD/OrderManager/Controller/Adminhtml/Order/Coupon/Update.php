<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Coupon;

use IWD\OrderManager\Model\Order\Coupon as OrderCoupon;
use IWD\OrderManager\Helper\Data;
use IWD\OrderManager\Model\Log\Logger;
use IWD\OrderManager\Controller\Adminhtml\Order\Additional\AbstractAction;
use IWD\OrderManager\Model\Order\Order;
use IWD\OrderManager\Model\Order\Payment;
use IWD\OrderManager\Model\Order\Shipping;
use IWD\OrderManager\Model\Quote\Quote;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Update
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Coupon
 */
class Update extends AbstractAction
{
    /**
     * @var OrderCoupon
     */
    protected $orderCoupon;

    /**
     * @var array
     */
    protected $result;

    /**
     * Update constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param Quote $quote
     * @param Order $order
     * @param Shipping $shipping
     * @param Payment $payment
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param OrderCoupon $orderCoupon
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        Quote $quote,
        Order $order,
        Shipping $shipping,
        Payment $payment,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        OrderCoupon $orderCoupon
    )
    {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            $scopeConfig,
            $quote,
            $order,
            $shipping,
            $payment,
            $resourceConnection
        );

        $this->orderCoupon = $orderCoupon;
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function update()
    {
        $this->updateCoupon();
    }

    /**
     * @return null|string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        parent::getResultHtml();

        $this->updateCoupon();

        return $this->result;
    }

    protected function updateCoupon()
    {
        try {
            $couponCode = $this->getCouponCode();
            $result = $this->getOrder()->updateCoupon($couponCode);

            if ($result == false) {
                $this->result['is_error'] = 0;
                $this->result['result'] = 'without_changes';
            } else {
                if (strlen($couponCode) > 0) {
                    $this->result['message'] = __('You used coupon code "%1".', $couponCode);
                } else {
                    $this->result['message'] = __('You canceled the coupon code.');
                }
                Logger::getInstance()->addMessage($this->result['message']);
                $this->result['is_error'] = 0;
                $this->result['result'] = 'reload';
            }
        } catch (\Exception $e) {
            $this->result['is_error'] = 1;
            $this->result['result'] = 'fail';
            $this->result['message'] = __($e->getMessage());
        }
    }

    /**
     * @return string
     */
    protected function getCouponCode()
    {
        $couponCode = $this->getRequest()->getParam('coupon', '');
        return trim($couponCode);
    }

    /**
     * @return OrderCoupon
     */
    protected function getOrder()
    {
        if (!$this->orderCoupon->getEntityId()) {
            $id = $this->getOrderId();
            $this->orderCoupon->load($id);
        }

        return $this->orderCoupon;
    }

    /**
     * @return array|string
     */
    protected function prepareResponse()
    {
        return ['result' => 'reload'];
    }
}
