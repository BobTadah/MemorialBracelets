<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

use Magento\Backend\App\Action;

class History extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Aheadworks\Giftcard\Model\GiftcardFactory
     */
    protected $_giftcardModelFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;


    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Giftcard\Model\GiftcardFactory $giftcardFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_giftcardModelFactory = $giftcardFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Get giftcard history list
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $giftcard = $this->_giftcardModelFactory->create();
        $id = $this->getRequest()->getParam('id');
            $giftcard->load($id);
        $this->_coreRegistry->register('aw_giftcard', $giftcard);

        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('aw_giftcard.history');
        $block->setUseAjax(true);
        return $resultLayout;
    }
}
