<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Page;

/**
 * Class Menu
 * @package Aheadworks\Giftcard\Block\Adminhtml\Page
 */
class Menu extends \Magento\Backend\Block\Template
{
    /**
     * @var null|array
     */
    protected $_items = null;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Giftcard::page/menu.phtml';

    /**
     * @var string
     */
    protected $_className = 'aw-gc-menu';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get menu container class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->_className;
    }

    public function getMenuItems()
    {
        if ($this->_items === null) {
            $items = [
                'giftcard' => [
                    'title' => __('Gift Card Codes'),
                    'url' => $this->getUrl('*/giftcard/index'),
                    'resource' => 'Aheadworks_Giftcard::giftcard_codes'
                ],
                'product' => [
                    'title' => __('Gift Card Products'),
                    'url' => $this->getUrl('*/product/index'),
                    'resource' => 'Aheadworks_Giftcard::giftcard_products'
                ],
                'system_config' => [
                    'title' => __('Settings'),
                    'url' => $this->getUrl('adminhtml/system_config/edit', ['section' => 'aw_giftcard'])
                ],
                'readme' => [
                    'title' => __('Readme'),
                    'url' => 'http://confluence.aheadworks.com/display/EUDOC/Gift+Card+-+Magento+2',
                    'attr' => [
                        'target' => '_blank'
                    ],
                    'separator' => true
                ],
                'support' => [
                    'title' => __('Get Support'),
                    'url' => 'http://ecommerce.aheadworks.com/contacts/',
                    'attr' => [
                        'target' => '_blank'
                    ]
                ]
            ];
            foreach ($items as $index => $item) {
                if (array_key_exists('resource', $item)) {
                    if (!$this->_authorization->isAllowed($item['resource'])) {
                        unset($items[$index]);
                    }
                }
            }
            $this->_items = $items;
        }
        return $this->_items;
    }

    /**
     * @return array
     */
    public function getCurrentItem()
    {
        $items = $this->getMenuItems();
        $controllerName = $this->getRequest()->getControllerName();
        if (array_key_exists($controllerName, $items)) {
            return $items[$controllerName];
        }
        return $items['giftcard'];
    }

    /**
     * @param array $item
     * @return string
     */
    public function renderAttributes(array $item)
    {
        $result = '';
        if (isset($item['attr'])) {
            foreach ($item['attr'] as $attrName => $attrValue) {
                $result .= sprintf(' %s=\'%s\'', $attrName, $attrValue);
            }
        }
        return $result;
    }

    /**
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        return $itemIndex == $this->getRequest()->getControllerName();
    }
}
