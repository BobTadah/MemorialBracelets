<?php

namespace IWD\OrderGrid\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use IWD\OrderGrid\Helper\Config;

class Multiselect extends Field
{
    protected $_template = 'config/multiselect.phtml';
    protected $configHelper;

    public function __construct(
        Config $configHelper,
        Context $context,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $patch = $this->getPatch($element->getOriginalData());
        $config = $this->getConfigData($patch);
        $this->addData([
            'html_id' => $element->getHtmlId(),
            'values' => $element->getValues(),
            'name' => $element->getName(),
            'config_data' => $config,
            'aggregated_config' => $this->configHelper->aggregateConfig($config)
        ]);


        return $this->_toHtml();
    }

    protected function getPatch(array $originalData)
    {
        if (isset($originalData['path']) && isset($originalData['id'])) {
            return $originalData['path'] . '/' . $originalData['id'];
        }

        return false;
    }
}
