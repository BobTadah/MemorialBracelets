<?php
namespace Aheadworks\Giftcard\Model\ResourceModel;

use Aheadworks\Giftcard\Model\Source\Giftcard\IsUsed;
use Aheadworks\Giftcard\Model\Source\Giftcard\Status;
use Magento\Framework\Exception\LocalizedException;

/**
 * Giftcard resource model
 */
class Giftcard extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const CODE_GENERATION_ATTEMPTS = 1000;

    const CODE_LENGTH = 12;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    )
    {
        parent::__construct($context, $resourcePrefix);
    }

    protected function _construct()
    {
        $this->_init('aw_giftcard', 'id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $giftCard)
    {
        /** @var $giftCard \Aheadworks\Giftcard\Model\Giftcard */
        if (!$giftCard->getId()) {
            $giftCard->setBalance($giftCard->getInitialBalance());
        }
        $this
            ->_attachState($giftCard)
            ->_attachCode($giftCard)
        ;
        if ($giftCard->isPhysical()) {
            $giftCard
                ->unsetData('sender_email')
                ->unsetData('recipient_email')
            ;
        }
        if (!$giftCard->getId() && $giftCard->isExpired()) {
            throw new LocalizedException(__('Expiration date cannot be in the past.'));
        }
        if (
            $giftCard->getState() == Status::DEACTIVATED_VALUE &&
            $giftCard->getOrigData('balance') == 0 &&
            $giftCard->getOrigData('balance') != $giftCard->getBalance()
        ) {
            throw new LocalizedException(__('Unable to change balance of deactivated gift card code'));
        }
        return $this;
    }

    protected function _generateCode()
    {
        $code = '';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $strLength = strlen($characters);
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= $characters[mt_rand(0, $strLength - 1)];
        }
        return $code;
    }

    public function codeExists($code)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select
            ->from($this->getMainTable(), 'code')
            ->where('code = ?', $code)
        ;

        if ($connection->fetchOne($select) === false) {
            return false;
        }
        return true;
    }

    protected function _attachState(\Aheadworks\Giftcard\Model\Giftcard $giftCard)
    {
        $state = $giftCard->getState();
        $isUsed = $giftCard->getIsUsed();

        if (null === $state) {
            $state = Status::AVAILABLE_VALUE;
        }
        if ($state != Status::DEACTIVATED_VALUE) {
            if ($giftCard->isUsed()) {
                $state = Status::USED_VALUE;
            } elseif ($giftCard->isExpired()) {
                $state = Status::EXPIRED_VALUE;
            } else {
                $state = Status::AVAILABLE_VALUE;
            }
        }

        if ($giftCard->isUsed()) {
            $isUsed = IsUsed::YES_VALUE;
        } elseif ($giftCard->isPartiallyUsed()) {
            $isUsed = IsUsed::PARTIALLY_VALUE;
        } else {
            $isUsed = IsUsed::NO_VALUE;
        }

        $giftCard->setState($state);
        $giftCard->setIsUsed($isUsed);
        return $this;
    }

    protected function _attachCode(\Aheadworks\Giftcard\Model\Giftcard $giftCard)
    {
        if (null === $giftCard->getId()) {
            $attempt = 0;
            do {
                if ($attempt >= self::CODE_GENERATION_ATTEMPTS) {
                    throw new LocalizedException(__('Unable to create giftcard code.'));
                }
                $code = $this->_generateCode();
                $attempt++;
            } while ($this->codeExists($code));

            $giftCard->setCode($code);
        }
        return $this;
    }
}
