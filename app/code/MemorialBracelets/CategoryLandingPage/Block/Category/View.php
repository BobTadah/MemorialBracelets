<?php

namespace MemorialBracelets\CategoryLandingPage\Block\Category;

class View extends \Magento\Catalog\Block\Category\View
{
    /**
     * Check if category display mode is "Static Block and Products"
     * @return bool
     */
    public function isLandingMode()
    {
        return $this->getCurrentCategory()->getDisplayMode() == 'LANDING_PAGE';
    }
}
