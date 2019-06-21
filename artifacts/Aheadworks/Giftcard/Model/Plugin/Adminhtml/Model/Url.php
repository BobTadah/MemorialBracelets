<?php
namespace Aheadworks\Giftcard\Model\Plugin\Adminhtml\Model;

use Magento\Backend\Model\Session;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

class Url
{
    /**
     * @var Session
     */
    protected $_session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->_session = $session;
    }

    /**
     * @return array
     */
    public function beforeGetUrl()
    {
        $arguments = func_get_args();
        array_shift($arguments);
        if (
            isset($arguments[0]) &&
            $this->_matchRoute($arguments[0]) &&
            $this->_session->getBackToAwGiftCardProducts()
        ) {
            $arguments[0] = 'aw_giftcard_admin/*/';
        }
        return $arguments;
    }


    protected function _matchRoute($route)
    {
        // todo: use regular expressions
        return
            $route == 'catalog/*/' ||
            $route == 'catalog/*/index'
            ;
    }
}