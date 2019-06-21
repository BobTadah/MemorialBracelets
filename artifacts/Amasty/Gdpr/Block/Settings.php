<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Data\Form\FormKey as FormKey;

class Settings extends Template
{
    protected $_template = 'settings.phtml';

    /**
     * @var FormKey
     */
    protected $formKey;

    public function __construct(
        Template\Context $context,
        array $data = [],
        FormKey $formKey
    ) {
        parent::__construct($context);
        $this->formKey = $formKey;
    }

    public function getPrivacySettings()
    {
        return [
            [
                'title' => __('Download personal data'),
                'content' => __('Here you can download a copy of your personal data which we store for your account in CSV format.'),
                'hasCheckbox' => false,
                'checkboxText' => '',
                'hidePassword' => false,
                'submitText' => __('Download'),
                'action' => $this->getUrl('gdpr/customer/downloadCsv'),
            ],
            [
                'title' => __('Anonymise personal data'),
                'content' => __('Anonymising your personal data means that it will be replaced with non-personal anonymous information and before that you will get your new login and password to your e-mail address. After this process, your e-mail address and all other personal data will be removed from the website.'),
                'hasCheckbox' => true,
                'checkboxText' => __('I agree and I want to proceed'),
                'hidePassword' => true,
                'submitText' => __('Proceed'),
                'action' => $this->getUrl('gdpr/customer/anonymise'),
            ],
            [
                'title' => __('Delete account'),
                'content' => __('Request to remove your account, together with all your personal data, will be processed by our staff.<br>Deleting your account will remove all the purchase history, discounts, orders, invoices and all other information that might be related to your account or your purchases.<br>All your orders and similar information will be lost.<br>You will not be able to restore access to your account after we approve your removal request.'),
                'checked' => true,
                'hasCheckbox' => true,
                'checkboxText' => __('I understand and I want to delete my account'),
                'hidePassword' => true,
                'submitText' => __('Submit request'),
                'action' => $this->getUrl('gdpr/customer/addDeleteRequest'),
            ]
        ];
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
