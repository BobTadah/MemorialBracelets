<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel\WithConsent\Grid;

class Collection extends \Amasty\Gdpr\Model\ResourceModel\Grid\AbstractSearchResult
{
    /**
     * Init collection select
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->joinCustomerData();

        return $this;
    }
}
