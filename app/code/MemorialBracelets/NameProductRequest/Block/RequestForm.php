<?php

namespace MemorialBracelets\NameProductRequest\Block;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\View\Element\Template;
use MemorialBracelets\NameProductRequest\Model\Field;
use MemorialBracelets\NameProductRequest\Model\FieldFactory;
use MemorialBracelets\NameProductRequest\Model\Option;
use MemorialBracelets\NameProductRequest\Model\OptionFactory;

class RequestForm extends Template
{
    /** @var FieldFactory */
    private $fieldFactory;

    /** @var OptionFactory */
    private $optionFactory;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @param Template\Context $context
     * @param FieldFactory $fieldFactory
     * @param OptionFactory $optionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        FieldFactory $fieldFactory,
        OptionFactory $optionFactory,
        AttributeRepositoryInterface $attributeRepository,
        array $data = []
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->optionFactory = $optionFactory;
        $this->attributeRepository = $attributeRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return [
            $this->text('Prefix/Rank', 'prefix'),
            $this->text('First Name', 'firstname')->setRequired(true),
            $this->text('Middle Initial'),
            $this->text('Last Name', 'lastname')->setRequired(true),
            $this->text('Suffix'),
            $this->text('Age')->setRequired(true),
            $this->text('Home Town', 'city')->setRequired(true),
            $this->select('Home State/Province', 'region')->setRequired(true)
                ->setOptions($this->productAttributeToOptions('state')),
            $this->select('Home Country', 'country')->setRequired(true)
                ->setOptions($this->productAttributeToOptions('country')),
            $this->text('Affiliation', 'special_request_affiliation'),
            $this->date('Incident Date', 'incident_date')->setRequired(true),
            $this->text('Incident Country', 'incident_country')->setRequired(true),
            $this->text('Event', 'special_request_event')->setRequired(true),
            $this->text('Status', 'special_request_status')->setRequired(true),
            $this->multiline('Custom Engraving', 3, 'special_engraving')->setMaxLength(30)
                ->setRequired(true),
        ];
    }

    /**
     * @param string $label
     * @param string|null $name
     * @return Field
     */
    private function text($label, $name = null)
    {
        return $this->fieldFactory->create(
            ['type' => Field::TYPE_TEXT, 'label' => $label, 'data' => ['name' => $name]]
        );
    }

    /**
     * @param      $label
     * @param null $name
     * @return Field
     */
    private function date($label, $name = null)
    {
        return $this->fieldFactory->create(
            ['type' => 'date', 'label' => $label, 'data' => ['name' => $name]]
        );
    }

    /**
     * @param string $label
     * @param int $lines
     * @param string|null $name
     * @return Field
     */
    private function multiline($label, $lines = 2, $name = null)
    {
        return $this->fieldFactory->create(
            ['type' => Field::TYPE_MULTILINE, 'label' => $label, 'data' => ['name' => $name, 'lines' => $lines]]
        );
    }

    /**
     * @param string $label
     * @param string|null $name
     * @return Field
     */
    private function textarea($label, $name = null)
    {
        return $this->fieldFactory->create(
            ['type' => Field::TYPE_TEXTAREA, 'label' => $label, 'data' => ['name' => $name]]
        );
    }

    /**
     * @param string $label
     * @param string null $name
     * @return Field
     */
    private function select($label, $name = null)
    {
        return $this->fieldFactory->create(
            ['type' => Field::TYPE_SELECT, 'label' => $label, 'data' => ['name' => $name]]
        );
    }

    /**
     *
     * @param string $label
     * @param string|null $value
     * @return mixed
     */
    private function option($label, $value = null)
    {
        return $this->optionFactory->create(['label' => $label, 'value' => $value]);
    }

    /**
     * @param string $attributeCode
     * @return Option[]
     */
    private function productAttributeToOptions($attributeCode)
    {
        $attributeOptions = $this->attributeRepository
            ->get(Product::ENTITY, $attributeCode)
            ->getOptions();

        usort(
            $attributeOptions,
            function (AttributeOptionInterface $optionA, AttributeOptionInterface $optionB) {
                // Future refactor, all of this to: $optionA->getSortOrder() <=> $optionB->getSortOrder()
                $sortA = $optionA->getLabel();
                $sortB = $optionB->getLabel();
                switch (true) {
                    default:
                    case $sortA == $sortB:
                        return 0;
                    case $sortA < $sortB:
                        return -1;
                    case $sortA > $sortB:
                        return 1;
                }
            }
        );

        $attributeOptions = array_filter(
            $attributeOptions,
            function (AttributeOptionInterface $option) {
                $value = $option->getValue();
                return !empty($value);
            }
        );

        return array_map(
            function (AttributeOptionInterface $option) {
                return $this->option($option->getLabel(), $option->getValue());
            },
            $attributeOptions
        );
    }
}
