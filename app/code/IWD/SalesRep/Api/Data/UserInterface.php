<?php

namespace IWD\SalesRep\Api\Data;

/**
 * Interface UserInterface
 * @package IWD\SalesRep\Api\Data
 */
interface UserInterface
{
    const SALESREP_ID = 'entity_id';
    const ENABLED = 'enabled';
    const ADMIN_ID = 'admin_user_id';
    const BONUS_COMMISSION = 'bonus_commission';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getEnabled();

    /**
     * @return int
     */
    public function getAdminId();

    /**
     * @return float
     */
    public function getBonusCommission();

    /**
     * @param $id
     * @return \IWD\SalesRep\Api\Data\UserInterface
     */
    public function setId($id);

    /**
     * @param $enabled
     * @return \IWD\SalesRep\Api\Data\UserInterface
     */
    public function setEnabled($enabled);

    /**
     * @param int $adminId
     * @return \IWD\SalesRep\Api\Data\UserInterface
     */
    public function setAdminId($adminId);

    /**
     * @param float $bonusCommission
     * @return \IWD\SalesRep\Api\Data\UserInterface
     */
    public function setBonusCommission($bonusCommission);
}
