<?php
namespace Aheadworks\Giftcard\Model;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Giftcard\Model\Source\Entity\Attribute\GiftcardType;
use \Aheadworks\Giftcard\Model\Source\Giftcard\Status;
use Aheadworks\Giftcard\Model\Source\Giftcard\EmailTemplate;

/**
 * Gift Card Model
 *
 * @method Giftcard setType(int)
 * @method Giftcard setBalance(float)
 * @method Giftcard setInitialBalance(float)
 * @method Giftcard setActive(bool)
 * @method Giftcard setState(int)
 * @method Giftcard setIsUsed(int)
 * @method Giftcard setExpireAt(string)
 * @method Giftcard setWebsiteId(int)
 * @method Giftcard setProductId(int)
 * @method Giftcard setProductName(string)
 *
 * @method int getType()
 * @method bool getActive()
 * @method string getCode()
 * @method string getState()
 * @method string getIsUsed()
 * @method string getExpireAt()
 * @method float getBalance()
 * @method float getInitialBalance()
 * @method int getReferenceId()
 * @method int getProductId()
 * @method int getOrderId()
 * @method int getWebsiteId()
 * @method string getSenderName()
 * @method string getSenderEmail()
 * @method string getRecipientName()
 * @method string getRecipientEmail()
 * @method string getEmailTemplate()
 * @method float getGiftcardAmount()
 * @method float getBaseGiftcardAmount()
 */
class Giftcard extends \Magento\Framework\Model\AbstractModel
{
    const XML_PATH_GIFTCARD_EXPIRE_DAYS = 'aw_giftcard/general/expire_days';

    /**
     * @var  Status
     */
    protected $_giftCardStatus;

    /**
     * @var Source\Entity\Attribute\GiftcardType
     */
    protected $_giftCardType;

    /**
     * @var Source\Giftcard\IsUsed
     */
    protected $giftCardIsUsed;

