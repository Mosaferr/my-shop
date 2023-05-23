<?php

namespace App\Http\Controllers;

use App\ValueObjects\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Order;

class StripeController extends Controller
{
    public function success(Request $request)
    {
        $orderId = Session::get('order_id');
        $order = Order::findOrFail($orderId);
        $payment = new Payment([
            'status' => PaymentStatus::SUCCESS,
            'session_id' => $request->session_id,
            'order_id' => $order->id,
        ]);
        $payment->save();
        Session::put('cart', new Cart());
        return redirect(route('cart.index'))->with('status', __('shop.product.status.sold.success'));
    }

    public function cancel(Request $request)
    {
        $orderId = Session::get('order_id');
        $order = Order::findOrFail($orderId);
        $payment = new Payment([
            'status' => PaymentStatus::FAIL,
            'error_code' => $request->error_code,
            'error_description' => $request->error_description,
            'session_id' => $request->session_id,
            'order_id' => $order->id,
        ]);
        $payment->save();
        return view('stripe.cancel');
    }
}
