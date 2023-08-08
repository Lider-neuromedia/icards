<?php

namespace App\Models;

use App\Enums\FieldType;

class SelectField extends Field
{
    public $type = FieldType::SELECT;

    /**
     * @var SelectOption[]
     */
    public $options = [];

    /**
     * @param string $key
     * @param string $label
     * @param SelectOption[] $options
     */
    public function __construct(string $key, string $label, array $options)
    {
        parent::__construct($key, $label);
        $this->options = $options;
    }
}
