<?php

namespace MemorialBracelets\NameProductRequest\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\TransportInterfaceFactory;

class Email
{
    /** @var TransportInterfaceFactory  */
    private $transportFactory;

    /** @var MessageInterfaceFactory  */
    private $messageFactory;

    /** @var Config  */
    private $config;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @param TransportInterfaceFactory $transportFactory
     * @param MessageInterfaceFactory $messageFactory
     * @param Config $config
     * @param UrlInterface $backendUrl
     */
    public function __construct(
        TransportInterfaceFactory $transportFactory,
        MessageInterfaceFactory $messageFactory,
        Config $config,
        UrlInterface $backendUrl
    ) {
        $this->transportFactory = $transportFactory;
        $this->messageFactory = $messageFactory;
        $this->config = $config;
        $this->backendUrl = $backendUrl;
    }

    /**
     * Sends a MessageInterface
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message)
    {
        $transport = $this->transportFactory->create(['message' => $message]);
        $transport->sendMessage();
    }

    /**
     * Whether or not we should try to send an email
     * @return bool
     */
    public function shouldSend()
    {
        $sendTo = $this->config->getEmailTo();
        $from = $this->config->getEmailFrom();
        return !empty($sendTo) && !empty($from);
    }

    /**
     * Creates a standard Name Product Request email message for a given product
     * @param ProductInterface $product
     * @return MessageInterface
     */
    public function createMessage(ProductInterface $product)
    {
        /** Link to product. */
        $params = [
            'id' => $product->getId(),
            '_cache_secret_key' => true
        ];
        $url = $this->backendUrl->getUrl('catalog/product/edit', $params);

        /** @var MessageInterface $message */
        $message = $this->messageFactory->create();

        $message->addTo($this->config->getEmailTo());
        $message->setFrom($this->config->getEmailFrom());
        $message->setSubject('New Name Product Request');
        $message->setMessageType(MessageInterface::TYPE_HTML);

        $body = <<<HTML
<p>A new name product request has been submitted for <strong>{$product->getName()}</strong>.</p>
<p>Use the SKU <strong>{$product->getSku()}</strong> to identify it uniquely in the admin panel. Or if you are already logged into the admin panel, just click <a href="{$url}">here</a>.</p>
<p>Please review this product at your earliest convenience.</p>
HTML;
        $message->setBody($body);
        $message->addCc($this->config->getEmailCc());

        return $message;
    }
}
