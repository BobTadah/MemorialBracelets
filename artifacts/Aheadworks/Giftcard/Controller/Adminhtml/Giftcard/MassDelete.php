<?php
namespace Aheadworks\Giftcard\Controller\Adminhtml\Giftcard;

class MassDelete extends \Aheadworks\Giftcard\Controller\Adminhtml\Giftcard\MassAbstract
{
    /**
     * @var string
     */
    protected $errorMessage = 'Something went wrong while deleting gift card code(s).';

    /**
     * @param \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Collection $collection
     */
    protected function massAction($collection)
    {
        $count = 0;
        foreach ($collection->getItems() as $giftCard) {
            $giftCard->delete();
            ++$count;
        }
        $this->messageManager->addSuccess(__('A total of %1 gift card(s) have been deleted.', $count));
    }
}
