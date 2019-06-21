<?php
namespace Aheadworks\Giftcard\Model\Source\Giftcard;

class Website implements \Magento\Framework\Option\ArrayInterface
{
    protected $_websiteCollection;

    public function __construct(\Magento\Store\Model\ResourceModel\Website\Collection $collection)
    {
        $this->_websiteCollection = $collection;
    }
     /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_websiteCollection as $website) {
            $options[] = ['label' => $website->getName(), 'value' => $website->getId()];
        }
        return $options;
    }
}
