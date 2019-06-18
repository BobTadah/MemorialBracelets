<?php

namespace MemorialBracelets\IconOption\Ui\Form\Button;

use Magento\Framework\Phrase;
use MemorialBracelets\IconOption\Api\IconOptionInterface;
use MemorialBracelets\IconOption\Ui\Component\Button\JavaScriptButton;

class Delete extends JavaScriptButton
{
    /** {@inheritdoc} */
    public function getButtonData()
    {
        if (!$this->getActiveIcon()) {
            return [];
        }
        return parent::getButtonData();
    }

    /** @return Phrase */
    public function getLabel()
    {
        return __('Delete Icon');
    }

    /** @return string */
    public function getOnClick()
    {
        $url = $this->getDeleteUrl();
        return "deleteConfirm('".__('Are you sure you want to delete this icon?')."', '{$url}')";
    }

    /** @return string|string[] */
    public function getClasses()
    {
        return 'delete';
    }

    /** @return int */
    public function getSortOrder()
    {
        return 20;
    }

    public function getDeleteUrl()
    {
        $icon = $this->getActiveIcon();
        if (is_null($icon)) {
            return $this->getUrl('*/*/');
        }

        return $this->getUrl('*/*/delete', ['id' => $icon->getId()]);
    }

    /**
     * @return IconOptionInterface|null
     */
    protected function getActiveIcon()
    {
        return $this->registry->registry('option_icon');
    }
}
