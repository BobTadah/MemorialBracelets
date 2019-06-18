<?php
namespace MemorialBracelets\ExtensibleCustomOption\Helper;

use function Couchbase\defaultDecoder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use MemorialBracelets\NameProduct\Model\Product\Type\Name;

class ExportData extends AbstractHelper
{
    const CUSTOM_ENGRAVING = "custom";
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Name
     */
    private $nameProduct;

    /**
     * ExportData constructor.
     */
    public function __construct(
        ProductRepository $productRepository,
        Config $config,
        Name $nameProduct
    ) {
        $this->productRepository = $productRepository;
        $this->config = $config;
        $this->nameProduct = $nameProduct;
    }

    /**
     * @param $itemOptions array
     * @return int
     */
    public function getEngravingUsedLinesQty($itemOptions)
    {
        $qty = null;

        //Early exit if no options.
        if (!isset($itemOptions['options'])) {
            return null;
        }

        //Get engraving type option data.
        $optionData = $this->getOptionData('engraving', $itemOptions['options']);

        //Count the used engraving lines.
        if ($optionData) {
            $parts = $this->splitDataValue($optionData);
            $qty = count($parts);
        }

        return $qty;
    }

    /**
     * If the option type exists in the item options set, then returns the option data (array with label, value, etc.)
     * @param $optionType string
     * @param $itemOptions array
     * @return null|array
     */
    private function getOptionData($optionType, $itemOptions)
    {
        foreach ($itemOptions as $option) {
            if ($option['option_type'] == $optionType) {
                return $option;
            }
        }
        return null;
    }

    /**
     * @param $itemOptions array
     * @return null|string
     */
    public function getEngravingType($itemOptions)
    {
        $productEvent = null;

        //Early exit if no options.
        if (!isset($itemOptions['options'])) {
            return null;
        }

        //Get engraving type option data.
        $optionData = $this->getOptionData('engraving', $itemOptions['options']);

        try {
            //Grab product associated to bracelet so can get the event_value attribute.
            $product = $this->productRepository->getById($itemOptions['info_buyRequest']['product']);

            //If have event, grab the label.
            if ($productEventId = $product->getEvent()) {
                $attribute = $this->config->getAttribute(Product::ENTITY, 'event');
                $options = $attribute->getSource()->getAllOptions();

                //Find the event description.
                foreach ($options as $option) {
                    if ((int) $option['value'] === (int) $productEventId) {
                        $productEvent = $option['label'];
                        break;
                    }
                }
            }

            //Sanitize engraving strings to make the comparisson.
            $currentEngraving = strtoupper(str_replace("\r", "", $optionData['value']));
            $currentEngraving = strtoupper(str_replace("\n", "", $currentEngraving));
            $defaultEngraving = strtoupper(str_replace("\r", "", $this->nameProduct->getNameInfo($product)));
            $defaultEngraving = strtoupper(str_replace("\n", "", $defaultEngraving));

            $engravingType = null;
            if ($optionData && array_key_exists('option_value', $optionData)) {
                //Sanitize option_value data before trying to decode and grab the engraving type.
                $optionValue = str_replace("\r", "", $optionData["option_value"]);
                $optionValue = str_replace("\n", "", $optionValue);
                $optionValue = json_decode($optionValue);
                $engravingType = isset($optionValue->type) ? $optionValue->type : null;
            }

            //Compare the current with the original engraving in order to decide which description return.
            if ($currentEngraving == $defaultEngraving || ($engravingType == 'name' && !empty($productEvent))) {
                return $productEvent;
            } else {
                return self::CUSTOM_ENGRAVING;
            }
        } catch (\Exception $e) {
            //Couldn't load product with used id.
            //TODO: Log exception.
        }
    }

    /**
     * @param $optionData
     * @return array
     */
    private function splitDataValue($optionData)
    {
        //Since new line separator can be \r\n or just \n, first we sanitize by removing \r from the string.
        $optionData['value'] = str_replace("\r", "", $optionData['value']);
        $parts = array_filter(explode("\n", $optionData['value']));

        return $parts;
    }
}
