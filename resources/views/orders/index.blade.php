@extends('layouts.app')

@section('content')
<div class="container">
    @include('helpers.flash-messages')
    <div class="row">
        <div class="col-6">
                <h1 style="color: #572c08;"><i class="fa-solid fa-bag-shopping"></i> Zamówienia</h1>
        </div>
    </div>

    <div class="row">
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Ilość</th>
                <th scope="col">Cena [PLN]</th>
                <th scope="col">Status zamówienia</th>
                <th scope="col">Produkty</th>
            </tr>
            </thead>
            <tbody>
            @foreach($orders as $order)
                <tr>
                    <th scope="row">{{ $order->id }}</th>
                    <td>{{ $order->quantity }}</td>
                    <td>{{ $order->price }}</td>
{{--                    <td>{{ $order->payment->status ?? 'Brak' }}</td>--}}
                    <td>{{ $order->payment->status ?? 'Zrealizowane' }}</td>
                    <td>
                        <ul>
                            @foreach($order->products as $product)
                                <li>{{ $product->name }} - {{ $product->description }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $orders->links() }}
    </div>
</div>
@endsection
