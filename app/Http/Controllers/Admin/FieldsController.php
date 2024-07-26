<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\FieldService;
use App\User;
use App\CardField;

class FieldsController extends Controller
{
    public function scopes(User $client)
    {
        $fields = CardField::TEMPLATE_FIELDS;
        $scopes = [];

        foreach ($fields as $groupKey => $group) {
            foreach ($group['values'] as $value) {
                $valueKey = $value['key'];
                $scopes["$groupKey.$valueKey"] = FieldService::isFieldGeneral($client, $groupKey, $valueKey);
            }
        }

        return view('admin.fields.scopes', compact('client', 'fields', 'scopes'));
    }

    public function storeScopes(Request $request, User $client)
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $fields = CardField::TEMPLATE_FIELDS;
        $scopes = [];
        $data = [];

        foreach ($fields as $groupKey => $group) {
            foreach ($group['values'] as $value) {
                $valueKey = $value['key'];
                $scopes[] = "$groupKey.$valueKey";
                $data["$groupKey.$valueKey"] = [
                    'field_group' => $groupKey,
                    'field_key' => $valueKey,
                    'general' => false,
                    'client_id' => $client->id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        $request->validate([
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*.key' => ['required', 'string', 'in:' . implode(",", $scopes)],
            'scopes.*.general' => ['nullable', 'boolean'],
        ]);

        try {

            DB::beginTransaction();

            foreach ($request->get('scopes') as $key => $scopeData) {
                $generalChecked = isset($scopeData['general']) && $scopeData['general'];

                if ($generalChecked) {
                    $data[$key]['general'] = $generalChecked;
                }
            }

            DB::table('field_scopes')
                ->where('client_id', $client->id)
                ->delete();
            DB::table('field_scopes')->insert($data);

            DB::commit();

            session()->flash('message', "Cambios guardados correctamente.");
            return redirect()->action('Admin\FieldsController@scopes', $client->id);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            Log::info($ex->getTraceAsString());
            DB::rollBack();

            session()->flash('message-error', "Error interno al guardar cambios.");
            return redirect()->back()->withInput($request->input());
        }
    }

    public function resetScopes(Request $request, User $client)
    {
        try {

            DB::beginTransaction();

            DB::table('field_scopes')
                ->where('client_id', $client->id)
                ->delete();

            DB::commit();

            session()->flash('message', "Cambios guardados correctamente.");
            return redirect()->action('Admin\FieldsController@scopes', $client->id);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            Log::info($ex->getTraceAsString());
            DB::rollBack();

            session()->flash('message-error', "Error interno al guardar cambios.");
            return redirect()->back()->withInput($request->input());
        }
    }
}
