<?php
namespace Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit;

/**
 * Class History
 * @package Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit
 */
class History extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Actions
     *
     * @var \Aheadworks\Giftcard\Model\Source\History\Actions
     */
    protected $_actions;

    /**
     * Wishlist item collection factory.
     *
     * @var \Aheadworks\Giftcard\Model\ResourceModel\History\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Aheadworks\Giftcard\Model\ResourceModel\History\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aheadworks\Giftcard\Model\Source\History\Actions $actions
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Aheadworks\Giftcard\Model\ResourceModel\History\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Aheadworks\Giftcard\Model\Source\History\Actions $actions,
        array $data = []
    ) {
        $this->_actions = $actions;
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initial settings.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('giftcard_history_grid');
        $this->setEmptyText(__('No records found.'));
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /* @var $giftcard \Aheadworks\Giftcard\Model\Giftcard */
        $giftcard = $this->_coreRegistry->registry('aw_giftcard');

        $collection = $this->_collectionFactory
            ->create()
            ->addGiftcardFilter($giftcard->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns.
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        /* @var $giftcard \Aheadworks\Giftcard\Model\Giftcard */
        $giftcard = $this->_coreRegistry->registry('aw_giftcard');

        $this->addColumn(
            'id',
            ['header' => __('ID'), 'index' => 'id', 'align' => 'left', 'type' => 'number']
        );
        $this->addColumn(
            'updated_at',
            [
                'header' => __('Date'),
                'type' => 'datetime',
                'index' => 'updated_at',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index'     => 'action',
                'sortable'  => false,
                'type'      => 'options',
                'options'   => $this->_actions->toOptionArray()
            ]
        );

        $currency = $this->_storeManager->getWebsite($giftcard->getWebsiteId())->getBaseCurrencyCode();
        $this->addColumn(
            'balance_amount',
           [
               'header'        => __('Balance'),
               'index'         => 'balance_amount',
               'type'          => 'currency',
               'currency' => $currency,
           ]
        );
        $this->addColumn(
            'balance_delta',
           [
               'header'        => __('Balance Change'),
               'index'         => 'balance_delta',
               'type'          => 'currency',
               'currency' => $currency,
           ]
        );
        $this->addColumn(
            'additional_info ',
            [
                'header' => __('Additional Information'),
                'type' => 'text',
                'index' => 'additional_info',
                'sortable' => false,
                'filter' => false,
                'renderer' => '\Aheadworks\Giftcard\Block\Adminhtml\Giftcard\Edit\History\Renderer\Additional'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get headers visibility
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }

    /**
     * Determine ajax url for grid refresh
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('aw_giftcard_admin/giftcard/history', ['_current' => true]);
    }
}
