<?php

namespace MemorialBracelets\CharmOption\Block\Adminhtml\Form\Button;

use MemorialBracelets\CharmOption\Api\CharmOptionInterface;
use MemorialBracelets\CharmOption\Model\CharmOption;

class Delete extends Generic
{

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getActiveCharm()) {
            $data = [
                'label' => __('Delete Option'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to delete this option?').'\', \''.$this->getDeleteUrl().'\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    public function getDeleteUrl()
    {
        $charm = $this->getActiveCharm();
        if (is_null($charm)) {
            return $this->getUrl('*/*/');
        }

        return $this->getUrl('*/*/delete', ['id' => $charm->getId()]);
    }

    /**
     * @return CharmOptionInterface|null
     */
    protected function getActiveCharm()
    {
        return $this->registry->registry('option_charm');
    }
}
