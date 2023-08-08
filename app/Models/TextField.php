<?php

namespace App\Models;

use App\Enums\FieldType;

class TextField extends Field
{
    public $type = FieldType::TEXT;
}
