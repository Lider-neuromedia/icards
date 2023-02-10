<?php

use App\Card;
use \Parsedown;

if (!function_exists('cardValue')) {
    function cardValue(Card $card, $group, $key)
    {
        $value = $card->field($group, $key, true);
        return $value;
    }
}

if (!function_exists('markdown')) {
    function markdown($content)
    {
        $parsedown = new Parsedown();
        $parsedown->setBreaksEnabled(true);
        $parsedown->setSafeMode(true);
        return $parsedown->text($content);
    }
}
