<?php
namespace Aheadworks\Giftcard\Block\Customer;

class Giftcard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_codeInputId = 'giftcard_code';

    /**
     * @var string
     */
    protected $_codeCheckInputId = 'check_giftcard_code';

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
    public function getCodeCheckInputId()
    {
        return $this->_codeCheckInputId;
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
            'checkGiftcardSelector' => '#' . $this->getCodeCheckInputId(),
            'checkButton' => 'button.action.check-giftcard'
        ]);
    }
}
