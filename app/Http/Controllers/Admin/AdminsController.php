<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\User;
use Illuminate\Http\Request;

class AdminsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $users = User::query()
            ->whereRole(User::ROLE_ADMIN)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(20);

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create()
    {
        $roles = User::roles();
        $user = new User(['role' => User::ROLE_CLIENT]);
        return view('admin.users.create', compact('user', 'roles'));
    }

    public function store(AdminRequest $request)
    {
        return $this->saveOrUpdate($request);
    }

    public function show(User $user)
    {
        //
    }

    public function edit(User $user)
    {
        $roles = User::roles();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(AdminRequest $request, User $user)
    {
        return $this->saveOrUpdate($request, $user);
    }

    public function destroy(User $user)
    {
        if (\Auth::user()->id == $user->id) {
            session()->flash('message-error', "El registro no puede ser borrado.");
            return redirect()->back();
        }
        if (User::count() == 1) {
            session()->flash('message-error', "El sistema no puede quedar sin usuarios.");
            return redirect()->back();
        }

        $user->delete();
        session()->flash('message', "Registro borrado.");
        return redirect()->action('Admin\AdminsController@index');
    }

    private function saveOrUpdate(Request $request, User $user = null)
    {
        try {

            \DB::beginTransaction();

            $data = $request->only('name', 'email');
            $data['role'] = User::ROLE_ADMIN;

            if ($request->has('password') && $request->get('password')) {
                $data['password'] = \Hash::make($request->get('password'));
            }

            if ($user != null) {
                $user->update($data);
            } else {
                $user = User::create($data);
                $user->save();
            }

            \DB::commit();

            session()->flash('message', "Registro guardado correctamente.");
            return redirect()->action('Admin\AdminsController@edit', $user->id);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            session()->flash('message-error', "Error interno al guardar registro.");
            return redirect()->back()->withInput($request->input());
        }
    }
}
