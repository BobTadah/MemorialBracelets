<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Aheadworks\Giftcard\Model\ResourceModel\Giftcard\CollectionFactory;

/**
 * Class MassAbstract
 * @package Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
 */
abstract class MassAbstract extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
{
    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardManager
     */
    protected $giftCardManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var string
     */
    protected $errorMessage = 'Something went wrong while perform mass action';

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     * @param \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory,
        \Aheadworks\Giftcard\Model\GiftcardManager $giftCardManager,
        \Magento\Ui\Component\MassAction\Filter $filter
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->giftCardManager = $giftCardManager;
        $this->filter = $filter;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $this->massAction($collection);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __($this->errorMessage));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('aw_giftcard_admin/*/');
    }

    /**
     * Performs mass action
     *
     * @param \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Collection $collection
     */
    abstract protected function massAction($collection);
}
