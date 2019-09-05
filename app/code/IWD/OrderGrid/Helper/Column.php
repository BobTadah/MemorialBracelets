<?php

namespace IWD\OrderGrid\Helper;

use \IWD\OrderGrid\Helper\Config as ConfigHelper;
use \Magento\Backend\Model\Url;
use \Magento\Framework\App\RequestInterface;

class Column
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Column constructor.
     * @param Config $configHelper
     * @param Url $url
     * @param RequestInterface $request
     */
    public function __construct(
        ConfigHelper $configHelper,
        Url $url,
        RequestInterface $request
    )
    {

        $this->configHelper = $configHelper;
        $this->url = $url;
        $this->request = $request;
    }


    /**
     * @param $productGroup
     * @param $type
     * @return array
     */
    public function getAggregatedProductsData($productGroup, $type): array
    {
        $dataFromGrouped = $this->getDataFromGrouped($productGroup);
        $productsIdArr = $dataFromGrouped['id'];
        $productsDataArr = $dataFromGrouped[$type];

        $aggregatedProductsData = [];
        foreach ($productsIdArr as $key => $id) {
            $aggregatedProductsData[] = [$type => $productsDataArr[$key], 'url' => $this->getUrl($id, 'product')];
        }

        return $aggregatedProductsData;
    }

    /**
     * @param $itemGroup
     * @return array
     */
    public function getDataFromGrouped($itemGroup): array
    {
        $itemGroupArray = explode(',', $itemGroup);
        $productsDataArr = array_chunk($itemGroupArray, 3);
        $arr = [];
        foreach ($productsDataArr as $product) {
            if (isset($product[0]) && isset($product[1]) && isset($product[2])) {
                $arr['id'][] = $product[0];
                $arr['sku'][] = $product[1];
                $arr['name'][] = $product[2];
            }
        }

        return $arr;
    }

    /**
     * @param $ids
     * @param $numbers
     * @param $type
     * @return array
     */
    public function getAggregatedData($ids, $numbers, $type): array
    {
        $aggregatedData = [];
        if (!empty($ids)) {
            $idArr = explode(',', $ids);
            $dataArr = explode(',', $numbers);

            foreach ($idArr as $key => $id) {
                $aggregatedData[] = ['number' => $dataArr[$key], 'url' => $this->getUrl($id, $type)];
            }

        }

        return $aggregatedData;
    }

    /**
     * @param $id
     * @param $type
     * @return string
     */
    public function getUrl($id, $type)
    {
        switch ($type) {
            case 'invoice':
                $routePath = 'sales/invoice/view';
                $paramName = 'invoice_id';
                break;
            case 'shipment':
                $routePath = 'sales/shipment/view';
                $paramName = 'shipment_id';
                break;
            case 'product':
                $routePath = 'catalog/product/edit';
                $paramName = 'id';
                break;
            case 'creditmemo':
                $routePath = 'sales/creditmemo/view';
                $paramName = 'creditmemo_id';
                break;
            default:
                $routePath = '';
                $paramName = '';
        }

        return $this->url->getUrl($routePath, [$paramName => $id]);
    }
}
