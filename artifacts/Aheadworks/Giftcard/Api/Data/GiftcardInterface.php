<?php
namespace Aheadworks\Giftcard\Api\Data;

/**
 * Interface GiftcardInterface
 * @package Aheadworks\Giftcard\Api\Data
 */
interface GiftcardInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const CODE = 'code';
    const AMOUNT = 'amount';
    const REMOVE_URL = 'removeUrl';
    /**#@-*/

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @return string
     */
    public function getRemoveUrl();
}
