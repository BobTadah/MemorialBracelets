<?php

namespace MemorialBracelets\NameProduct\Model\Order\Pdf\Items\Creditmemo;

use Magento\Sales\Model\Order\Pdf\Items\Creditmemo\DefaultCreditmemo;

class Name extends DefaultCreditmemo
{
    public function draw()
    {
        $type = $this->getItem()->getOrderItem()->getRealProductType();
        $renderer = $this->getRenderedModel()->getRenderer($type);
        $renderer->setOrder($this->getOrder());
        $renderer->setItem($this->getItem());
        $renderer->setPdf($this->getPdf());
        $renderer->setPage($this->getPage());

        $renderer->draw();
    }
}
