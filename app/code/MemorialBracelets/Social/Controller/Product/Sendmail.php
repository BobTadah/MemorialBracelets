<?php

namespace MemorialBracelets\Social\Controller\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\SendFriend\Controller\Product;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\SendFriend\Model\SendFriend;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Session;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Sendmail
 * @see \Magento\SendFriend\Controller\Product\Sendmail
 * @package MemorialBracelets\Social\Controller\Product
 */
class Sendmail extends Product
{
    /** @var  \Magento\Catalog\Api\CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var \Magento\Catalog\Model\Session */
    protected $catalogSession;

    /** @var JsonFactory  $resultJsonFactory*/
    protected $resultJsonFactory;

    /**
     * Sendmail constructor.
     * @param Context                     $context
     * @param Registry                    $coreRegistry
     * @param Validator                   $formKeyValidator
     * @param SendFriend                  $sendFriend
     * @param ProductRepositoryInterface  $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Session                     $catalogSession
     * @param JsonFactory                 $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Validator $formKeyValidator,
        SendFriend $sendFriend,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        Session $catalogSession,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context, $coreRegistry, $formKeyValidator, $sendFriend, $productRepository);
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession = $catalogSession;
        $this->resultJsonFactory  = $resultJsonFactory;
    }

    /**
     * Send Email Post Action
     * @see \Magento\SendFriend\Controller\Product\Sendmail::execute()
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $isAjax = false;
        $request = $this->getRequest()->getParams();
        if (!empty($request) && array_key_exists('isAjax', $request) && $request['isAjax']) {
            $isAjax = true;
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('sendfriend/product/send', ['_current' => true]);
            if ($isAjax) {
                $result  = $this->resultJsonFactory->create();
                $result->setData(['output' => 0]);
            } else {
                return $resultRedirect;
            }
        }

        $product = $this->_initProduct();
        $data    = $this->getRequest()->getPostValue();

        if (!$product || !$data) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $categoryId = $this->getRequest()->getParam('cat_id', null);
        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $noEntityException) {
                $category = null;
            }
            if ($category) {
                $product->setCategory($category);
                $this->_coreRegistry->register('current_category', $category);
            }
        }

        $this->sendFriend->setSender($this->getRequest()->getPost('sender'));
        $this->sendFriend->setRecipients($this->getRequest()->getPost('recipients'));
        $this->sendFriend->setProduct($product);

        try {
            $validate = $this->sendFriend->validate();
            if ($validate === true) {
                $this->sendFriend->send();
                $this->messageManager->addSuccess(__('The link to a friend was sent.'));
                $url = $product->getProductUrl();
                $resultRedirect->setUrl($this->_redirect->success($url));
                if ($isAjax) {
                    $result  = $this->resultJsonFactory->create();
                    $result->setData(['output' => 1]);
                    return $result;
                } else {
                    return $resultRedirect;
                }
            } else {
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                } else {
                    $this->messageManager->addError(__('We found some problems with the data.'));
                }
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Some emails were not sent.'));
        }

        // save form data
        $this->catalogSession->setSendfriendFormData($data);

        $url = $this->_url->getUrl('sendfriend/product/send', ['_current' => true]);
        $resultRedirect->setUrl($this->_redirect->error($url));

        if ($isAjax) {
            $result  = $this->resultJsonFactory->create();
            $result->setData(['output' => 1]);
            return $result;
        } else {
            return $resultRedirect;
        }
    }
}
