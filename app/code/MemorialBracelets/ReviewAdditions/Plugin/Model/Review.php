<?php

namespace MemorialBracelets\ReviewAdditions\Plugin\Model;

class Review
{
    /**
     * Validate review fields
     *
     * @return bool|string[]
     */
    public function aroundValidate(\Magento\Review\Model\Review $review, callable $proceed)
    {
        $errors = [];

        if (!\Zend_Validate::is($review->getNickname(), 'NotEmpty')) {
            $errors[] = __('Please enter a nickname.');
        }

        if (!\Zend_Validate::is($review->getDetail(), 'NotEmpty')) {
            $errors[] = __('Please enter a review.');
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }
}
