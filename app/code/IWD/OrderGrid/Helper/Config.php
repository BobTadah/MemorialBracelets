<?php

namespace IWD\OrderGrid\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \IWD\OrderGrid\Model\Config\Source\Order\MassAction;
/**
 * Class Config
 * @package IWD\OrderGrid\Helper
 */
class Config
{
    const XML_PATH_ENABLED = 'iwd_ordergrid/general/enabled';
    const XML_PATH_ORDER_COLOR = 'iwd_ordergrid/general/colors';
    const XML_PATH_ORDER_COLUMNS = 'iwd_ordergrid/general/columns';
    const XML_PATH_ORDER_MASSACTION = 'iwd_ordergrid/allow_massaction/massaction_list';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var MassAction
     */
    protected $massActionSource;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param MassAction $massActionSource
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        MassAction $massActionSource
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->massActionSource = $massActionSource;
    }

    /**
     * @param $configValue
     * @return mixed
     */
    public function aggregateConfig($configValue)
    {
        parse_str($configValue, $arr);
        return $arr;
    }

    /**
     * @return mixed
     */
    public function getColors()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORDER_COLOR);
    }

    /**
     * @return mixed
     */
    public function getOrdersColor()
    {
        $colorsConfig = $this->getColors();
        return $this->aggregateConfig($colorsConfig);
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORDER_COLUMNS);
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        $status = $this->scopeConfig->getValue(self::XML_PATH_ENABLED);
        return (bool)$status;
    }

    /**
     * @param $column
     * @return bool
     */
    public function isActiveColumn($column)
    {
        return in_array($column, explode(',', $this->getColumns()));
    }

    /**
     * @return bool
     */
    public function getMassAction()
    {
        $config = $this->scopeConfig->getValue(self::XML_PATH_ORDER_MASSACTION);
        return explode(',', $config);
    }

    public function removeMassAction($actionType)
    {
        return array_key_exists($actionType, $this->massActionSource->toArray()) && !in_array($actionType,  $this->getMassAction());
    }
}
