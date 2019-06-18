<?php

namespace MemorialBracelets\NameProduct\Model\Product\Type;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Module\Manager;

class Plugin
{
    /** @var  Manager */
    protected $moduleManager;

    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function afterGetOptionArray(Type $subject, array $result)
    {
        if (!$this->moduleManager->isOutputEnabled('MemorialBracelets_NameProduct')) {
            unset($result[Name::TYPE_CODE]);
        }
        return $result;
    }
}
