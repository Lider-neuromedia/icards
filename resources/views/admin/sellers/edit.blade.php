@extends('layouts.dashboard')

@section('title', 'Editar Vendedor')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('sellers.index') }}">Vendedores</a></li>
    <li class="breadcrumb-item active">Editar Vendedor</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-5">

                {{-- Formulario de editar --}}

                <form action="{{ route('sellers.update', $seller) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="id" value="{{ $seller->id }}">
                    @include('admin.sellers.form')
                </form>

                {{-- Formulario de borrar --}}

                @include('partials.delete', [
                    'id_form' => 'delete-seller-form',
                    'label' => 'Borrar Vendedor',
                    'route' => route('sellers.destroy', $seller->id),
                ])

            </div>

            <div class="col-12 col-md-5">

                <table class="table table-bordered text-nowrap bg-white">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">Clientes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($seller->clients()->count() == 0)
                            <tr>
                                <td class="text-center">No tiene clientes</td>
                            </tr>
                        @endif

                        @foreach ($seller->clients as $client)
                            <tr>
                                <td class="text-center">
                                    <a target="_blank"
                                        href="{{ url('/admin/clients') }}?search={{ $client->name }}">{{ $client->name }}</a>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>


            </div>
        </div>

    </div>
@endsection
