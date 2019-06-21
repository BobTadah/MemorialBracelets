<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Giftcard;

use Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType;
use Aheadworks\Giftcard\Model\Source\Giftcard\Status;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var string
     */
    protected $_typeSelectId = 'giftcard_type';

    /**
     * @var string
     */
    protected $_senderEmailId = 'giftcard_sender_email';

    /**
     * @var string
     */
    protected $_recipientEmailId = 'giftcard_recipient_email';

    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Giftcard::giftcard/form/container.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Aheadworks_Giftcard';
        $this->_controller = 'adminhtml_giftcard';

        parent::_construct();

        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');

        /* @var $giftcard \Aheadworks\Giftcard\Model\Giftcard */
        $giftcard = $this->_coreRegistry->registry('aw_giftcard');
        if (!$giftcard || !$giftcard->getId()) {
            $this->buttonList->add(
                'saveandsend',
                [
                    'label' => __('Save and Send Gift Card'),
                    'class' => 'primary',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'saveAndSend', 'target' => '#edit_form']],
                    ]
                ],
                5
            );
            $this->buttonList->update('save', 'class', 'save');
        }

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            2
        );
        if ($giftcard && $giftcard->getId() && !$giftcard->isPhysical()) {
            $this->buttonList->add(
                'saveandsend',
                [
                    'label' => __('Save and Resend Gift Card'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'saveAndSend', 'target' => '#edit_form']],
                    ]
                ],
                3
            );
        }
        if ($giftcard && $giftcard->getId()) {
            if ($giftcard->getState() == Status::DEACTIVATED_VALUE) {
                $this->buttonList->add(
                    'activate',
                    [
                        'label' =>  __('Activate'),
                        'class' => 'save',
                        'onclick' => 'setLocation(\'' . $this->getUrl("aw_giftcard_admin/giftcard/activate", ['id' => $giftcard->getId()]) . '\')',
                    ],
                    1
                );
            } else {
                $this->buttonList->add(
                    'deactivate',
                    [
                        'label' =>  __('Deactivate'),
                        'class' => 'save',
                        'onclick' => 'setLocation(\'' . $this->getUrl("aw_giftcard_admin/giftcard/deactivate", ['id' => $giftcard->getId()]) . '\')',
                    ],
                    1
                );
            }
        }
    }

    public function getJsInitOptions()
    {
        return \Zend_Json::encode([
            'handlersData' => [
                'saveAndSend' => [
                    'action' => [
                        'args' => [
                            'send' => '1'
                        ]
                    ]
                ]
            ]
        ]);
    }
}
