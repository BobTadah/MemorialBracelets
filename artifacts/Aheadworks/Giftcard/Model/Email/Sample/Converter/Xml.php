<?php
namespace Aheadworks\Giftcard\Model\Email\Sample\Converter;

/**
 * Class Xml
 * @package Aheadworks\Giftcard\Model\Email\Sample\Converter
 */
class Xml implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        $events = $source->getElementsByTagName('template');
        foreach ($events as $event) {
            $eventData = [];
            /** @var $event \DOMElement */
            foreach ($event->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                /** @var $event \DOMElement */
                $eventData[$child->nodeName] = $child->nodeValue;
            }
            $output[] = $eventData;
        }
        return $output;
    }
}
