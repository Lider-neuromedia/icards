<?php

use App\Card;

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

if (!function_exists('isUserAdmin')) {
    function isUserAdmin()
    {
        if (!auth()->check()) {
            return false;
        }
        return auth()->user()->isAdmin();
    }
}

if (!function_exists('isUserClient')) {
    function isUserClient()
    {
        if (!auth()->check()) {
            return false;
        }
        return auth()->user()->isClient();
    }
}
