<?php
namespace MemorialBracelets\CartMessage\Plugin;

/**
 * Class MessageUpdate
 * @package MemorialBracelets\CartMessage\Plugin
 */
class MessageUpdate
{
    /**
     * @param $subject
     * @param $message
     * @return mixed
     */
    public function beforeAddSuccessMessage($subject, $message)
    {
        if ($message instanceof \Magento\Framework\Phrase) {
            if (strpos($message->getText(), 'You added %1 to your shopping cart.') !== false) { // check for cart phrase.
                $newMessage = $message->getText() . ' Modify your selections to add another %1 to the cart.';
                $product    = $message->getArguments();
                $product    = reset($product);
                $newMessage = str_replace('%1', $product, $newMessage);
                return [$newMessage, null];
            } else {
                return [$message, null];
            }
        } else {
            return [$message, null];
        }
    }
}
