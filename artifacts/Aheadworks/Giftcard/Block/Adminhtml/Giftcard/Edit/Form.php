<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit;

use Aheadworks\Giftcard\Model\Source\Giftcard\EmailTemplate;

/**
 * Class Form
 * @package Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Email\Model\Template\SenderResolver
     */
    protected $senderResolver;

    /**
     * @var EmailTemplate
     */
    protected $giftCardEmailTemplateSource;

    /**
     * @var Form\Renderer\GiftcardProduct
     */
    protected $giftCardProductRenderer;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesno;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Email\Model\Template\SenderResolver $senderResolver
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param EmailTemplate $giftCardEmailTemplateSource
     * @param Form\Renderer\GiftcardProduct $giftCardProductRenderer
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Email\Model\Template\SenderResolver $senderResolver,
        \Magento\Store\Model\System\Store $systemStore,
        EmailTemplate $giftCardEmailTemplateSource,
        Form\Renderer\GiftcardProduct $giftCardProductRenderer,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->giftCardEmailTemplateSource =$giftCardEmailTemplateSource;
        $this->senderResolver = $senderResolver;
        $this->giftCardProductRenderer = $giftCardProductRenderer;
        $this->yesno = $yesno;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('giftcard_');

        /* @var $giftcard \Aheadworks\Giftcard\Model\Giftcard */
        $giftcard = $this->_coreRegistry->registry('aw_giftcard');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Gift Card Information')]);

        if ($giftcard->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'giftcard_id']);
            $fieldset->addField(
                'code',
                'label',
                [
                    'name' => 'code',
                    'label' => __('Code'),
                    'title' => __('Code')
                ]
            );
            $fieldset->addField(
                'initial_balance',
                'label',
                [
                    'name' => 'initial_balance',
                    'label' => __('Initial Amount'),
                    'title' => __('Initial Amount'),
                    'class' => 'validate-number validate-zero-or-greater',
                    'required'  => true
                ]
            );
            $fieldset->addField(
                'state_text',
                'label',
                [
                    'name' => 'state_text',
                    'label' => __('Availability'),
                    'title' => __('Availability')
                ]
            );
            $fieldset->addField(
                'is_used_text',
                'label',
                [
                    'name' => 'is_used',
                    'label' => __('Is Used'),
                    'title' => __('Is Used')
                ]
            );
            $dateFormat = $this->_localeDate->getDateFormat(
                \IntlDateFormatter::MEDIUM
            );
            $fieldset->addField(
                'expire_at',
                'date',
                [
                    'name' => 'expire_at',
                    'label' => __('Expiration Date'),
                    'title' => __('Expiration Date'),
                    'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                    'date_format' => $dateFormat
                ]
            );
            $formattedDate = (new \DateTime($giftcard->getCreatedAt()))->format('d M Y H:i');
            $giftcard->setCreatedAtFormatted($formattedDate);
            $fieldset->addField(
                'created_at_formatted',
                'label',
                [
                    'name' => 'created_at_formatted',
                    'label' => __('Created At'),
                    'title' => __('Created At'),
                ]
            );
        }

        if (!$giftcard->getId()) {
            $fieldset->addField(
                'initial_balance',
                'text',
                [
                    'name' => 'initial_balance',
                    'label' => __('Initial Amount'),
                    'title' => __('Initial Amount'),
                    'class' => 'validate-number validate-zero-or-greater',
                    'required'  => true
                ]
            );
            $defaultExpire = $this->_scopeConfig->getValue(\Aheadworks\Giftcard\Model\Giftcard::XML_PATH_GIFTCARD_EXPIRE_DAYS);
            $fieldset->addField(
                'expire_after',
                '\Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit\Form\Renderer\Expireafter',
                [
                    'name' => 'expire_after',
                    'label' => __('Expires After, days'),
                    'title' => __('Expires After, days'),
                    'value' => $defaultExpire
                ]);
        }
        $fieldset->addField(
            'website_id',
            'select',
            [
                'name' => 'website_id',
                'label' => __('Website'),
                'title' => __('Website'),
                'values' => $this->systemStore->getWebsiteValuesForForm()
            ]
        );

        if ($giftcard->getOrderId()) {
            $fieldset = $form->addFieldset('order_fieldset', ['legend' => __('Order Information')]);
            if ($giftcard->getProductId()) {
                $afterProductLinkText = $giftcard->getTypeText();
                $fieldset->addField(
                    'product_id',
                    'link',
                    [
                        'name' => 'product_id',
                        'after_element_html' => "({$afterProductLinkText})",
                        'label' => __('Product'),
                        'title' => __('Product')
                    ]
                )->setRenderer($this->giftCardProductRenderer);
            }
            $order = $giftcard->getInitialOrder();
            if ($order) {
                $fieldset->addField(
                    'order_increment_id',
                    'link',
                    [
                        'value' => "#{$order->getIncrementId()}",
                        'href'  => $this->getUrl('sales/order/view', ['order_id' => $order->getId()]),
                        'label' => __('Order'),
                        'title' => __('Order')
                    ]
                );
                if ($order->getCustomerId()) {
                    $fieldset->addField(
                        'order_customer',
                        'link',
                        [
                            'value' => $order->getCustomerName(),
                            'href'  => $this->getUrl('customer/index/edit', ['id' => $order->getCustomerId()]),
                            'label' => __('Customer Name'),
                            'title' => __('Customer Name')
                        ]
                    );
                } else {
                    $fieldset->addField(
                        'order_customer_name',
                        'label',
                        [
                            'value' => $order->getBillingAddress()->getName(),
                            'label' => __('Customer Name'),
                            'title' => __('Customer Name')
                        ]
                    );
                    $fieldset->addField(
                        'order_customer_email',
                        'label',
                        [
                            'value' => $order->getCustomerEmail(),
                            'label' => __('Customer Email'),
                            'title' => __('Customer Email')
                        ]
                    );
                }
            }
        } else {
            $fieldset = $form->addFieldset('sender_fieldset', ['legend' => __('Sender Details')]);
            $fieldset->addField(
                'type',
                'hidden',
                [
                    'name' => 'type',
                    'value' => \Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType::VALUE_VIRTUAL
                ]
            );
            if (!$giftcard->getId()) {
                $sender = $this->senderResolver->resolve(
                    $this->_scopeConfig->getValue(\Aheadworks\Giftcard\Model\Email\Sender::XML_PATH_SENDER_IDENTITY)
                );
                $giftcard->setSenderName($sender['name']);
                $giftcard->setSenderEmail($sender['email']);
            }
            $fieldset->addField(
                'sender_name',
                'text',
                [
                    'name' => 'sender_name',
                    'label' => __('Sender Name'),
                    'title' => __('Sender Name'),
                    'required'  => true
                ]
            );
            $fieldset->addField(
                'sender_email',
                'text',
                [
                    'name' => 'sender_email',
                    'label' => __('Sender Email'),
                    'title' => __('Sender Email'),
                    'class' => 'validate-email',
                    'required'  => !$giftcard->isPhysical()
                ]
            );
        }


        $fieldset = $form->addFieldset('recipient_fieldset', ['legend' => __('Recipient Details')]);
        $fieldset->addField(
            'recipient_name',
            'text',
            [
                'name' => 'recipient_name',
                'label' => __('Recipient Name'),
                'title' => __('Recipient Name'),
                'required'  => true
            ]
        );
        if ($giftcard->getId()) {
            $fieldset->addField(
                'balance',
                'text',
                [
                    'name' => 'balance',
                    'label' => __('Balance'),
                    'title' => __('Balance'),
                    'class'     => 'validate-greater-than-zero validate-number',
                    'required'  => true
                    //todo: add note with currency code here
                ]
            );
        }
        if (!$giftcard->getId() || $giftcard->getId() && !$giftcard->isPhysical()) {
            $fieldset->addField(
                'recipient_email',
                'text',
                [
                    'name' => 'recipient_email',
                    'label' => __('Recipient Email'),
                    'title' => __('Recipient Email'),
                    'class' => 'validate-email',
                    'required'  => true
                ]
            );
        }
        if ($giftcard->isPhysical()) {
            $fieldset->addField(
                'email_template',
                'hidden',
                [
                    'name' => 'email_template',
                    'value' => EmailTemplate::DO_NOT_SEND_VALUE
                ]
            );
        } else {
            $fieldset->addField(
                'email_template',
                'select',
                [
                    'name' => 'email_template',
                    'label' => __('Email Template'),
                    'title' => __('Email Template'),
                    'options' => $this->giftCardEmailTemplateSource->getOptions()
                ],
                $giftcard->getId() ? '' : 'recipient_name'
            );
        }

        if ($giftcard->getId()) {
            $form->addFieldset('history_fieldset', ['legend' => __('History')]);
            $this->addChild('form_after', 'Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit\History');
        }

        $form->addValues($giftcard->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
