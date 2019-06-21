<?php
namespace Aheadworks\Giftcard\Helper\Catalog\Product;

use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class Configuration extends AbstractHelper implements ConfigurationInterface
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType
     */
    protected $sourceGiftCardType;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Escaper $escaper
     * @param \Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType $sourceGiftCardType
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Escaper $escaper,
        \Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType $sourceGiftCardType
    ) {
        $this->escaper = $escaper;
        $this->sourceGiftCardType = $sourceGiftCardType;
        parent::__construct($context);
    }

    /**
     * @param ItemInterface $item
     * @param string $code
     * @return array|bool|string
     */
    protected function _getCustomOption(ItemInterface $item, $code)
    {
        $option = $item->getOptionByCode($code);
        if ($option) {
            $value = $option->getValue();
            return $value ? $value : null;
        }
        return null;
    }

    /**
     * Retrieves product options list
     *
     * @param ItemInterface $item
     * @return array
     */
    public function getOptions(ItemInterface $item)
    {
        return $this->getGiftCardOptionsData([
            TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE_NAME => $this->_getCustomOption($item, TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE_NAME),
            TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME => $this->_getCustomOption($item, TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME),
            TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL => $this->_getCustomOption($item, TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL),
            TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME => $this->_getCustomOption($item, TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME),
            TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL => $this->_getCustomOption($item, TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL),
            TypeGiftCard::BUY_REQUEST_ATTR_CODE_MESSAGE => $this->_getCustomOption($item, TypeGiftCard::BUY_REQUEST_ATTR_CODE_MESSAGE),
            'aw_gc_created_codes' => $this->_getCustomOption($item, 'aw_gc_created_codes')
        ]);
    }

    /**
     * @param array $data
     * @return array
     */
    public function getGiftCardOptionsData($data)
    {
        $result = [];

        if (isset($data[TypeGiftCard::ATTRIBUTE_CODE_TYPE])) {
            $type = $data[TypeGiftCard::ATTRIBUTE_CODE_TYPE];
            $result[] = [
                'label' => __('Gift Card Type'),
                'value' => $this->escaper->escapeHtml($this->sourceGiftCardType->getOptionText($type))
            ];
        }

        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE_NAME])) {
            $result[] = [
                'label' => __('Gift Card Design'),
                'value' => $this->escaper->escapeHtml($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_EMAIL_TEMPLATE_NAME])
            ];
        }

        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME])) {
            $senderName = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_NAME];
            if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL])) {
                $senderEmail = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_SENDER_EMAIL];
            }
            $result[] = [
                'label' => __('Gift Card Sender'),
                'value' => $this->escaper->escapeHtml(isset($senderEmail) ? "{$senderName} &lt;{$senderEmail}&gt;" : $senderName)
            ];
        }

        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME])) {
            $recipientName = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_NAME];
            if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL])) {
                $recipientEmail = $data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_RECIPIENT_EMAIL];
            }
            $result[] = [
                'label' => __('Gift Card Recipient'),
                'value' => $this->escaper->escapeHtml(isset($recipientEmail) ? "{$recipientName} &lt;{$recipientEmail}&gt;" : $recipientName)
            ];
        }

        if (isset($data['email_sent'])) {
            $emailSent = $data['email_sent'];
            $result[] = [
                'label' => __('Email Sent'),
                'value' => $emailSent ? __('Yes') : __('No')
            ];
        }

        if (isset($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_MESSAGE])) {
            $message = trim($data[TypeGiftCard::BUY_REQUEST_ATTR_CODE_MESSAGE]);
            if ($message) {
                $result[] = [
                    'label' => __('Gift Card Message'),
                    'value' => $this->escaper->escapeHtml($message)
                ];
            }
        }

        if (isset($data['aw_gc_created_codes'])) {
            $codes = $data['aw_gc_created_codes'];
            if (is_array($codes) && count($codes) > 0) {
                $result[] = [
                    'label' => __('Gift Card Codes'),
                    'value' => implode('<br/>', $this->escaper->escapeHtml($codes))
                ];
            }
        }

        return $result;
    }
}
