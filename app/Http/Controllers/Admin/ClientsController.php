<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Subscription;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $clients = User::query()
            ->whereRole(User::ROLE_CLIENT)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(12);

        return view('admin.clients.index', compact('clients', 'search'));
    }

    public function create()
    {
        $client = new User(['role' => User::ROLE_CLIENT]);
        $subscription = new Subscription([
            'start_at' => Carbon::now(),
            'finish_at' => Carbon::now()->add('years', 1),
            'cards' => 1,
        ]);
        return view('admin.clients.create', compact('client', 'subscription'));
    }

    public function store(ClientRequest $request)
    {
        return $this->saveOrUpdate($request);
    }

    public function show(User $client)
    {
        //
    }

    public function edit(User $client)
    {
        $subscription = $client->subscriptions()->first();

        if (!$subscription) {
            $subscription = new Subscription([
                'start_at' => Carbon::now(),
                'finish_at' => Carbon::now()->add('years', 1),
                'cards' => 1,
            ]);
        }

        return view('admin.clients.edit', compact('client', 'subscription'));
    }

    public function update(ClientRequest $request, User $client)
    {
        return $this->saveOrUpdate($request, $client);
    }

    public function destroy(User $client)
    {
        $client->delete();
        session()->flash('message', "Registro borrado.");
        return redirect()->action('Admin\ClientsController@index');
    }

    private function saveOrUpdate(Request $request, User $client = null)
    {
        try {

            \DB::beginTransaction();

            $data = $request->only('name', 'email');
            $data['role'] = User::ROLE_CLIENT;

            if ($request->has('password') && $request->get('password')) {
                $data['password'] = \Hash::make($request->get('password'));
            }

            if ($client != null) {
                $client->update($data);
            } else {
                $client = User::create($data);
                $client->save();
            }

            $client->subscriptions()->delete();
            $subscription = new Subscription($request->only('cards', 'start_at', 'finish_at'));
            $subscription->client()->associate($client);
            $subscription->save();

            \DB::commit();

            session()->flash('message', "Registro guardado correctamente.");
            return redirect()->action('Admin\ClientsController@edit', $client->id);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            session()->flash('message-error', "Error interno al guardar registro.");
            return redirect()->back()->withInput($request->input());
        }
    }
}
