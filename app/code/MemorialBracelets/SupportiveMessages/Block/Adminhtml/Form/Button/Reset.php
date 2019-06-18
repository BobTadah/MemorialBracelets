<?php

namespace MemorialBracelets\SupportiveMessages\Block\Adminhtml\Form\Button;

class Reset extends JavaScriptButton
{

    /** @return string */
    public function getLabel()
    {
        return __('Reset');
    }

    /** @return string */
    public function getOnClick()
    {
        return 'location.reload()';
    }

    /** @return string|string[] */
    public function getClasses()
    {
        return 'reset';
    }

    /** @return int */
    public function getSortOrder()
    {
        return 30;
    }
}
