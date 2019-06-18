<?php

namespace MemorialBracelets\IconOption\Ui\Grid\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use MemorialBracelets\IconOption\Helper\IconStorageConfiguration;
use MemorialBracelets\IconOption\Ui\Component\Column\Walkable;

class Thumbnail extends Walkable
{
    /** @var IconStorageConfiguration */
    protected $iconStorageConfiguration;

    /** @var string */
    protected $fieldName;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        IconStorageConfiguration $iconStorageConfiguration,
        array $components,
        array $data
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->iconStorageConfiguration = $iconStorageConfiguration;
        $this->fieldName = $this->getData('name');
    }

    public function processItem(array &$item)
    {
        if ($item['icon']) {
            $item[$this->fieldName.'_src'] = $this->iconStorageConfiguration->getMediaUrl($item['icon']);
            $item[$this->fieldName.'_alt'] = $this->getAlt($item);
            $item[$this->fieldName.'_orig_src'] = $this->iconStorageConfiguration->getMediaUrl($item['icon']);
        } else {
            $item['thumbnail'] = 'None Set';
        }
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: 'title';
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
