<?php

namespace MemorialBracelets\SwatchOption\Plugin;

use Magento\Catalog\Model\Product\Option;
use \Magento\Framework\Json\Encoder as JsonEncoder;

class OptionSaveModifier
{
    protected $encoder;

    public function __construct(JsonEncoder $jsonEncoder)
    {
        $this->encoder = $jsonEncoder;
    }

    public function beforeAfterSave(Option $subject)
    {
        $subject->setValues($subject->getData('swatches'));
    }
}
