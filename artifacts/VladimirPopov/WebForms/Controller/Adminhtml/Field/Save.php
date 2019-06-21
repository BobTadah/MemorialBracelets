<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2017 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Field;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    protected $webformsHelper;

    public function __construct(
        Action\Context $context,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper
    )
    {
        $this->webformsHelper = $webformsHelper;
        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        $data = $this->getRequest()->getPostValue('field');
        if ($this->getRequest()->getParam('id')) {
            $model = $this->_objectManager->create('VladimirPopov\WebForms\Model\Field')->load($this->getRequest()->getParam('id'));
            return $this->webformsHelper->isAllowed($model->getWebformId());
        } else if (!empty($data['webform_id'])) {
            return $this->webformsHelper->isAllowed($data['webform_id']);
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $store = $this->getRequest()->getParam('store');
        $data = $this->getRequest()->getPostValue('field');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('VladimirPopov\WebForms\Model\Field');

            !empty($data['id']) ? $id = $data['id'] : $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);

                if ($store) {
                    unset($data['id']);
                    unset($data['webform_id']);
                    $model->saveStoreData($store, $data);
                }
            }

            isset($data['type']) ?: $data['type'] = $model->getType();
            switch ($data['type']) {
                case 'text':
                    break;
                case 'email':
                    if (!empty($data["hint_email"])) $data["hint"] = $data["hint_email"];
                    break;
                case 'url':
                    if (!empty($data["hint_url"])) $data["hint"] = $data["hint_url"];
                    break;
                case 'textarea':
                    if (!empty($data["hint_textarea"])) $data["hint"] = $data["hint_textarea"];
                    break;
                case 'hidden':
                    if (!$this->_authorization->isAllowed('VladimirPopov_WebForms::field_hidden')) {
                        $this->messageManager->addErrorMessage(__('You don\'t have permission to manage Hidden fields'));
                        return $resultRedirect->setPath('*/*/edit', array('_current' => true));
                    };
                    break;
                case 'image':
                    if (!empty($data["value"]["dropzone_image"])) $data["value"]["dropzone"] = $data["value"]["dropzone_image"];
                    if (!empty($data["value"]["dropzone_text_image"])) $data["value"]["dropzone_text"] = $data["value"]["dropzone_text_image"];
                    if (!empty($data["value"]["dropzone_maxfiles_image"])) $data["value"]["dropzone_maxfiles"] = $data["value"]["dropzone_maxfiles_image"];
                    break;
            }

            $this->_eventManager->dispatch(
                'webforms_field_prepare_save',
                ['field' => $model, 'request' => $this->getRequest()]
            );

            try {
                if (!$store) $model->setData($data)->save();

                $this->messageManager->addSuccessMessage(__('You saved this field.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/field/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/form/edit', ['id' => $model->getWebformId(), 'active_tab' => 'fields_section', 'store' => $store]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the field.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id, 'webform_id' => $this->getRequest()->getParam('webform_id'), 'store' => $store]);
        }
        return $resultRedirect->setPath('webforms/form/');
    }
}