<?php
namespace Aheadworks\Giftcard\Model;

/**
 * History Model
 *
 */
class History extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Backend\Model\Auth\Session $session
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Backend\Model\Auth\Session $session,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_authSession = $session;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Aheadworks\Giftcard\Model\ResourceModel\History');
    }

    public function registerAction($actionType, \Aheadworks\Giftcard\Model\Giftcard $giftcardModel)
    {
        $info = array(
            'message_type' => Source\History\Actions::BY_ADMIN_MESSAGE_VALUE,
            'message_data' => $this->_getCurrentAdminUserName()
        );

        if (null !== $giftcardModel->getOrder()) {
            $info = array(
                'message_type' => Source\History\Actions::BY_ORDER_MESSAGE_VALUE,
                'message_data' => $giftcardModel->getOrder()->getIncrementId()
            );
        }
        if (null !== $giftcardModel->getCreditmemo()) {
            $orderIncrementId = $giftcardModel->getCreditmemo()->getIncrementId();
            if ($giftcardModel->getCreditmemo() instanceof \Magento\Sales\Model\Order\Creditmemo) {
                $orderIncrementId = $giftcardModel->getCreditmemo()->getOrder()->getIncrementId();
            }
            $info = array(
                'message_type' => Source\History\Actions::BY_CREDITMEMO_MESSAGE_VALUE,
                'message_data' => $orderIncrementId
            );
        }

        $_balanceDelta = $giftcardModel->getBalance();
        if (!$giftcardModel->isObjectNew() && null !== $giftcardModel->getOrigData('balance')) {
            $_balanceDelta = $giftcardModel->getBalance() - $giftcardModel->getOrigData('balance');
        }

        $this
            ->setGiftcardId($giftcardModel->getId())
            ->setAction($actionType)
            ->setBalanceDelta($_balanceDelta)
            ->setBalanceAmount($giftcardModel->getBalance())
            ->setAdditionalInfo($info)
            ->save()
        ;
        return $this;
    }

    protected function _getCurrentAdminUserName()
    {
        if ($user = $this->_authSession->getUser()) {
            if ($username = $user->getUsername()) {
                return $username;
            }
        }
        return null;
    }
}