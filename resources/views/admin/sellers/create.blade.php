@extends('layouts.dashboard')

@section('title', 'Crear Vendedor')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('sellers.index') }}">Vendedores</a></li>
    <li class="breadcrumb-item active">Crear Vendedor</li>
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-8">

                <form action="{{ route('sellers.store', $seller) }}" method="post">
                    @csrf
                    @include('admin.sellers.form')
                </form>

            </div>
        </div>

    </div>
@endsection
