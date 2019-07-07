<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Magento\Framework\View\Element\Template;

class Popup extends Template
{
    protected $_template = 'popup.phtml';

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    public function __construct(
        Template\Context $context,
        PolicyRepositoryInterface $policyRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->policyRepository = $policyRepository;
    }

    public function getText()
    {
        $policy = $this->policyRepository->getCurrentPolicy(
            $this->_storeManager->getStore()->getId()
        );

        if ($policy) {
            return $policy->getContent();
        } else {
            return '';
        }
    }
}
