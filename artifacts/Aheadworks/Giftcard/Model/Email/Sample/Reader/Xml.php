<?php
namespace Aheadworks\Giftcard\Model\Email\Sample\Reader;

/**
 * Class Xml
 * @package Aheadworks\Giftcard\Model\Email\Sample\Reader
 */
class Xml extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Aheadworks\Giftcard\Model\Email\Sample\Converter\Xml $converter
     * @param \Aheadworks\Giftcard\Model\Email\Sample\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Aheadworks\Giftcard\Model\Email\Sample\Converter\Xml $converter,
        \Aheadworks\Giftcard\Model\Email\Sample\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'sample_email_templates.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
