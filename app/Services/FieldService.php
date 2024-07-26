<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Models\Field;
use App\User;
use App\CardField;

class FieldService
{
    /**
     * Validar si un grupo tiene campos generales.
     */
    public static function hasGroupWithGeneralFields(User $client, string $group): bool
    {
        foreach (CardField::TEMPLATE_FIELDS as $key => $fields_group) {
            if ($key == $group) {
                foreach ($fields_group['values'] as $value) {
                    $isFieldGeneral = FieldService::isFieldGeneral($client, $group, $value['key']);
                    if ($isFieldGeneral) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Validar si un grupo tiene campos específicos.
     */
    public static function hasGroupWithSpecificFields(User $client, string $group): bool
    {
        foreach (CardField::TEMPLATE_FIELDS as $key => $fields_group) {
            if ($key == $group) {
                foreach ($fields_group['values'] as $value) {
                    $isFieldSpecific = FieldService::isFieldSpecific($client, $group, $value['key']);
                    if ($isFieldSpecific) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private static function isFieldGeneralOrSpecific(User $client, String $groupKey, String $fieldKey, bool $expected): bool
    {
        $value = DB::table('field_scopes')
            ->where([
                'client_id' => $client->id,
                'field_group' => $groupKey,
                'field_key' => $fieldKey,
            ])
            ->first();

        if (!$value) {
            $groupValues = CardField::TEMPLATE_FIELDS[$groupKey]['values'];

            $field = Arr::first($groupValues, function ($x) use ($fieldKey) {
                return $x['key'] === $fieldKey;
            });

            return $field['general'] === $expected;
        }

        return (bool) $value->general === $expected;
    }

    public static function isFieldGeneral(User $client, String $groupKey, String $fieldKey): bool
    {
        return self::isFieldGeneralOrSpecific($client, $groupKey, $fieldKey, Field::GENERAL);
    }

    public static function isFieldSpecific(User $client, String $groupKey, String $fieldKey): bool
    {
        return self::isFieldGeneralOrSpecific($client, $groupKey, $fieldKey, Field::SPECIFIC);
    }
}
