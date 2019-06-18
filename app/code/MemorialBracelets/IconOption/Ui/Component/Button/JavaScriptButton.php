<?php

namespace MemorialBracelets\IconOption\Ui\Component\Button;

use Magento\Framework\Phrase;

abstract class JavaScriptButton extends Generic
{
    /** @return boolean */
    public function getEnabled()
    {
        return true;
    }

    /** @return Phrase */
    abstract public function getLabel();

    /** @return string */
    abstract public function getOnClick();

    /** @return string|string[] */
    abstract public function getClasses();

    /** @return int */
    abstract public function getSortOrder();

    public function getButtonData()
    {
        if (!$this->getEnabled()) {
            return [];
        }
        return [
            'label' => $this->getLabel(),
            'on_click' => $this->getOnClick(),
            'class' => is_array($this->getClasses()) ? implode(' ', $this->getClasses()) : $this->getClasses(),
            'sort_order' => $this->getSortOrder(),
        ];
    }
}
