<?php

namespace IWD\OrderManager\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResourceConnection;

/**
 * Class ArchivedOrderObserver
 * @package IWD\OrderManager\Observer
 */
class ArchivedOrderObserver implements ObserverInterface
{
    /**
     * @var ResourceConnection
     */
    private $appResourceConnection;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * ArchivedOrderObserver constructor.
     * @param Session $authSession
     * @param ResourceConnection $appResourceConnection
     */
    public function __construct(Session $authSession, ResourceConnection $appResourceConnection)
    {
        $this->authSession = $authSession;
        $this->appResourceConnection = $appResourceConnection;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getIsArchived()){
            $connection = $this->appResourceConnection
                ->getConnection(ResourceConnection::DEFAULT_CONNECTION);

            $salesInvoiceGridTable = $this->appResourceConnection->getTableName('sales_order_grid');
            $connection->delete($salesInvoiceGridTable, ['entity_id = (?)' => $order->getId()]);

        }

    }

}
