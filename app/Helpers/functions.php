<?php

use App\Services\FieldService;
use App\Card;
use App\User;

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
    /**
     * TODO: No usar esta función.
     */
    function isUserAdmin()
    {
        if (!auth()->check()) {
            return false;
        }

        /** @var User */
        $authUser = auth()->user();
        return $authUser->isAdmin();
    }
}

if (!function_exists('isUserClient')) {
    function isUserClient()
    {
        if (!auth()->check()) {
            return false;
        }

        /** @var User */
        $authUser = auth()->user();
        return $authUser->isClient();
    }
}

if (!function_exists('hasGroupWithGeneralFields')) {
    function hasGroupWithGeneralFields(User $client, string $group): bool
    {
        return FieldService::hasGroupWithGeneralFields($client, $group);
    }
}

if (!function_exists('hasGroupWithSpecificFields')) {
    function hasGroupWithSpecificFields(User $client, string $group): bool
    {
        return FieldService::hasGroupWithSpecificFields($client, $group);
    }
}

if (!function_exists('isFieldGeneral')) {
    function isFieldGeneral(User $client, String $groupKey, String $fieldKey): bool
    {
        return FieldService::isFieldGeneral($client, $groupKey, $fieldKey);
    }
}

if (!function_exists('isFieldSpecific')) {
    function isFieldSpecific(User $client, String $groupKey, String $fieldKey): bool
    {
        return FieldService::isFieldSpecific($client, $groupKey, $fieldKey);
    }
}
