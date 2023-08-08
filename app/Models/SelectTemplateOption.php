<?php

namespace App\Models;

class SelectTemplateOption extends SelectOption
{
    /**
     * @var string
     */
    public $stylesPath;

    /**
     * @var string
     */
    public $templatePath;

    public function __construct(string $id, string $name, string $stylesPath, string $templatePath)
    {
        parent::__construct($id, $name);
        $this->stylesPath = $stylesPath;
        $this->templatePath = $templatePath;
    }
}
