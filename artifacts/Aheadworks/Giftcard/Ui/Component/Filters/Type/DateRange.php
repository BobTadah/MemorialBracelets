<?php
namespace Aheadworks\Giftcard\Ui\Component\Filters\Type;

/**
 * Class DateRange
 */
class DateRange extends \Magento\Ui\Component\Filters\Type\DateRange
{
    /**
     * Apply filter by its type
     *
     * @param string $type
     * @param string $value
     * @return void
     */
    protected function applyFilterByType($type, $value)
    {
        if (!empty($value)) {
            $value = $this->wrappedComponent->convertDate($value);
            if ($type == 'lteq') {
                $value->add(new \DateInterval('PT23H59M59S'));
            }

            $filter = $this->filterBuilder->setConditionType($type)
                ->setField($this->getName())
                ->setValue($value->format('Y-m-d H:i:s'))
                ->create();

            $this->getContext()->getDataProvider()->addFilter($filter);
        }
    }
}
