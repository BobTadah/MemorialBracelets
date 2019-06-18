<?php

namespace MemorialBracelets\ReviewAdditions\Block;

/**
 * Class ReviewPager
 * @package MemorialBracelets\ReviewAdditions\Block
 */
class ReviewPager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * The list of available pager limits
     *
     * @var array
     */
    protected $_availableLimit = [4 => 4, 8 => 8, 12 => 12, 20 => 20];
}
