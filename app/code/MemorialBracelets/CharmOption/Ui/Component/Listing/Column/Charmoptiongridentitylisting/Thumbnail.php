<?php

namespace MemorialBracelets\CharmOption\Ui\Component\Listing\Column\Charmoptiongridentitylisting;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use MemorialBracelets\CharmOption\Model\CharmOption\IconStorageConfiguration;

class Thumbnail extends Walkable
{
    /** @var IconStorageConfiguration  */
    protected $iconStorageConfiguration;

    private $fieldName;

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
        $item[$this->fieldName.'_src'] = $this->iconStorageConfiguration->getMediaUrl($item['icon']);
        $item[$this->fieldName.'_alt'] = $this->getAlt($item);
        $item[$this->fieldName.'_orig_src'] = $this->iconStorageConfiguration->getMediaUrl($item['icon']);
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
