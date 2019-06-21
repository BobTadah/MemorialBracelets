<?php
namespace Aheadworks\Giftcard\Model\Source\Giftcard;

class EmailTemplate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * 'Do not send' option value
     */
    const DO_NOT_SEND_VALUE = '0';

    /**
     * 'Do not send' option label
     */
    const DO_NOT_SEND_LABEL = 'Do not send';

    /**
     * @var \Magento\Config\Model\Config\Source\Email\Template
     */
    protected $emailTemplates;

    /**
     * @param \Magento\Config\Model\Config\Source\Email\Template $emailTemplates
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Email\Template $emailTemplates
    ) {
        $this->emailTemplates = $emailTemplates;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = $this->emailTemplates
            ->setPath('aw_giftcard_email_template')
            ->toOptionArray()
        ;
        array_unshift($optionArray, [
            'value' => self::DO_NOT_SEND_VALUE,
            'label' => __(self::DO_NOT_SEND_LABEL)
        ]);
        return $optionArray;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * @param int $value
     * @return null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
