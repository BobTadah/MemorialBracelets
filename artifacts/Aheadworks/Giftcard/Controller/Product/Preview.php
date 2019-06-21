<?php
namespace Aheadworks\Giftcard\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Preview
 * @package Aheadworks\Giftcard\Controller\Product
 */
class Preview extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Aheadworks\Giftcard\Model\Email\Previewer
     */
    protected $previewer;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Aheadworks\Giftcard\Model\Email\Previewer $previewer
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Aheadworks\Giftcard\Model\Email\Previewer $previewer
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->previewer = $previewer;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store');
        $data = $this->getRequest()->getPostValue();
        $success = true;
        try {
            $content = $this->previewer->getPreview($data, $storeId);
        } catch (LocalizedException $e) {
            $success = false;
            $content = $e->getMessage();
        }
        $jsonData = [
            'success' => $success,
            'content' => $content
        ];

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($jsonData);
    }
}
