<?php

namespace MemorialBracelets\LoginAddition\Plugin;

use Magento\Customer\Controller\Account\CreatePost;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class LoginPlugin
 * @see \Magento\Customer\Controller\Account\CreatePost
 * @package MemorialBracelets\LoginAddition\Plugin
 */
class LoginPlugin
{
    /**
     * @see \Magento\Customer\Controller\Account\CreatePost::execute()
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(CreatePost $subject, Redirect $result)
    {
        $requestParams = $subject->getRequest()->getParams();
        // if we created an account on the cart page modal prompt -> redirect to cart
        if (!empty($requestParams)  && $requestParams['location'] == 'cart') {
            $result->setPath('checkout/cart');
        }

        return $result;
    }
}
