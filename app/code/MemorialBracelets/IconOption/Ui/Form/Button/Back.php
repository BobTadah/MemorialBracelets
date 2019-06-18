<?php

namespace MemorialBracelets\IconOption\Ui\Form\Button;

use Magento\Framework\Phrase;
use MemorialBracelets\IconOption\Ui\Component\Button\JavaScriptButton;

class Back extends JavaScriptButton
{

    /** @return Phrase */
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
