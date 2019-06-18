<?php

namespace MemorialBracelets\ReviewImport\Console\Command;

use Magento\Framework\App\State;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManagerFactory;
use Symfony\Component\Console\Input\InputOption;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\Review;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;

/**
 * Class ReviewImportCommand
 * @package MemorialBracelets\ReviewImport\Console\Command
 */
class ReviewImportCommand extends Command
{
    /** @var StoreManager $storeManager */
    protected $storeManager;

    /** @var ObjectManagerInterface $objectManager */
    protected $objectManager;

    /** @var DirectoryList $directoryList */
    protected $directoryList;

    /** @var ReviewFactory $reviewFactory */
    protected $reviewFactory;

    /** @var ProductRepositoryInterface $productRepository */
    protected $productRepository;

    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var LoggerInterface logger */
    protected $logger;

    /**
     * constant vars: positions of columns in csv
     */
    const CSV_PRODUCT_POSITION = 0;
    const CSV_REVIEW_POSITION  = 1;
    const CSV_DATE_POSITION    = 2;
    const CSV_NAME_POSITION    = 3;

    /**
     * ReviewImportCommand constructor.
     * @param StoreManager               $storeManager
     * @param ObjectManagerFactory       $objectManagerFactory
     * @param State                      $state
     * @param DirectoryList              $directoryList
     * @param ReviewFactory              $reviewFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder      $searchCriteriaBuilder
     * @param LoggerInterface            $logger
     */
    public function __construct(
        StoreManager $storeManager,
        ObjectManagerFactory $objectManagerFactory,
        State $state,
        DirectoryList $directoryList,
        ReviewFactory $reviewFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->storeManager                   = $storeManager;
        $params                               = $_SERVER;
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[StoreManager::PARAM_RUN_TYPE] = 'store';
        $this->objectManager                  = $objectManagerFactory->create($params);
        $state->setAreaCode(Area::AREA_FRONTEND);

        $this->directoryList         = $directoryList;
        $this->reviewFactory         = $reviewFactory;
        $this->productRepository     = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger                = $logger;
        parent::__construct();
    }

    /**
     * set up command/arguments name and description
     */
    protected function configure()
    {
        // set command name and description
        $this->setName('reviews:products:import')->setDescription('Imports Product Reviews');

        // set arguments name and description
        $this->setDefinition(
            [
                new InputOption(
                    'csv-path',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Path to csv file within Magento.',
                    null
                ),
                new InputOption(
                    'store-id',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Magento store id.',
                    null
                )
            ]
        );

        parent::configure();
    }

    /**
     * this will attempt to load the csv and save each entry.
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting Import:</info>');

        $magentoRoot  = $this->directoryList->getPath(DirectoryList::ROOT);
        $csvPath      = $magentoRoot . '/' . $input->getOption('csv-path');
        $failureArray = [];

        if (!file_exists($csvPath)) { // file not found
            $output->writeln("<error>CVS file not found in {$csvPath}</error>");
            return false;
        }

        // set up the store id
        $storeId = $this->isValidStore($input->getOption('store-id'));
        if (!$storeId) { // set to default store id
            $storeId = 1;
        }

        $csv      = array_map('str_getcsv', file($csvPath));
        $count    = count($csv) - 1; // do not count column title line
        $rowCount = 1;

        foreach ($csv as $key => $row) { // parse csv
            if ($rowCount == 1 || !$row[self::CSV_PRODUCT_POSITION]) { // skip the first and blank rows of csv
                $rowCount++;
                continue;
            }

            $csvProduct = $this->getProduct($row[self::CSV_PRODUCT_POSITION]);
            $csvReview  = $row[self::CSV_REVIEW_POSITION];
            $csvDate    = $this->formatDate($row[self::CSV_DATE_POSITION]);
            $csvName    = $row[self::CSV_NAME_POSITION];
            $csvTitle   = 'Reflection';
            $data       = [
                'nickname' => $csvName,
                'title'    => $csvTitle,
                'detail'   => $csvReview
            ];

            if ($product = $csvProduct['product']) { // attempt to save the review
                try {
                    /** @var Review $review */
                    $review = $this->reviewFactory->create();
                    $review->setData($data);

