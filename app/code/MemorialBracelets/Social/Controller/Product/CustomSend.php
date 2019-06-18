<?php

namespace MemorialBracelets\Social\Controller\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\SendFriend\Controller\Product;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\SendFriend\Model\SendFriend;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class CustomSend
 * @see \Magento\SendFriend\Controller\Product\Send
 * @package MemorialBracelets\Social\Controller\Product
 */
class CustomSend extends Product
{
    /** @var Session  $catalogSession*/
    protected $catalogSession;

    /** @var JsonFactory  $resultJsonFactory*/
    protected $resultJsonFactory;

    /**
     * CustomSend constructor.
     * @param Context                    $context
     * @param Registry                   $coreRegistry
     * @param Validator                  $formKeyValidator
     * @param SendFriend                 $sendFriend
     * @param ProductRepositoryInterface $productRepository
     * @param Session                    $catalogSession
     * @param JsonFactory                $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Validator $formKeyValidator,
        SendFriend $sendFriend,
        ProductRepositoryInterface $productRepository,
        Session $catalogSession,
        JsonFactory $resultJsonFactory
    ) {
        $this->catalogSession = $catalogSession;
        $this->resultJsonFactory  = $resultJsonFactory;
        parent::__construct(
            $context,
            $coreRegistry,
            $formKeyValidator,
            $sendFriend,
            $productRepository
        );
    }

    /**
     * Show Send to a Friend Form
     * @see \Magento\SendFriend\Controller\Product\Send::execute()
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $isAjax = false;
        $request = $this->getRequest()->getParams();
        if (!empty($request) && array_key_exists('isAjax', $request) && $request['isAjax']) {
            $isAjax = true;
        }

        $product = $this->_initProduct();

        if (!$product) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        if ($this->sendFriend->getMaxSendsToFriend() && $this->sendFriend->isExceedLimit()) {
            $this->messageManager->addNotice(
                __('You can\'t send messages more than %1 times an hour.', $this->sendFriend->getMaxSendsToFriend())
            );
        }

        /** @var \Magento\Framework\View\Result\Page $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_eventManager->dispatch('sendfriend_product', ['product' => $product]);
        $data  = $this->catalogSession->getSendfriendFormData();
        $block = null;
        if ($data) {
            $this->catalogSession->setSendfriendFormData(true);
            $block = $result->getLayout()->getBlock('sendfriend.send');
            if ($block) {
                /** @var \Magento\SendFriend\Block\Send $block */
                $block->setFormData($data);
            }
        }

        if ($isAjax) {
            $block  = $result->getLayout()
                             ->createBlock('Magento\SendFriend\Block\Send')
                             ->setTemplate('Magento_SendFriend::send.phtml')
                             ->setData('isAjax', 1)
                             ->toHtml();
            $result  = $this->resultJsonFactory->create();
            $result->setData(['output' => $block]);
        }

        return $result;
    }
}
