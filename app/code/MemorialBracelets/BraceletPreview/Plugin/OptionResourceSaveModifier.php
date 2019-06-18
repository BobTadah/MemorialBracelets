<?php

namespace MemorialBracelets\BraceletPreview\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Option;
use Magento\Framework\Model\AbstractModel;

class OptionResourceSaveModifier
{
    const DATA_KEY = 'piece';

    // @codingStandardsIgnoreStart
    public function around_afterSave(Option $subject, callable $proceed, AbstractModel $object)
    {
        // @codingStandardsIgnoreEnd
        $result = $proceed($object);

        $this->saveValuePreviewPiece($subject, $object);

        return $result;
    }

    protected function saveValuePreviewPiece(Option $subject, AbstractModel $object)
    {
        $table = $subject->getTable('mb_preview_product_option_preview_piece');
        $connection = $subject->getConnection();

        $statement = $connection->select()->from($table)
            ->where('option_id = ?', $object->getId());

        $exists = $connection->fetchOne($statement);

        if ($object->getData(static::DATA_KEY) && $exists) {
            $connection->update(
                $table,
                ['piece' => $object->getData(static::DATA_KEY)],
                ['option_id = ?', $object->getId()]
            );
        } elseif ($object->getData(static::DATA_KEY)) {
            $connection->insert(
                $table,
                [
                    'option_id' => $object->getId(),
                    'piece' => $object->getData(static::DATA_KEY)
                ]
            );
        } elseif ($object->getId()) {
            $connection->delete($table, ['option_id = ?', $object->getId()]);
        }
    }
}
