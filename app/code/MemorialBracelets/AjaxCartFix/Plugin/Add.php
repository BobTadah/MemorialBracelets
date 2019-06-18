<?php

namespace MemorialBracelets\AjaxCartFix\Plugin;

use Magento\Framework\Exception\LocalizedException;

class Add
{
    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */

    protected $messageManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Add constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->messageManager = $messageManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\Add $add
     * @param callable $proceed
     * @return array|void
     */
    public function aroundExecute(\Magento\Checkout\Controller\Cart\Add $add, callable $proceed)
    {
        $result = $proceed();
        // Interestingly, it almost seems to not matter if I get the messages or add them to the result correctly.
        // The simple fact of clearing the Response's faulty backurl keeps the page from reloading,
        // so the message system can proceed correctly
        if ($add->getRequest()->isAjax()) {
            $result = [];
            $test = $this->messageManager->getMessages();
            $messages = [];
            // I've invented this status parameter.  It should return either 'success' or 'error'
            // I added code in Magento_Catalog\web\js\catalog-add-to-cart.js to look for the status and
            // act accordingly
            $status = 'success';
            foreach ($this->messageManager->getMessages()->getItems() as $message) {
                array_push($messages, $message->getText());
                $status =  $message->getType();
            }
            $result['messages'] = $messages;
            $result['status'] = $status;

            $add->getResponse()->representJson(
                $this->objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
            return;
        }
        return $result;
    }
}
