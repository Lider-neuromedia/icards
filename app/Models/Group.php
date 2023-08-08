<?php

namespace App\Models;

class Group
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var Field[]
     */
    public $values;

    /**
     * @param string $label
     * @param Field[] $values
     */
    public function __construct(string $label, array $values)
    {
        $this->label = $label;
        $this->values = $values;
    }
}
