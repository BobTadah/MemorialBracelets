<?php

namespace MemorialBracelets\SupportiveMessages\Block\Adminhtml\Form\Button;

class Back extends JavaScriptButton
{

    /** @return string */
    public function getLabel()
    {
        return __('Back');
    }

    /** @return string */
    public function getOnClick()
    {
        return sprintf("location.href = '%s';", $this->getUrl('*/*/'));
    }

    /** @return string|string[] */
    public function getClasses()
    {
        return 'back';
    }

    /** @return int */
    public function getSortOrder()
    {
        return 10;
    }
}
