<?php

namespace App\Enums;

abstract class EnumClass
{
    protected $formats = [];

    protected $colors = [];

    /**
     * Obtener el listado de constantes.
     */
    public static function constants()
    {
        $ignoreTypes = ['array'];

        $oClass = new \ReflectionClass(static::class);
        $constants = collect($oClass->getConstants())
            ->filter(function ($x) use ($ignoreTypes) {
                return !in_array(gettype($x), $ignoreTypes);
            })
            ->toArray();

        return $constants;
    }

    /**
     * Obtener solo los valores de las constantes.
     */
    public static function values()
    {
        return array_values(self::constants());
    }

    /**
     * Obtener valores en linea.
     *
     * Ej: admin,employee,client
     */
    public static function inlineValues(array $only = [])
    {
        $values = self::values();

        if (count($only) > 0) {
            $values = collect($values)
                ->filter(function ($x) use ($only) {
                    return in_array($x, $only);
                })
                ->toArray();
        }

        return implode(",", $values);
    }

    /**
     * Formatear valores.
     *
     * SUPER_ADMIN ==> Super Admin
     */
    public function format(String $value)
    {
        if (isset($this->formats[$value])) {
            return __($this->formats[$value]);
        }

        $constants = self::constants();

        foreach ($constants as $lConstant => $lValue) {
            if ($value == $lValue) {
                $temp = ucwords(strtolower(str_replace("_", " ", $lConstant)));
                return __($temp);
            }
        }

        return null;
    }

    /**
     * Obtener listado clave valor.
     */
    public function listing()
    {
        $list = [];

        foreach (self::constants() as $item) {
            $list[$item] = $this->format($item);
        }

        return $list;
    }

    /**
     * Obtener listado en formato apto para input select.
     */
    public function selectListing(array $only = [])
    {
        $list = [];

        foreach ($this->listing() as $key => $value) {
            if (count($only) > 0) {
                if (!in_array($key, $only)) {
                    continue;
                }
            }

            $list[] = (object) [
                'id' => $key,
                'name' => $value,
            ];
        }

        return $list;
    }

    public function color(string $key)
    {
        return $this->colors[$key];
    }
}
