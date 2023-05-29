<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\ValueObjects\Cart;
use Barryvdh\Debugbar\Facades\Debugbar;
use Devpark\Transfers24\Exceptions\RequestException;
use Devpark\Transfers24\Exceptions\RequestExecutionException;
use Devpark\Transfers24\Requests\Transfers24;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Omnipay\Omnipay;

class OrderController extends Controller
{
    private Transfers24 $transfers24;
    public $gateway;
    public function __construct(Transfers24 $transfers24)
    {
        $this->transfers24 = $transfers24;
        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId(env('PAYPAL_CLIENT_ID'));
        $this->gateway->setSecret(env('PAYPAL_SECRET_ID'));
        $this->gateway->setTestMode(true); // set 'false' when go live
    }
    /** Display a listing of the resource */
    public function index(): View
    {
        return view("orders.index", [
            'orders' => Order::where('user_id', Auth::id())->paginate(10)
        ]);
    }
    /**  Store a newly created resource in storage */
    public function store(Request $request): RedirectResponse
    {
        $paymentSystem = $request->post('inlineRadioOptions');
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

            switch ($paymentSystem) {
                case 'option1':
                    return $this->paymentTransfer24($order);
                case 'option2':
                    return $this->paymentStripe($order);
                case 'option3':
                    return $this->paymentPaypal($order);
                default:
                    return back()->with('warning', 'Coś poszło nie tak.');
            }
        }
        return back();
    }

    private function paymentTransfer24(Order $order)
    {
        $payment = new Payment();
        $payment->order_id = $order->id;
        $this->transfers24->setEmail(Auth::user()->email)->setAmount($order->price);
        try {
            $response = $this->transfers24->init();
            if ($response->isSuccess()) {
                $payment->status = PaymentStatus::IN_PROGRESS;
                $payment->session_id = $response->getSessionId();
                $payment->save();
                Session::put('cart', new Cart());
                return redirect($this->transfers24->execute($response->getToken()));
            } else {
                $payment->status = PaymentStatus::FAIL;
                $payment->error_code = $response->getErrorCode();
                $payment->error_description = json_encode($response->getErrorDescription());
                $payment->save();
                return back()->with('warning', 'Coś poszło nie tak.');
            }
        } catch (RequestException|RequestExecutionException $error) {
            Log::error("Błąd transakcji", ['error' => $error]);
            return back()->with('warning', 'Coś poszło nie tak.');
        }
    }

    public function paymentStripe(Order $order)
    {
        $cart = Session::get('cart', new Cart());
        try {
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
                'cancel_url'=> url('cancel'),
            ]);
            Session::put('order_id', $order->id);
            return redirect()->away($session->url);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function paymentPaypal(Order $order)
    {
        $cart = Session::get('cart', new Cart());
        try {
            $response = $this->gateway->purchase([
                'amount' => $cart->getSum(),
                'currency' => env('PAYPAL_CURRENCY'),
                'returnUrl' => route('success'),
                'cancelUrl' => route('cancel'),
            ])->send();

            if ($response->isRedirect()) {
                $response->redirect();
                return redirect();

            } else {
                return $response->getMessage();
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
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
        Session::put('cart', new Cart());
        return view('orders.success');
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
        Session::put('cart', new Cart());
        return view('orders.cancel');
    }
}
