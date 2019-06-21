<?php
namespace Aheadworks\Giftcard\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class GetOptions extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Aheadworks\Giftcard\Helper\Catalog\Product\View\Options
     */
    protected $optionsHelper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Aheadworks\Giftcard\Helper\Catalog\Product\View\Options $optionsHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Aheadworks\Giftcard\Helper\Catalog\Product\View\Options $optionsHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->optionsHelper = $optionsHelper;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product');
        $jsonData = $productId ? $this->optionsHelper->getProductOptions($productId) : [];

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($jsonData);
    }
}
