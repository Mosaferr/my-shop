<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        return view('home');
    }

    /**
     * Store a newly created resource in storage.
     * @param Product $product
     * @return JsonResponse
     */
    public function store(Product $product): JsonResponse
    {
        return response()->json([
            'status' => 'success'
        ]);
    }
}
