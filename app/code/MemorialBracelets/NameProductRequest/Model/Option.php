<?php

namespace MemorialBracelets\NameProductRequest\Model;

class Option
{
    /** @var string */
    private $label;

    /** @var string */
    private $value;

    /**
     * @param string $label
     * @param string $value
     */
    public function __construct($label, $value = null)
    {
        $this->label = $label;
        $this->value = $value === null ? $label : $value;
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
     * @return Option
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Option
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
