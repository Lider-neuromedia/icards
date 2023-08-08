<?php

namespace App\Models;

use App\Enums\FieldType;

class GradientField extends Field
{
    public $type = FieldType::GRADIENT;
}
