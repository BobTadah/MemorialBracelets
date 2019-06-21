<?php
namespace Aheadworks\Giftcard\Api\Data;

/**
 * Interface AddToQuoteResultInterface
 * @package Aheadworks\Giftcard\Api\Data
 */
interface AddToQuoteResultInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const SUCCESS = 'success';
    const MESSAGE = 'message';
    /**#@-*/

    /**
     * @return bool
     */
    public function getSuccess();

    /**
     * @return string
     */
    public function getMessage();
}
