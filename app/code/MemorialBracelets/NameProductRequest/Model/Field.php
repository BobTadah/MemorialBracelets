<?php

namespace MemorialBracelets\NameProductRequest\Model;

class Field
{
    const TYPE_TEXT = 'text';
    const TYPE_MULTILINE = 'multiline';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT = 'select';

    /** @var string  */
    private $type;

    /** @var string  */
    private $label;

    /** @var bool  */
    private $required = false;

    /** @var string  */
    private $name;

    /** @var int */
    private $maxLength;

    /** @var Option[] */
    private $options = [];

    /** @var int  */
    private $lines = 1;

    /**
     * @param string $type
     * @param string $label
     * @param array $data
     */
    public function __construct($type, $label, $data = [])
    {
        $this->type = $type;
        $this->label = $label;

        if (isset($data['required'])) {
            $this->required = !!$data['required'];
        }

        if (isset($data['options']) && is_array($data['options'])) {
            $this->options = $data['options'];
        }

        if (isset($data['name']) && $data['name']) {
            $this->name = $data['name'];
        } else {
            $this->name = strtolower(str_replace(' ', '_', $label));
        }

        if (isset($data['maxlength'])) {
            $this->maxLength = intval($data['maxlength'], 10);
        }

        if (isset($data['lines'])) {
            $this->lines = intval($data['lines'], 10);
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Field
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Field
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     * @return Field
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param Option[] $options
     * @return Field
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @param int $maxLength
     * @return Field
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * @return int
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * @param int $lines
     * @return Field
     */
    public function setLines($lines)
    {
        $this->lines = $lines;
        return $this;
    }
}
