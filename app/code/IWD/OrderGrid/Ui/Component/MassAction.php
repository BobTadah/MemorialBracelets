<?php

namespace IWD\OrderGrid\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\MassAction as BaseMassAction;
use IWD\OrderGrid\Helper\Config;

/**
 * Class MassAction
 * @package IWD\OrderGrid\Ui\Component
 */
class MassAction extends BaseMassAction
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * MassAction constructor.
     * @param Config $configHelper
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Config $configHelper,
        ContextInterface $context,
        $components = [],
        array $data = []
    )
    {
        $this->configHelper = $configHelper;
        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        $config = $this->getConfiguration();
        foreach ($this->getChildComponents() as $actionComponent) {
            $config['actions'][] = $actionComponent->getConfiguration();
        };

        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }

        $newConfigActions = [];
        foreach ($config['actions'] as $configItem) {
           $res =  $this->configHelper->removeMassAction($configItem['type']);
            if ($res) {
                continue;
            }

            $newConfigActions[] = $configItem;
        }

        $config['actions'] = $newConfigActions;

        $this->setData('config', $config);
        $this->components = [];

        parent::prepare();
    }
}