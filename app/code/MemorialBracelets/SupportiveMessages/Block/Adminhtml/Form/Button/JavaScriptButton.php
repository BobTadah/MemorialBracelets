<?php

namespace MemorialBracelets\SupportiveMessages\Block\Adminhtml\Form\Button;

abstract class JavaScriptButton extends Generic
{
    /** @return boolean */
    public function getEnabled()
    {
        return true;
    }

    /** @return string */
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
