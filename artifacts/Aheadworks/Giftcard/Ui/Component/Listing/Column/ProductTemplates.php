<?php
namespace Aheadworks\Giftcard\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class ProductTemplates extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Framework\Mail\Template\FactoryInterface
     */
    protected $emailTemplateFactory;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    protected $emailTemplateConfig;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Mail\Template\FactoryInterface $emailTemplateFactory
     * @param \Magento\Email\Model\Template\Config $emailTemplateConfig
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Mail\Template\FactoryInterface $emailTemplateFactory,
        \Magento\Email\Model\Template\Config $emailTemplateConfig,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->emailTemplateConfig = $emailTemplateConfig;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $product = new \Magento\Framework\DataObject($item);
                $templates = $product->getData($fieldName);
                if (is_array($templates)) {
                    $templateNames = [];
                    foreach ($templates as $template) {
                        $templateNames[] = $this->_getTemplateName($template['template']);
                    }
                    $item[$fieldName . '_names'] = $templateNames;
                }
            }
        }

        return $dataSource;
    }

    /**
     * Retrieves email template name using $templateId
     *
     * @param int|string $templateId
     * @return string
     */
    protected function _getTemplateName($templateId)
    {
        /** @var \Magento\Email\Model\Template $emailTemplate */
        $emailTemplate = $this->emailTemplateFactory->get($templateId);
        if (is_numeric($templateId)) {
            $emailTemplate->load($templateId);
        }
        return is_numeric($templateId) ?
            $emailTemplate->getTemplateCode() :
            $this->emailTemplateConfig->getTemplateLabel($templateId)->getText();
    }
}
