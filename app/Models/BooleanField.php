<?php

namespace App\Models;

use App\Enums\FieldType;

class BooleanField extends Field
{
    public $type = FieldType::BOOLEAN;
}
