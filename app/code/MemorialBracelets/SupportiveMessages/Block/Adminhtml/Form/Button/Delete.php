<?php

namespace MemorialBracelets\SupportiveMessages\Block\Adminhtml\Form\Button;

use MemorialBracelets\SupportiveMessages\Api\SupportiveMessageInterface;

class Delete extends JavaScriptButton
{
    /** @return boolean */
    public function getEnabled()
    {
        return !is_null($this->getActiveMessage());
    }

    /** @return string */
    public function getLabel()
    {
        return __('Delete Message');
    }

    /** @return string */
    public function getOnClick()
    {
        $url = $this->getDeleteUrl();
        return "deleteConfirm('".__('Are you sure you want to delete this message?')."', '".$url."');";
    }

    /** @return string|string[] */
    public function getClasses()
    {
        return ['delete'];
    }

    /** @return int */
    public function getSortOrder()
    {
        return 20;
    }

    /** @return string */
    public function getDeleteUrl()
    {
        $message = $this->getActiveMessage();
        if (is_null($message)) {
            return $this->getUrl('*/*/');
        }

        return $this->getUrl('*/*/delete', ['id' => $message->getId()]);
    }

    /** @return SupportiveMessageInterface|null */
    public function getActiveMessage()
    {
        return $this->registry->registry('supportivemessage');
    }
}
