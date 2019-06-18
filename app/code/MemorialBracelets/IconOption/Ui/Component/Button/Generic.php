<?php

namespace MemorialBracelets\IconOption\Ui\Component\Button;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

abstract class Generic implements ButtonProviderInterface
{
    /** @var Context  */
    protected $context;

    /** @var Registry  */
    protected $registry;

    public function __construct(Registry $registry, Context $context)
    {
        $this->registry = $registry;
        $this->context = $context;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrl($route, $params);
    }

    /** @inheritdoc */
    abstract public function getButtonData();
}
