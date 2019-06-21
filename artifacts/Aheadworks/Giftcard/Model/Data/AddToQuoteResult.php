<?php
namespace Aheadworks\Giftcard\Model\Data;

use Aheadworks\Giftcard\Api\Data\AddToQuoteResultInterface;

/**
 * Class AddToQuoteResult
 * @package Aheadworks\Giftcard\Model\Data
 */
class AddToQuoteResult extends \Magento\Framework\Api\AbstractSimpleObject implements AddToQuoteResultInterface
{
    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->_get(AddToQuoteResultInterface::SUCCESS);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_get(AddToQuoteResultInterface::MESSAGE);
    }
}
