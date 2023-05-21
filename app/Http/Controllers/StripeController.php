<?php

namespace App\Http\Controllers;

use App\ValueObjects\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Order;
use Stripe\Stripe;
class StripeController extends Controller
{
    public function session()
    {
        $cart = Session::get('cart', new Cart());
        if ($cart->hasItems()) {
            $order = new Order();
            $order->quantity = $cart->getQuantity();
            $order->price = $cart->getSum();
            $order->user_id = Auth::id();
            $order->save();

            $productIds = $cart->getItems()->map(function ($item) {
                return ['product_id' => $item->getProductId()];
            });
            $order->products()->attach($productIds);
            // return $this->paymentTransaction($order);
        }

        Stripe::setApiKey(config('stripe.sk'));
        $session = \Stripe\Checkout\Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'pln',
                        'product_data' => [
                             'name' => 'Do zapłaty:',
                        ],
                        'unit_amount' => $cart->getSum() * 100,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => route('success'),
            'cancel_url' => route('cancel'),
        ]);
        Session::put('order_id', $order->id);
        return redirect()->away($session->url);
    }

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
        $cart = Session::get('cart', new Cart());
        return view('stripe.success');
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

    public function updatePaymentStatus(Request $request)
    {
        $session_id = $request->get('session_id');
        $payment = Payment::where('session_id', $session_id)->firstOrFail();
        \Stripe\Stripe::setApiKey(config('stripe.sk'));
        $stripe_session = \Stripe\Checkout\Session::retrieve($session_id);

        switch ($stripe_session->payment_status) {
            case 'paid':
                $payment->status = PaymentStatus::SUCCESS;
                break;
            case 'canceled':
                $payment->status = PaymentStatus::FAIL;
                break;
            default:
                $payment->status = PaymentStatus::IN_PROGRESS;
                break;
        }
        $payment->save();
    }
}
