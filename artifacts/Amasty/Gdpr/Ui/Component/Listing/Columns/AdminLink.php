<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Ui\Component\Listing\Columns;

class AdminLink extends AbstractLink
{
    const URL = 'adminhtml/user/edit';
    const ID_FIELD_NAME = 'last_edited_by';
    const ID_PARAM_NAME = 'user_id';
}
