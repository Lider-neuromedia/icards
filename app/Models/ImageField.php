<?php

namespace App\Models;

use App\Enums\FieldType;

class ImageField extends Field
{
    public $type = FieldType::IMAGE;

    /**
     * Tamaño máximo de archivo.
     * @var int
     */
    public $max;

    public function __construct(string $key, string $label, int $max)
    {
        parent::__construct($key, $label);
        $this->max = $max;
    }
}