    /**
     * @var  \Aheadworks\Giftcard\Model\HistoryFactory
     */
    protected $_giftCardHistoryFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param HistoryFactory $giftCardHistoryFactory
     * @param Status $giftCardStatus
     * @param Source\Giftcard\IsUsed $giftCardIsUsed
     * @param GiftcardType $giftCardType
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        \Aheadworks\Giftcard\Model\HistoryFactory $giftCardHistoryFactory,
        Status $giftCardStatus,
        Source\Entity\Attribute\GiftcardType $giftCardType,
        Source\Giftcard\IsUsed $giftCardIsUsed,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->_giftCardHistoryFactory = $giftCardHistoryFactory;
        $this->_giftCardType = $giftCardType;
        $this->_giftCardStatus = $giftCardStatus;
        $this->giftCardIsUsed = $giftCardIsUsed;
        $this->_escaper = $escaper;
        $this->customerFactory = $customerFactory;
        $this->orderFactory = $orderFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        parent::__construct($context, $registry);
    }

    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\ResourceModel\Giftcard');
    }

    public function loadByCode($code)
    {
        return $this->load($code, 'code');
    }

    public function isPhysical()
    {
        return $this->getType() == GiftcardType::VALUE_PHYSICAL;
    }

    /**
     * @param string $emailTemplate
     * @return $this
     */
    public function setEmailTemplate($emailTemplate)
    {
        if ($this->isPhysical()) {
            $emailTemplate = EmailTemplate::DO_NOT_SEND_VALUE;
        }
        $this->setData('email_template', $emailTemplate);
        return $this;
    }

    /**
     * Set Sender data
     *
     * @param array $data
     * @return $this
     */
    public function setSenderData($data)
    {
        if (!isset($data['senderName'])) {
            // todo: throw exception
        }
        $this->setData('sender_name', $data['senderName']);
        if (!$this->isPhysical()) {
            if (!isset($data['senderEmail'])) {
                // todo: throw exception
            }
            $this->setData('sender_email', $data['senderEmail']);
        }
        return $this;
    }

    /**
     * Set Recipient data
     *
     * @param array $data
     * @return $this
     */
    public function setRecipientData($data)
    {
        if (!isset($data['recipientName'])) {
            // todo: throw exception
        }
        $this->setData('recipient_name', $data['recipientName']);
        if (!$this->isPhysical()) {
            if (!isset($data['recipientEmail'])) {
                // todo: throw exception
            }
            $this->setData('recipient_email', $data['recipientEmail']);
        }
        return $this;
    }

    public function getRecipientCustomer()  //TODO Store this entity in a class variable
    {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($this->getWebsiteId())->loadByEmail($this->getRecipientEmail());
        if ($customer->getId()) {
            return $customer;
        }
        return null;
    }

    /**
     * Retreive entity of the order by which the gift card was created
     * @return \Magento\Sales\Model\Order|null
     */
    public function getInitialOrder()   //TODO Store this entity in a class variable
    {
        $order = $this->orderFactory->create();
        $order->load($this->getOrderId());
        if ($order->getId()) {
            return $order;
        }
        return null;
    }

    public function validate($storeId = null)
    {
        if (
            $this->getState() != Status::AVAILABLE_VALUE
        ) {
            throw new LocalizedException(__($this->_giftCardStatus->getErrorMessage($this->getState())));
        }
        if ($this->isExpired()) {
            throw new LocalizedException(__(Status::EXPIRED_ERROR_MESSAGE));
        }
        $websiteId = $storeId ?
            $this->_storeManager->getStore($storeId)->getWebsiteId() :
            $this->_storeManager->getWebsite()->getId()
        ;
        if ($websiteId != $this->getWebsiteId()) {
            throw new LocalizedException(__(Status::DEFAULT_ERROR_MESSAGE));
        }
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isValidForRedeem($storeId = null)
    {
        try {
            $this->validate($storeId);
            return true;
        } catch (LocalizedException $e) {
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $today = $this->_localeDate
            ->date(null, null, false)
            ->setTime(0, 0)
        ;
        $expired = $this->_localeDate
            ->date($this->getExpireAt(), null, false)
            ->setTime(0, 0)
        ;
        return $today > $expired;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        return !($this->getBalance() > 0);
    }

    /**
     * @return bool
     */
    public function isPartiallyUsed()
    {
        return !$this->isUsed() && ($this->getBalance() < $this->getInitialBalance());
    }

    public function _afterLoad()
    {
        $this
            ->attachStateText()
            ->attachTypeText()
            ->attachIsUsedText()
        ;
        return parent::_afterLoad();
    }

    /**
     * Attach gift card state to the model
     *
     * @return $this
     */
    protected function attachStateText()
    {
        $stateLabel = $this->_giftCardStatus->getOptionByValue($this->getState());
        if (null !== $stateLabel) {
            $this->setStateText($stateLabel);
        }
        return $this;
    }

    /**
     * Attach gift card type to the model
     *
     * @return $this
     */
    protected function attachTypeText()
    {
        $typeLabel = $this->_giftCardType->getOptionText($this->gettype());
        if (null !== $typeLabel) {
            $this->setTypeText($typeLabel);
        }
        return $this;
    }

    /**
     * Attach gift card is used to the model
     *
     * @return $this
     */
    protected function attachIsUsedText()
    {
        $isUsedLabel = $this->giftCardIsUsed->getOptionByValue($this->getIsUsed());
        if (null !== $isUsedLabel) {
            $this->setIsUsedText($isUsedLabel);
        }
        return $this;
    }

    public function beforeSave()
    {
        $_result = parent::beforeSave();

        if ($this->isObjectNew())
        {
            $expireAfter = '';
            $data = $this->getData();
            if (isset($data['use_config_expire_after'])) {
                $expireAfter = $this->_scopeConfig->getValue(
                    self::XML_PATH_GIFTCARD_EXPIRE_DAYS,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $data['website_id']
                );
            } elseif (isset($data['expire_after'])) {
                $expireAfter = $data['expire_after'];
            }
            if ($expireAfter) {
                $this->setData(
                    'expire_at',
                    $this->_localeDate
                        ->date('+' . $expireAfter . 'days', null, false)
                        ->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT)
                );
            }
        }
        return $_result;
    }

    public function afterSave()
    {
        $_result = parent::afterSave();
        $this->_registerHistory();
        return $_result;
    }

    protected function _registerHistory()
    {
        /** @var \Aheadworks\Giftcard\Model\History $giftCardHistory */
        $giftCardHistory = $this->_giftCardHistoryFactory->create();
        if ($this->isObjectNew()) {
            $giftCardHistory->registerAction(Source\History\Actions::CREATED_VALUE, $this);
        }
        if (
            !$this->isObjectNew() && $this->getOrigData('balance') > $this->getBalance()
            && null !== $this->getOrder()
        ) {
            $giftCardHistory->registerAction(
                $this->isPartiallyUsed() ? Source\History\Actions::PARTIALLY_USED_VALUE : Source\History\Actions::USED_VALUE,
                $this
            );
        }
        if (!$this->isObjectNew() && null !== $this->getCreditmemo()) {
            $giftCardHistory->registerAction(Source\History\Actions::DEACTIVATED_VALUE, $this);
        }
        if (!$this->isObjectNew() && $this->getOrigData('balance') != $this->getBalance()) {
            if (null === $this->getOrder()) {
                $giftCardHistory->registerAction(Source\History\Actions::UPDATED_VALUE, $this);
            }
        }
        if ($this->getOrigData('state') && $this->getState() == Status::EXPIRED_VALUE) {
            $giftCardHistory->registerAction(Source\History\Actions::EXPIRED_VALUE, $this);
        }
        return $this;
    }
}