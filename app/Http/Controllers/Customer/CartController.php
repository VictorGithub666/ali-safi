<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()->cart()
            ->with(['product', 'vendor'])
            ->get();
        
        $total = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        return view('customer.cart.index', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'size' => ['nullable', 'string'],
        ]);

        $product = Product::find($validated['product_id']);
        $price = $product->getPriceForSize($validated['size'] ?? null);

        Cart::create([
            'user_id' => auth()->id(),
            'product_id' => $validated['product_id'],
            'vendor_id' => $validated['vendor_id'],
            'quantity' => $validated['quantity'],
            'size' => $validated['size'] ?? null,
            'price' => $price,
        ]);

        return redirect()->back()->with('success', 'Product added to cart');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'cart_id' => ['required', 'exists:carts,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'action' => ['nullable', 'in:increase,decrease'],
        ]);

        $cartItem = Cart::find($validated['cart_id']);
        
        if ($cartItem->user_id !== auth()->id()) {
            abort(403);
        }

        if ($request->input('action') === 'increase') {
            $cartItem->increment('quantity');
        } elseif ($request->input('action') === 'decrease' && $cartItem->quantity > 1) {
            $cartItem->decrement('quantity');
        } elseif ($validated['quantity'] ?? null) {
            $cartItem->update(['quantity' => $validated['quantity']]);
        }

        return redirect()->back()->with('success', 'Cart updated');
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'cart_id' => ['required', 'exists:carts,id'],
        ]);

        $cartItem = Cart::find($validated['cart_id']);
        
        if ($cartItem->user_id !== auth()->id()) {
            abort(403);
        }

        $cartItem->delete();

        return redirect()->back()->with('success', 'Item removed from cart');
    }

    public function clear()
    {
        auth()->user()->cart()->delete();
        
        return redirect()->back()->with('success', 'Cart cleared');
    }
}
