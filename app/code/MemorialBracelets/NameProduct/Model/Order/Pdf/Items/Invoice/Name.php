<?php

namespace MemorialBracelets\NameProduct\Model\Order\Pdf\Items\Invoice;

use Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice;

class Name extends DefaultInvoice
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
