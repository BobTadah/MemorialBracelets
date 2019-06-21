<?php
namespace Aheadworks\Giftcard\Model\Plugin\View\Element\Html;

class Links
{
    /**
     * @var string
     */
    protected $_giftCardLinkName = 'customer-account-navigation-aw-giftcard-link';

    /**
     * @param $interceptor
     * @var \Magento\Framework\View\Element\Html\Link[] $links
     * @return \Magento\Framework\View\Element\Html\Link[]
     */
    public function afterGetLinks($interceptor, $links)
    {
        if (isset($links[$this->_giftCardLinkName])) {
            $giftCardLink = $links[$this->_giftCardLinkName];
            unset($links[$this->_giftCardLinkName]);
            $links[$this->_giftCardLinkName] = $giftCardLink;
        }
        return $links;
    }
}
