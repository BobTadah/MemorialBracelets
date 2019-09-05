<?php

namespace IWD\SalesRep\Model\ResourceModel\Order\Report;

use IWD\SalesRep\Model\ResourceModel\User as SalesrepResource;
use IWD\SalesRep\Model\User as Salesrep;
use IWD\SalesRep\Model\Order as SalesrepOrder;

/**
 * Class Collection
 * @package IWD\SalesRep\Model\ResourceModel\Order\Report
 */
class Collection extends \IWD\SalesRep\Model\ResourceModel\Order\Collection
{
    /**
     * @var bool
     */
    protected $_isTotals = false;

    /**
     * @var
     */
    private $sumColumns;

    /**
     * @var float
     */
    private $bonusCommision = 0;

    /**
     * @var array
     */
    private $bonusCommissionHash = [];

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var \Magento\Directory\Model\Currency\DefaultLocator
     */
    private $currencyLocator;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface,
        \Magento\Directory\Model\Currency\DefaultLocator $defaultLocator,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->authSession = $authSession;
        $this->localeCurrency = $currencyInterface;
        $this->currencyLocator = $defaultLocator;
        $this->request = $requestInterface;
        $this->resourceConnection = $resourceConnection;
        
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _setTotalColumns()
    {
        $columns = $this->getSelect()->getPart('columns');
        foreach ($columns as &$column) {
            if (in_array($column[2], $this->sumColumns)) {
                $column[1] = new \Zend_Db_Expr('sum(' . $column[1] . ')');
            } else {
                $column[1] = new \Zend_Db_Expr('""');
            }
        }
        $this->getSelect()->setPart('columns', $columns);
    }

    public function setPageSize($size)
    {
        $this->_pageSize = false; // select all records for this report
        return $this;
    }
    
