<?php

namespace MemorialBracelets\EngravingDisplay\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\Product\Attribute\Repository;

/**
 * Class Events
 * @package MemorialBracelets\EngravingDisplay\Model\Config\Source
 */
class Events implements ArrayInterface
{
    /** @var Repository */
    protected $attributeRepository;

    /**
     * Events constructor.
     * @param Repository $attributeRepository
     */
    public function __construct(Repository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * builds out the options for the events attribute.
     * @return array
     */
    public function toOptionArray()
    {
        $data   = [];
        $events = $this->attributeRepository->get('event')->getOptions();

        if ($events) {
            foreach ($events as $event) {
                $data[] = [
                    'value' => $event->getValue(),
                    'label' => $event->getLabel()
                ];
            }
        }

        return $data;
    }
}
