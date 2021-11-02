<?php

namespace App\Services;

class SlugService
{
    public static function generate($name, $table, $id = null)
    {
        $slug = '';
        $n = 0;

        do {
            if ($n === 0) {
                $slug = \Str::slug($name);
            } else {
                $slug = \Str::slug($name . " $n");
            }
            $n++;

            $exists = \DB::table($table)
                ->where('slug', $slug)
                ->when($id != null, function ($q) use ($id) {
                    $q->where('id', '!=', $id);
                })
                ->exists();
        } while ($exists);

        return $slug;
    }
}
