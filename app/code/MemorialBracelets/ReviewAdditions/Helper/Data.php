<?php

namespace MemorialBracelets\ReviewAdditions\Helper;

use Magento\Framework\Registry;

/**
 * Class Data
 *
 * @package MemorialBracelets\ReviewAdditions\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * This function will return whether the product is an instance of the 'name'
     * type. If true, the function will return a true boolean value. If false, it will
     * return null.
     *
     * @return bool|null
     */
    public function productNameTypeCheck()
    {
        $flag    = null;
        $product = $this->registry->registry('product');

        if ($product) {//if product exists and is an instance of the name type.
            if ($product->getTypeId() == 'name') {
                $flag = true;
            }
        }

        return $flag;
    }
}
