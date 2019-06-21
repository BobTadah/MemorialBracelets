<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Product;

class MassDelete extends \Aheadworks\Giftcard\Controller\Adminhtml\Product
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_getSession()->setBackToAwGiftCardProducts(true);
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward
            ->setController('product')
            ->setModule('catalog')
            ->forward('massDelete');
    }
}
