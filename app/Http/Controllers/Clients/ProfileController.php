<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    public function index()
    {
        $client = \Auth::user();
        $subscription = $client->subscriptions()->first();
        return view('clients.profile.index', compact('client', 'subscription'));
    }

    public function store(ProfileRequest $request)
    {
        try {

            \DB::beginTransaction();

            $data = $request->only('name', 'email');

            if ($request->has('password') && $request->get('password')) {
                $data['password'] = \Hash::make($request->get('password'));
            }

            $client = \Auth::user();
            $client->update($data);

            \DB::commit();

            session()->flash('message', "Perfil actualizado correctamente.");
            return redirect()->action('Clients\ProfileController@index');

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            session()->flash('message-error', "Error interno al actualizar perfil.");
            return redirect()->back()->withInput($request->input());
        }
    }
}
