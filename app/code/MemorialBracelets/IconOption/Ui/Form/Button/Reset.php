<?php

namespace MemorialBracelets\IconOption\Ui\Form\Button;

use Magento\Framework\Phrase;
use MemorialBracelets\IconOption\Ui\Component\Button\JavaScriptButton;

class Reset extends JavaScriptButton
{

    /** @return Phrase */
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
