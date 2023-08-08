<?php

namespace App\Models;

use App\Enums\FieldType;

class TextareaField extends Field
{
    public $type = FieldType::TEXTAREA;
}
