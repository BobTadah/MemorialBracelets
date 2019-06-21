<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Product;

class MassStatus extends \Aheadworks\Giftcard\Controller\Adminhtml\Product
{
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
            ->forward('massStatus');
    }
}
