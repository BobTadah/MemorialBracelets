<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class NewAction extends \Aheadworks\Giftcard\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context, $resultPageFactory, $resultForwardFactory);
        $this->productFactory = $productFactory;
    }

    /**
     * @return $this|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_getSession()->setBackToAwGiftCardProducts(true);
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward
            ->setController('product')
            ->setModule('catalog')
            ->setParams([
                'set' => $this->productFactory->create()->getDefaultAttributeSetId(),
                'type' => TypeGiftCard::TYPE_CODE
            ])
            ->forward('new');
    }
}
