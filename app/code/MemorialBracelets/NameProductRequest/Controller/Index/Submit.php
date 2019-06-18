<?php

namespace MemorialBracelets\NameProductRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;
use MemorialBracelets\NameProductRequest\Helper\Email;
use MemorialBracelets\NameProductRequest\Helper\ProductCreator;
use MemorialBracelets\NameProductRequest\Helper\StockSetter;
use Psr\Log\LoggerInterface;

class Submit extends Action
{
    /** @var string[] */
    private $requiredFields = ['firstname', 'lastname'];

    /** @var ProductCreator */
    private $productCreator;

    /** @var StockSetter */
    private $stockSetter;

    /** @var Email */
    private $mailer;

    /** @var LoggerInterface */
    private $log;

    /**
     * @param Context $context
     * @param ProductCreator $productCreator
     * @param StockSetter $stockSetter
     * @param Email $mailer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ProductCreator $productCreator,
        StockSetter $stockSetter,
        Email $mailer,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->productCreator = $productCreator;
        $this->stockSetter = $stockSetter;
        $this->mailer = $mailer;
        $this->log = $logger;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();

        if ($request instanceof Http) {
            $data = $request->getPostValue();
        } else {
            $data = $request->getParams();
        }

        foreach ($this->requiredFields as $requiredField) {
            if (!isset($data[$requiredField])) {
                return $this->missingRequiredField();
            }
        }

        $nameParts = [$data['prefix'], $data['firstname'], $data['middle_initial'], $data['lastname'], $data['suffix']];
        $nameParts = array_filter(
            $nameParts,
            function ($piece) {
                return !empty($piece);
            }
        );
        $name = implode(' ', $nameParts);

        try {
            $product = $this->productCreator->create();
        } catch (\Exception $e) {
            $this->log->critical($e);
            return $this->unknownError();
        }
        
        
        $product->setName($name)
            ->setCustomAttribute('name_prefix', $data['prefix'])
            ->setCustomAttribute('first_name', $data['firstname'])
            ->setCustomAttribute('middle_name', $data['middle_initial'])
            ->setCustomAttribute('last_name', $data['lastname'])
            ->setCustomAttribute('name_suffix', $data['suffix'])
            ->setCustomAttribute('age', $data['age'])
            ->setCustomAttribute('city', $data['city'])
            ->setCustomAttribute('state', $data['region'])
            ->setCustomAttribute('country', $data['country'])
            ->setCustomAttribute('special_request_affiliation', $data['special_request_affiliation'])
            ->setCustomAttribute('date', $data['incident_date'])
            ->setCustomAttribute('incident_country', $data['incident_country'])
            ->setCustomAttribute('special_engraving', implode("\r\n", $data['special_engraving']))
            ->setCustomAttribute('special_request_event', $data['special_request_event'])
            ->setCustomAttribute('special_request_status', $data['special_request_status'])
        ;

        $product = $this->productCreator->setUrlKey($product);

        try {
            $product = $this->productCreator->save($product);
        } catch (\Exception $e) {
            $this->log->critical($e);
            return $this->unknownError();
        }

        try {
            $this->stockSetter->setupForProduct($product);
        } catch (\Exception $e) {
            $this->log->critical($e);
        }

        if ($this->mailer->shouldSend()) {
            try {
                $email = $this->mailer->createMessage($product);
                $this->mailer->send($email);
            } catch (\Exception $e) {
                $this->log->critical('Error sending product request email:');
                $this->log->critical($e);
            }
        } else {
            $this->log->notice('Name Product Request email not attempted for product ' . $product->getSku());
        }

        return $this->_redirect('request/index/success');
    }

    /**
     * What to do when there's an error with the form.
     * @return ResponseInterface
     */
    public function missingRequiredField()
    {
        $this->messageManager->addErrorMessage(__('Please ensure all required fields are filled out'));
        return $this->_redirect('request');
    }

    /**
     * What to do when there's an error we can't show the user
     * @return ResponseInterface
     */
    public function unknownError()
    {
        $this->messageManager->addErrorMessage(__('There was an error saving your request.  Please try again.'));
        return $this->_redirect('request');
    }
}
