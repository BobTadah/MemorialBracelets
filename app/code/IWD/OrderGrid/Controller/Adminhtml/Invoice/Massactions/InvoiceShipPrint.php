<?php

namespace IWD\OrderGrid\Controller\Adminhtml\Invoice\MassActions;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use IWD\OrderGrid\Controller\Adminhtml\AbstractMassAction;

/**
 * Class InvoiceShipPrint
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceShipPrint extends AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var Invoice
     */
    protected $pdfInvoice;

    /**
     * @var Shipment
     */
    protected $pdfShipment;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var InvoiceCollectionFactory
     */
    protected $invoiceCollectionFactory;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param FileFactory $fileFactory
     * @param Invoice $pdfInvoice
     * @param Shipment $pdfShipment
     * @param DateTime $dateTime
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Filter $filter,
        FileFactory $fileFactory,
        Invoice $pdfInvoice,
        Shipment $pdfShipment,
        DateTime $dateTime,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->pdfInvoice = $pdfInvoice;
        $this->pdfShipment = $pdfShipment;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->collectionFactory = $orderCollectionFactory;
        parent::__construct($context, $filter);
    }

    /**
     * Print invoice and ship documents for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function massAction(AbstractCollection $collection)
    {
        $orderIds = $collection->getAllIds();

        $shipments = $this->shipmentCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
        $invoices = $this->invoiceCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);

        $documents = [];
        if ($invoices->getSize()) {
            $documents[] = $this->pdfInvoice->getPdf($invoices);
        }
        if ($shipments->getSize()) {
            $documents[] = $this->pdfShipment->getPdf($shipments);
        }

        if (empty($documents)) {
            $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }

        $pdf = array_shift($documents);
        foreach ($documents as $document) {
            $pdf->pages = array_merge($pdf->pages, $document->pages);
        }

        return $this->fileFactory->create(
            sprintf('docs%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('IWD_OrderGrid::iwdordergrid_invoice_create')
            && $this->_authorization->isAllowed('IWD_OrderGrid::iwdordergrid_ship')
            && $this->_authorization->isAllowed('Magento_Sales::invoice')
            && $this->_authorization->isAllowed('Magento_Sales::ship');
    }
}
