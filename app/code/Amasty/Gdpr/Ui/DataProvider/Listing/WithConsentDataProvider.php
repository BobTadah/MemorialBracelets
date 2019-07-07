<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Ui\DataProvider\Listing;

use Amasty\Gdpr\Api\WithConsentRepositoryInterface;
use Amasty\Gdpr\Model\ResourceModel\WithConsent\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class WithConsentDataProvider extends AbstractDataProvider implements DataProviderInterface
{
    /**
     * @var WithConsentRepositoryInterface
     */
    private $repository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        WithConsentRepositoryInterface $repository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->repository = $repository;
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        $data = parent::getData();
        foreach ($data['items'] as $key => $question) {
            $questionData = $this->repository->getById($question['id'])->getData();
            $data['items'][$key] = $questionData;
        }

        return $data;
    }
}
