<?php
/**
 * Created by PhpStorm.
 * User: dnyomo
 * Date: 2/9/2018
 * Time: 2:56 PM
 */

namespace MemorialBracelets\CategoryLandingPage\Plugin;

use MemorialBracelets\CategoryLandingPage\Block\Category\View;

class ListProduct extends View
{
    public function aroundGetIdentities(\Magento\Catalog\Block\Product\ListProduct $subject, callable $proceed)
    {
        if (($this->getCurrentCategory() != null) && $this->getCurrentCategory()->getDisplayMode() == 'LANDING_PAGE') {
            return [];
        }

        return $proceed();
    }
}
