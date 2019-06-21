<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit\History\Renderer;

class Additional extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Actions
     *
     * @var \Aheadworks\Giftcard\Model\Source\History\Actions
     */
    protected $_actions;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\Source\History\Actions $actions,
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
        $this->_actions = $actions;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $item)
    {
        if (null === $item->getData('additional_info')) {
            return '';
        }

        $info = $item->getData('additional_info');
        if (!is_array($info)
            || !array_key_exists('message_type', $info)
            || !array_key_exists('message_data', $info)
        ) {
            return '';
        }

        $messageLabel = $this->_actions
            ->getMessageLabelByType($info['message_type'])
        ;
        return __($messageLabel, $info['message_data']);
    }


}
