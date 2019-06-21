<?php
namespace Aheadworks\Giftcard\Block\Checkout\Cart;

class Giftcard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_codeInputId = 'giftcard_code';

    /**
     * @return string
     */
    public function getCodeInputId()
    {
        return $this->_codeInputId;
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('awgiftcard/card/codePost');
    }

    /**
     * @return string
     */
    public function getJsInitOptions()
    {
        return \Zend_Json::encode([
            'giftcardCodeSelector' => '#' . $this->getCodeInputId(),
            'applyButton' => 'button.action.apply-giftcard'
        ]);
    }
}
