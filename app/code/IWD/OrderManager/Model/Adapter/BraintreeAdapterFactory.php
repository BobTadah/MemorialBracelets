<?php

namespace IWD\OrderManager\Model\Adapter;

use Magento\Framework\ObjectManagerInterface;
use Magento\Braintree\Gateway\Config\Config;

//support old version braintree

class BraintreeAdapterFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function create($storeId = null)
    {
        return $this->objectManager->create(
            \Magento\Braintree\Model\Adapter\BraintreeAdapter::class,
            [
                'merchantId' => $this->config->getMerchantId($storeId),
                'publicKey' => $this->config->getValue(Config::KEY_PUBLIC_KEY, $storeId),
                'privateKey' => $this->config->getValue(Config::KEY_PRIVATE_KEY, $storeId),
                'environment' => $this->config->getEnvironment($storeId)
            ]
        );
    }
}