    protected function _initSelect()
    {
        $currencyCode = $this->currencyLocator->getDefaultCurrency($this->request);
        $typeSymbol = $this->localeCurrency->getCurrency($currencyCode)->getSymbol();

        parent::_initSelect();

        // filter by salesrep
        if ($this->authSession->getUser()->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID)) {
            $this->addFieldToFilter('salesrep_id', $this->authSession->getUser()->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID));
        }

        $orderFinalPrice = 'order.grand_total - ifnull(order.total_refunded, 0)';

        /**
         * @todo add EE store credits, reward points etc
         */

        $orderApplyWhenPrice = "if (commission_apply = 'after', $orderFinalPrice, $orderFinalPrice - order.discount_amount )";

        $commissionFixedFormula = "main_table.commission_rate";
        $commissionPercentFormula = "(main_table.commission_rate * $orderApplyWhenPrice) / 100";

        $commissionFormula = "if (
                main_table.commission_type = 'fixed',
                $commissionFixedFormula,
                $commissionPercentFormula) ";

        $this->join(
            ['salesrep_user' => $this->resourceConnection->getTableName(SalesrepResource::TABLE_NAME)],
            'main_table.salesrep_id = salesrep_user.' . Salesrep::SALESREP_ID,
            []
        )
        ->join(
            ['user' => $this->resourceConnection->getTableName('admin_user')],
            'salesrep_user.' . Salesrep::ADMIN_ID . ' = user.user_id',
            [
                'name' => new \Zend_Db_Expr('concat(user.firstname, " ", user.lastname)')
            ]
        )
        ->join(
            ['order' => $this->resourceConnection->getTableName('sales_order')],
            'main_table.order_id = order.entity_id',
            [
                'total' => 'order.grand_total',
                'invoiced' => 'order.total_invoiced',
                'refund' => 'order.total_refunded',
                'status' => 'order.status',
                'period' => 'order.created_at',
                'increment_id' => 'order.increment_id',
                'customer_id' => 'order.customer_id',
                'discount' => 'order.discount_amount',
                'bonus_commission' => 'salesrep_user.bonus_commission',
                'customer_name' => new \Zend_Db_Expr('concat(order.customer_firstname, " ", order.customer_lastname)'),
                'commission' => new \Zend_Db_Expr($commissionFormula),
                'commission_desc' => new \Zend_Db_Expr('if (commission_type = "fixed", concat("' . $typeSymbol . '", commission_rate), concat( trim(trailing "." from trim(trailing "0" from commission_rate)), "%", " ", commission_apply, " discounts"))')
            ]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _beforeLoad()
    {
        if ($this->_isTotals) {
            $this->_setTotalColumns();
        }
        return parent::_beforeLoad();
    }

    /**
     * Process collection after loading
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $currencyCode = $this->currencyLocator->getDefaultCurrency($this->request);
        $typeSymbol = $this->localeCurrency->getCurrency($currencyCode)->getSymbol();

        foreach ($this->_items as $item) {
            $this->updateCommission($item, $typeSymbol);
            $this->updateBonusCommission($item, $typeSymbol);
        }

        parent::_afterLoad();

        return $this;
    }

    /**
     * @param SalesrepOrder $item
     * @param $currencySymbol
     * @return $this
     */
    protected function updateCommission(SalesrepOrder $item, $currencySymbol)
    {
        if(empty($item->getTieredOptions())) {
            return $this;
        }

        foreach($item->getTieredOptions() as $opt) {
            $total = $item->getData('total');
            if($this->criteriaMet($total, $opt['operand'], $opt['order_total'])) {
                if($opt['commission_type'] == 'fixed') {
                    $commission = $opt['commission_rate'];
                }
                elseif ($opt['commission_apply'] == 'before') {
                    $total = $total - $item->getData('discount');
                    $commission = min($opt['commission_rate'], 100) * $total / 100;
                }
                else {
                    $commission = min($opt['commission_rate'], 100) * $total / 100;
                }
                $item->setData('commission', $commission);
                $item->setData('commission_desc', $currencySymbol . $commission);
            }
        }

        return $this;
    }

    /**
     * @param SalesrepOrder $item
     * @param $currencySymbol
     * @return $this
     */
    protected function updateBonusCommission(SalesrepOrder $item, $currencySymbol)
    {
        $bonusCommision = $this->bonusCommision;
        if(floatval($item->getData('bonus_commission')) > 0) {
            $bonusCommision = floatval($item->getData('bonus_commission'));
        }
        if(empty($bonusCommision)) {
            return $this;
        }

        if($item->getData('customer_id') && $item->getData('salesrep_id')) {
            $hash = $item->getData('customer_id') . '&' . $item->getData('salesrep_id');
            if(!in_array($hash, $this->bonusCommissionHash)) {
                $this->bonusCommissionHash[] = $hash;
                $commission = $item->getData('commission') + $bonusCommision;
                $commissionDesc = $item->getData('commission_desc') . ' + ' .
                    $currencySymbol . $bonusCommision . ' bonus';
                $item->setData('commission', $commission);
                $item->setData('commission_desc', $commissionDesc);
            }
        }

        return $this;
    }

    /**
     * Criteria checker
     *
     * @param string $value1 - the value to be compared
     * @param string $operator - the operator
     * @param string $value2 - the value to test against
     * @return boolean - criteria met/not met
     */
    protected function criteriaMet($value1, $operator, $value2)
    {
        switch ($operator) {
            case '<':
                return $value1 < $value2;
                break;
            case '<=':
                return $value1 <= $value2;
                break;
            case '>':
                return $value1 > $value2;
                break;
            case '>=':
                return $value1 >= $value2;
                break;
            case '=':
                return $value1 == $value2;
                break;
            default:
                return false;
        }
    }

    /**
     * @param $value
     * @return $this
     */
    public function isTotals($value)
    {
        $this->_isTotals = $value;
        return $this;
    }

    /**
     * @param float $bonus
     */
    public function setBonusCommission($bonus)
    {
        $this->bonusCommision = floatval($bonus);
    }

    /**
     * @param $cols
     */
    public function setSumColumns($cols)
    {
        $this->sumColumns = $cols;
    }

    /**
     * @param array $statuses
     * @return $this|Collection
     */
    public function addOrderStatusFilter(array $statuses)
    {
        return $this->addFieldToFilter('status', [ 'in' => $statuses]);
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch ($field) {
            case Salesrep::SALESREP_ID:
                $this->getSelect()->where('main_table.' . SalesrepOrder::SALESREP_ID . ' = ' . $condition);
                break;
            default:
                return parent::addFieldToFilter($field, $condition);
        }

        return $this;
    }

    /**
     * @param $from
     * @param $to
     * @return $this
     */
    public function setDateRange($from, $to)
    {
        return $this
            ->addFieldToFilter('created_at', ['gteq' => $from])
            ->addFieldToFilter('created_at', ['lteq' => $to]);
    }
}
