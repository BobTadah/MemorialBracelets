<?php
namespace Aheadworks\Giftcard\Model\Product\Entity\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Giftcard\Model\Product\Type\Giftcard as TypeGiftCard;

/**
 * Class Amounts
 * @package Aheadworks\Giftcard\Model\Product\Entity\Attribute\Backend
 */
class Amounts extends \Magento\Catalog\Model\Product\Attribute\Backend\Price
{
    /**
     * @var \Aheadworks\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Amounts
     */
    protected $_resourceAmounts;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Aheadworks\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Amounts $resourceAmounts
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Aheadworks\Giftcard\Model\ResourceModel\Product\Attribute\Backend\Amounts $resourceAmounts
    ) {
        $this->_resourceAmounts = $resourceAmounts;
        parent::__construct($currencyFactory, $storeManager, $catalogData, $config, $localeFormat);
    }

    /**
     * @param \Magento\Catalog\Model\Product $object
     * @return $this|bool
     * @throws LocalizedException
     */
    public function validate($object)
    {
        $amountsRows = $object->getData($this->getAttribute()->getName());
        $amountsKeys = array();
        if ($amountsRows !== null) {
            foreach ($amountsRows as $data) {
                if (!isset($data['price']) || !empty($data['delete'])) {
                    continue;
                }
                $key = join('-', array($data['website_id'], (float)$data['price']));
                if (array_key_exists($key, $amountsKeys)) {
                    throw new LocalizedException(__('Duplicate amount found.'));
                }
                $amountsKeys[$key] = true;
            }
        }
        if (count($amountsKeys) == 0 && !$object->getData(TypeGiftCard::ATTRIBUTE_CODE_ALLOW_OPEN_AMOUNT)) {
            throw new LocalizedException(__('Specify amount options.'));
        }
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product $object
     * @return $this
     */
    public function afterSave($object)
    {
        $amountsRows = $object->getData($this->getAttribute()->getName());
        if ($amountsRows === null) {
            return $this;
        }

        $old = $new = [];

        $origAmounts = $object->getOrigData($this->getAttribute()->getName());
        if (!is_array($origAmounts)) {
            $origAmounts = [];
        }
        foreach ($origAmounts as $data) {
            if ($data['website_id'] > 0 || $data['website_id'] == '0') {
                $key = join('-', [$data['website_id'], $data['price']]);
                $old[$key] = $data;
            }
        }
        foreach ($amountsRows as $data) {
            if (!empty($data['delete'])) {
                continue;
            }

            $key = join('-', [$data['website_id'], $data['price']]);
            $new[$key] = [
                'website_id' => $data['website_id'],
                'value' => $data['price']
            ];
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        $isChanged = false;
        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->_resourceAmounts->deleteAmountData($this->_getAmountObject($data, $object->getId()));
                $isChanged = true;
            }
        }
        if (!empty($insert)) {
            foreach ($insert as $data) {
                $this->_resourceAmounts->saveAmountData($this->_getAmountObject($data, $object->getId()));
                $isChanged = true;
            }
        }
        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['price'] != $v['value'] || $old[$k]['website_id'] != $v['website_id']) {
                    $this->_resourceAmounts->saveAmountData(
                        array_merge($v, ['value_id' => $old[$k]['value_id']]),
                        $object->getId()
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
        $data = $this->_resourceAmounts->loadAmountData($object->getId());
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
    protected function _getAmountObject($data, $entityId)
    {
        $data['entity_id'] = $entityId;
        return new \Magento\Framework\DataObject($data);
    }
}
