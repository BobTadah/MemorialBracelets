<?php

namespace MemorialBracelets\CheckHandling\Block;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Info extends Template
{
    /** @var Registry  */
    protected $_coreRegistry;
    protected $_order;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getOrder()
    {
        if ($this->_order === null) {
            if ($this->hasData('order')) {
                $this->_order = $this->_getData('order');
            } elseif ($this->_coreRegistry->registry('current_order')) {
                $this->_order = $this->_coreRegistry->registry('current_order');
            } elseif ($this->getParentBlock()->getOrder()) {
                $this->_order = $this->getParentBlock()->getOrder();
            }
        }
        return $this->_order;
    }
}
