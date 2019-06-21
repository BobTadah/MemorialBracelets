<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

/**
 * Class MassDeactivate
 * @package Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
 */
class MassDeactivate extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard\MassAbstract
{
    /**
     * @var string
     */
    protected $errorMessage = 'Something went wrong while deactivating gift card code(s).';

    /**
     * @param $collection
     */
    protected function massAction($collection)
    {
        $count = 0;
        /** @var \Aheadworks\Giftcard\Model\Giftcard $giftCard */
        foreach ($collection->getItems() as $giftCard) {
            try {
                $this->giftCardManager->deactivate($giftCard);
                ++$count;
            } catch (\Exception $e) {

            }
        }
        $this->messageManager->addSuccess(__('A total of %1 gift card code(s) have been deactivated.', $count));
    }
}
