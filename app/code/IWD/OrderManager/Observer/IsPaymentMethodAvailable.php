<?php

namespace IWD\OrderManager\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Auth\Session;
use IWD\OrderManager\Helper\Data;

/**
 * Class IsPaymentMethodAvailable
 * @package IWD\OrderManager\Observer
 */
class IsPaymentMethodAvailable implements ObserverInterface
{

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;
    private $request;

    /**
     * @var Data
     */
    private $configHelper;

    /**
     * SaveOrderStatusHistoryObserver constructor.
     * @param Session $authSession
     */
    public function __construct(
        Session $authSession,
        \Magento\Framework\App\Request\Http $request,
        Data $configHelper
    ) {
        $this->authSession = $authSession;
        $this->request = $request;
        $this->configHelper = $configHelper;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->request->getModuleName() === 'iwdordermanager' && $this->request->getControllerName() === 'order_payment') {
            $availablePaymentMethods = ['free', 'cashondelivery', 'banktransfer', 'checkmo', 'purchaseorder', 'iwd_authcim'];
            if (!in_array($observer->getEvent()->getData('method_instance')->getCode(), $availablePaymentMethods)) {
                $result = $observer->getEvent()->getResult();
                $result->setData('is_available', false);
            }
        }
    }
}
