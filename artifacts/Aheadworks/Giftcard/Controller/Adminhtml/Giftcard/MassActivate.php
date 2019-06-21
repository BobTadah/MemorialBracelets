<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

/**
 * Class MassActivate
 * @package Aheadworks\Giftcard\Controller\Adminhtml\Giftcard
 */
class MassActivate extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard\MassAbstract
{
    /**
     * @var string
     */
    protected $errorMessage = 'Something went wrong while activating gift card code(s).';

    /**
     * @param \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Collection $collection
     */
    protected function massAction($collection)
    {
        $count = 0;
        /** @var \Aheadworks\Giftcard\Model\Giftcard $giftCard */
        foreach ($collection->getItems() as $giftCard) {
            try {
                $this->giftCardManager->activate($giftCard);
                ++$count;
            } catch (\Exception $e) {

            }
        }
        $this->messageManager->addSuccess(__('A total of %1 gift card code(s) have been activated.', $count));
    }
}
