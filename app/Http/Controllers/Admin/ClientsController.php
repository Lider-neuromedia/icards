<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Mail\AccountCreated;
use App\Seller;
use App\Subscription;
use App\User;

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
            ->with('allowedAccounts')
            ->orderBy('name', 'asc')
            ->paginate(20);

        return view('admin.clients.index', compact('clients', 'search'));
    }

    public function create()
    {
        $sellers = Seller::orderBy('name', 'asc')->get();
        $client = new User(['role' => User::ROLE_CLIENT]);

        $subscription = new Subscription([
            'start_at' => now(),
            'finish_at' => now()->add('years', 1),
            'cards' => 1,
        ]);

        $allowedAccounts = [];
        $accounts = User::query()
            ->select('id', 'name', 'email')
            ->where('role', User::ROLE_CLIENT)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($x) {
                $x->enabled = false;
                $x->setHidden(['seller_name']);
                return $x;
            });

        return view('admin.clients.create', compact('client', 'subscription', 'sellers', 'accounts', 'allowedAccounts'));
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
        $sellers = Seller::orderBy('name', 'asc')->get();
        $subscription = $client->subscriptions()->first();

        if (!$subscription) {
            $subscription = new Subscription([
                'start_at' => now(),
                'finish_at' => now()->add('years', 1),
                'cards' => 1,
            ]);
        }

        $allowedAccounts = $client->allowedAccounts()->get()->pluck('id')->toArray();
        $accounts = User::query()
            ->select('id', 'name', 'email')
            ->where('role', User::ROLE_CLIENT)
            ->where('id', '!=', $client->id)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($x) use ($allowedAccounts) {
                $x->enabled = false;
                $x->setHidden(['seller_name']);
                return $x;
            });

        return view('admin.clients.edit', compact('client', 'subscription', 'sellers', 'accounts', 'allowedAccounts'));
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

            DB::beginTransaction();

            $data = $request->only('name', 'email');
            $data['role'] = User::ROLE_CLIENT;
            $send_mail = false;

            if ($request->has('password') && $request->get('password')) {
                $data['password'] = Hash::make($request->get('password'));
            }

            $client_id = $client != null ? $client->id : null;
            $slug = \App\Services\SlugService::generate($data['name'], 'users', $client_id);
            $data['slug'] = $slug;

            if ($client != null) {
                $client->update($data);
            } else {
                $client = User::create($data);
                $client->save();
                $send_mail = true;
            }

            // Actualizar suscripción.
            $subscription_data = $request->only('cards', 'start_at', 'finish_at');
            $subscription_data['start_at'] = Carbon::createFromFormat('Y-m-d\TH:i', $subscription_data['start_at']);
            $subscription_data['finish_at'] = Carbon::createFromFormat('Y-m-d\TH:i', $subscription_data['finish_at']);

            $client->subscriptions()->delete();
            $subscription = new Subscription($subscription_data);
            $subscription->client()->associate($client);
            $subscription->save();

            $this->deleteClientExtraCards($client);

            // Habilitar/Deshabilitar cuentas.
            $allowed_accounts = $request->get('allowed_accounts');
            if ($allowed_accounts) {
                $client->allowedAccounts()->sync($allowed_accounts);
            } else {
                $client->allowedAccounts()->detach();
            }

            // Asignar vendedor
            $seller = Seller::findOrFail($request->get('seller_id'));
            $client->sellers()->sync($seller);

            // Notificar cliente por correo.
            if ($send_mail) {
                $credentials = $request->only('email', 'password');
                Mail::to($client)->send(new AccountCreated($client, $credentials));
            }

            DB::commit();

            session()->flash('message', "Registro guardado correctamente.");
            return redirect()->action('Admin\ClientsController@edit', $client->id);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            Log::info($ex->getTraceAsString());
            DB::rollBack();

            session()->flash('message-error', "Error interno al guardar registro.");
            return redirect()->back()->withInput($request->input());
        }
    }

    /**
     * Borrar las tarjetas que sobrepasen el límite de la subscripción.
     */
    private function deleteClientExtraCards(User $client)
    {
        $max = $client->subscriptions()->first()->cards;
        $cards_count = $client->cards()->count();

        if ($max < $cards_count) {
            do {

                $client->cards()->orderBy('created_at', 'desc')->first()->delete();
            } while ($client->cards()->count() > $max);
        }
    }
}