                    $validate = $review->validate();
                    if ($validate === true) {
                        $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                               ->setEntityPkValue($product->getId())
                               ->setStatusId(Review::STATUS_APPROVED)
                               ->setStoreId($storeId)
                               ->setStores([$storeId])
                               ->setCreatedAt($csvDate)
                               ->save();

                        /* must re-save the review with the since the create_at data is overwritten
                           when item is first saved */
                        $review->setCreatedAt($csvDate)->save();
                    }

                    $output->writeln("<info>{$key} of {$count} Review - {$product->getName()}: success</info>");
                } catch (\Exception $e) { // save failed log/output error
                    array_push($failureArray, $e->getMessage());
                    $this->logger->critical($e->getMessage());
                }
            } else { // product not loaded correctly: do not save review and output error
                array_push($failureArray, 'error: ' . $row[self::CSV_PRODUCT_POSITION] . ' ' . $csvProduct['message']);
                $output->writeln("<info>{$key} of {$count} Review - {$row[self::CSV_PRODUCT_POSITION]}: fail</info>");
                $this->logger->critical($row[self::CSV_PRODUCT_POSITION] . ' failed to save review: ' . $csvProduct['message']);
            }

            $rowCount++;
        }

        if (!empty($failureArray)) { // if we have any reflections that failed
            $output->writeln('<info></info>');
            $output->writeln('<info>Failed Items:</info>');

            foreach ($failureArray as $item) { // cycle through and output all the failed items
                $output->writeln("<info>{$item}</info>");
            }

            $output->writeln('<info>End Failed Items:</info>');
            $output->writeln('<info></info>');
        }

        $output->writeln('<info>Ending Import:</info>');

        return true;
    }


    /**
     * this will attempt to load the product and return a catalog product object.
     * @param $name
     * @return array
     */
    public function getProduct($name)
    {
        $product = null;

        /** @var \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('name', '%'.$name.'%', 'like')->create();
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $products */
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        if (count($products) == 1) { // only one product returned: success
            $product = reset($products);
            $message = $name . ' loaded correctly: success';
        } elseif (count($products) > 1) { // more than 1 product was returned: failure
            $message = $name . ' has multiple products: fail';
        } else { // no products were returned: failure
            $message = $name . ' has no matches: fail';
        }

        return [
            'product' => $product,
            'message' => $message
        ];
    }

    /**
     * this function will format the data with the proper format/timezone for the database.
     * @param $date
     * @return \DateTimeImmutable|false|string
     */
    protected function formatDate($date)
    {
        if ($date) { // format date
            try {
                $timeZone      = new \DateTimeZone('America/New_York');
                $formattedDate = new \DateTimeImmutable($date . ' noon', $timeZone);
                $formattedDate = $formattedDate->format('Y-m-d H:i:s');
            } catch (\Exception $e) { // invalid date format: set to current date
                $this->logger->critical($e->getMessage());
                $formattedDate = date("Y/m/d H:i:s");
            }
        } else { // date field empty: set current date
            $formattedDate = date("Y/m/d H:i:s");
        }

        return $formattedDate;
    }

    /**
     * this will check if the storeId argument is a valid store.
     * @param $storeId
     * @return bool
     */
    protected function isValidStore($storeId)
    {
        /**
         * @var array $storeArray
         * pattern: [[id] => [store object]]
         * the 0 store id (admin) will not exist in this array.
         */
        $storeArray = $this->storeManager->getStores();

        if (array_key_exists($storeId, $storeArray)) { // store exists
            return $storeId;
        } else { // no such store
            return false;
        }
    }
}
