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
//    private \Omnipay\Common\GatewayInterface $gateway;
    public function __construct(Transfers24 $transfers24)
    {
        $this->transfers24 = $transfers24;
        $this->gateway = Omnipay::create('PayPal_Rest');

        $this->gateway->setClientId(env('PAYPAL_CLIENT_ID'));
        $this->gateway->setSecret(env('PAYPAL_SECRET_ID'));
        $this->gateway->setTestMode(true); //'false' when go live
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
                    Log::error("Błąd transakcji");
                    return back()->with('warning', 'Coś poszło nie tak.');
            }
        }
        return back();
    }
/*======================================================================================*/
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
/*--------------------------------------------------------------------------------------*/
    public function paymentStripe(Order $order)
    {
        $cart = Session::get('cart',new Cart());
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
/*--------------------------------------------------------------------------------------*/

    public function paymentPaypal(Order $order)
    {
        $cart = Session::get('cart',new Cart());
        try {
            $response=$this->gateway->purchase(array(
                'amount'=>$cart->getSum(),
                'currency'=>env('PAYPAL_CURRENCY'),
                'returnUrl' => route('success'),
                'cancelUrl'=>url('error'),

                /*'returnUrl'=>url('success'),
                'cancelUrl'=>url('error'),

                'success_url' => route('success'),
                'cancel_url' => route('cancel'),*/
            ))->send();

            if($response->isRedirect()) {
                $response->redirect();  // this will automatically forward the customer

            } else {      // not successful
                return $response->getMessage();
            }
        }catch(\Throwable $th) {
            return $th->getMessage();
        }
    }


//    public function success(Request $request)
//    {
//        if ($request->input('paymentId') && $request->input('PayerID')) {
//            $transaction = $this->gateway->completePurchase(array(
//                'payer_id' => $request->input('PayerID'),
//                'transactionReference' => $request->input('paymentId')
//            ));
//            $response = $transaction->send();
//            if ($response->isSuccessful()) {
//
//                /*$orderId = Session::get('order_id');
//                $order = Order::findOrFail($orderId);
//                $payment = new Payment([
//                    'status' => PaymentStatus::SUCCESS,
//                    'session_id' => $request->session_id,
//                    'order_id' => $order->id,
//                ]);
//                $payment->save();*/
//
//                //Debugbar::info($payment);
//
//                Session::put('cart', new Cart());   //czysty koszyk
//                return redirect()->route('cart.index');
////                return 'Płatność PayPal ZREALIZOWANA';
//            } else {
//                return $response->getMessage();
//            }
//
//        } else {
//            return 'Płatność PayPal odrzucono';
//        }
//    }
    public function error()
    {
        return 'Klient odmówił płatności PayPal';
    }
}
