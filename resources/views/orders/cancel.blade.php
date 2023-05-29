@extends('layouts.app')

@section('css-files')
    @vite('resources/css/cart.css')
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <div class="alert alert-success" role="alert">
                            Tansakcja nie została zrealizowana. Spróbuj ponownie.
                        </div>
                    </div>
                </div>

                <div class="cart_buttons">
                    <a href="/" class="button cart_button_clear">Wróć do sklepu</a>
                </div>

            </div>
        </div>
    </div>
@endsection
