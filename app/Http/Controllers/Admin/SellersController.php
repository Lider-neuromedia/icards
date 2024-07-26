<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SellerRequest;
use App\Seller;

class SellersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sellers = Seller::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            })
            ->orderBy('name', 'asc')
            ->paginate(20);

        return view('admin.sellers.index', compact('sellers', 'search'));
    }

    public function create()
    {
        $seller = new Seller();
        return view('admin.sellers.create', compact('seller'));
    }

    public function store(SellerRequest $request)
    {
        return $this->saveOrUpdate($request);
    }

    public function show(Seller $seller)
    {
        //
    }

    public function edit(Seller $seller)
    {
        return view('admin.sellers.edit', compact('seller'));
    }

    public function update(SellerRequest $request, Seller $seller)
    {
        return $this->saveOrUpdate($request, $seller);
    }

    public function destroy(Seller $seller)
    {
        $seller->delete();
        session()->flash('message', "Registro borrado.");
        return redirect()->action('Admin\SellersController@index');
    }

    private function saveOrUpdate(Request $request, Seller $seller = null)
    {
        try {

            DB::beginTransaction();

            $data = $request->only('name');

            if ($seller != null) {
                $seller->update($data);
            } else {
                $seller = Seller::create($data);
            }

            DB::commit();

            session()->flash('message', "Registro guardado correctamente.");
            return redirect()->action('Admin\SellersController@edit', $seller->id);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            Log::info($ex->getTraceAsString());
            DB::rollBack();

            session()->flash('message-error', "Error interno al guardar registro.");
            return redirect()->back()->withInput($request->input());
        }
    }
}
