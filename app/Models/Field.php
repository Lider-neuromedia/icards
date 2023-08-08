<?php

namespace App\Models;

abstract class Field
{
    public const GENERAL = true;
    public const SPECIFIC = false;
    public const WATCHABLE = false;

    /**
     * Tipo de campo de plantilla.
     * @var string
     */
    public $type = null;

    /**
     * Llave única para identificar campo.
     * @var string
     */
    public $key;

    /**
     * Descripción del campo.
     * @var string
     */
    public $label;

    /**
     * Mensaje de ayuda.
     * @var string
     */
    public $help;

    /**
     * Si el campo se aplica de forma general a todas las tarjetas o de forma individual.
     * @var bool
     */
    public $general;

    /**
     * @var bool
     */
    public $watch;

    /**
     * Validar si el campo es visible dependiendo del valor de otro campo.
     * @var string|null
     */
    public $visible_when;

    /**
     * Valor por defecto del campo.
     * @var mixed|null
     */
    public $default;

    /**
     * Valor de ejeemplo.
     * @var mixed|null
     */
    public $example;

    /**
     * Reglas de validación en formularios.
     * @var array
     */
    public $rules;

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
        $this->general = Field::GENERAL;
        $this->help = "";
        $this->visible_when = null;
        $this->watch = false;
        $this->default = null;
        $this->example = null;
        $this->rules = [];
    }

    public function helpText(string $text): Field
    {
        $this->help = $text;
        return $this;
    }

    public function default($value): Field
    {
        $this->default = $value;
        return $this;
    }

    public function example($value): Field
    {
        $this->example = $value;
        return $this;
    }

    public function general(): Field
    {
        $this->general = Field::GENERAL;
        return $this;
    }

    public function specific(): Field
    {
        $this->general = Field::SPECIFIC;
        return $this;
    }

    public function watchable(): Field
    {
        $this->watch = FIELD::WATCHABLE;
        return $this;
    }

    public function visibleWhen(string $field_key, $value): Field
    {
        $this->visible_when = "$field_key:$value";
        return $this;
    }

    public function rules(array $rules): Field
    {
        $this->rules = $rules;
        return $this;
    }
}
