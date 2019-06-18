<?php

namespace MemorialBracelets\WysiwygFix\Plugin;

use Magento\Framework\DataObject;

class WysiwygConfig
{
    /**
     * @param $subject
     * @param DataObject $config
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     * @return DataObject
     */
    public function afterGetConfig($subject, DataObject $config)
    {
        $config->addData(['add_directives' => true]);

        return $config;
    }
}
