<?php
namespace Aheadworks\Giftcard\Model\Product\Entity\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class Templates
 * @package Aheadworks\Giftcard\Model\Product\Entity\Attribute\Backend
 */
class Templates extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Aheadworks\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Templates
     */
    protected $resourceTemplates;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    public function __construct(
        \Aheadworks\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Templates $resourceTemplates,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->resourceTemplates = $resourceTemplates;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\Catalog\Model\Product $object
     * @return $this|bool
     * @throws LocalizedException
     */
    public function validate($object)
    {
        if ($this->_isPhysical($object)) {
            return $this;
        }
        $templatesRows = $object->getData($this->getAttribute()->getName());
        $savedRowsCount = 0;
        $storesTemplates = [];
        $allStoresTemplates = [];
        if ($templatesRows !== null) {
            foreach ($templatesRows as $data) {
                if (!isset($data['template']) || !empty($data['delete'])) {
                    continue;
                }

                $savedRowsCount++;

                $storeId = $data['store_id'];
                $template = $data['template'];
                if ($storeId == 0) {
                    $allStoresTemplates[] = $template;
                } else {
                    if (!isset($storesTemplates[$storeId])) {
                        $storesTemplates[$storeId] = [];
                    }
                    $storesTemplates[$storeId][] = $template;
                }
            }
        }
        $duplicate = false;
        foreach ($storesTemplates as $storeId => $templates) {
            $duplicatesWithAll = array_intersect($allStoresTemplates, $templates);
            if (!empty($duplicatesWithAll)) {
                $duplicate = true;
                break;
            }
            while ($template = array_shift($templates)) {
                if (in_array($template, $templates)) {
                    $duplicate = true;
                    break;
                }
            }
        }
        if (!$duplicate) {
            while ($template = array_shift($allStoresTemplates)) {
                if (in_array($template, $allStoresTemplates)) {
                    $duplicate = true;
                    break;
                }
            }
        }
        if ($duplicate) {
            throw new LocalizedException(__('Duplicate template found.'));
        }
        if ($savedRowsCount == 0) {
            throw new LocalizedException(__('Specify email templates options.'));
        }
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     */
    public function afterSave($object)
    {
        $templatesRows = !$this->_isPhysical($object) ?
            $object->getData($this->getAttribute()->getName()) :
            []
        ;
        if ($templatesRows === null) {
            return $this;
        }

        $old = $new = [];

        $origTemplates = $object->getOrigData($this->getAttribute()->getName());
        if (!is_array($origTemplates)) {
            $origTemplates = [];
        }
        foreach ($origTemplates as $data) {
            if ($data['store_id'] > 0 || $data['store_id'] == '0') {
                $key = join('-', [$data['store_id'], $data['template']]);
                $old[$key] = [
                    'store_id' => $data['store_id'],
                    'value_id' => $data['value_id'],
                    'value' => $data['template'],
                    'image' => isset($data['image']) ? $data['image'] : '',
                ];
            }
        }

        $magentoVersion = $this->getMagentoVersion();
        foreach ($templatesRows as $data) {
            if (!empty($data['delete'])) {
                continue;
            }

            if (version_compare($magentoVersion, '2.1.0', '>=')) {
                $imageValue = isset($data['image']) ? $this->_getImage($data['image']) : '';
            } else {
                $imageValue = isset($data['image']) ? $data['image'] : '';
            }

            $key = join('-', [$data['store_id'], $data['template']]);
            $new[$key] = [
                'store_id' => $data['store_id'],
                'value' => $data['template'],
                'image' => $imageValue,
            ];
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        $isChanged = false;
        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->resourceTemplates->deleteTemplatesData($this->_getTemplatesObject($data, $object->getId()));
                $isChanged = true;
            }
        }
        if (!empty($insert)) {
            foreach ($insert as $data) {
                $this->resourceTemplates->saveTemplatesData($this->_getTemplatesObject($data, $object->getId()));
                $isChanged = true;
            }
        }
        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['value'] != $v['value'] || $old[$k]['image'] != $v['image']) {
                    $this->resourceTemplates->saveTemplatesData(
                        $this->_getTemplatesObject(array_merge($v, ['value_id' => $old[$k]['value_id']]), $object->getId())
                    );
                    $isChanged = true;
                }
            }
        }
        if ($isChanged) {
            $valueChangedKey = $this->getAttribute()->getName() . '_changed';
            $object->setData($valueChangedKey, 1);
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $data = $this->resourceTemplates->loadTemplatesData($object->getId());
        $object->setData($this->getAttribute()->getName(), $data);
        $object->setOrigData($this->getAttribute()->getName(), $data);

        $valueChangedKey = $this->getAttribute()->getName() . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);
        return $this;
    }

    /**
     * @param array $data
     * @param int $entityId
     * @return \Magento\Framework\DataObject
     */
    protected function _getTemplatesObject($data, $entityId)
    {
        $data['entity_id'] = $entityId;
        return new \Magento\Framework\DataObject($data);
    }

    /**
     * @param \Magento\Catalog\Model\Product $object
     * @return bool
     */
    protected function _isPhysical($object)
    {
        return $object->getTypeInstance()->isTypePhysical($object);
    }

    /**
     * @param array $data
     * @return string
     */
    protected function _getImage($imageData)
    {
        $result = '';
        if (is_array($imageData)) {
            $image = array_shift($imageData);
            if (isset($image['file'])) {
                $result = $image['file'];
            }
        }
        return $result;
    }

    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
