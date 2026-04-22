<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Vendor;
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

        // Check if vendor is open
        $vendor = Vendor::find($validated['vendor_id']);
        if (!$vendor || !$vendor->is_open) {
            return redirect()->back()->with('error', 'This shop is currently closed and not accepting orders.');
        }

        // Check if product exists and is active
        $product = Product::where('id', $validated['product_id'])
            ->where('is_active', true)
            ->first();
            
        if (!$product) {
            return redirect()->back()->with('error', 'This product is not available.');
        }

        // Check if vendor has this product and it's available
        $vendorProduct = $vendor->products()
            ->where('product_id', $validated['product_id'])
            ->where('is_available', true)
            ->first();
            
        if (!$vendorProduct) {
            return redirect()->back()->with('error', 'This product is not available from this vendor.');
        }

        // Check stock
        if ($vendorProduct->pivot->stock_quantity < $validated['quantity']) {
            return redirect()->back()->with('error', 'Insufficient stock available.');
        }

        $price = $product->getPriceForSize($validated['size'] ?? null);
        
        // Use custom vendor price if set
        if ($vendorProduct->pivot->custom_price) {
            $price = $vendorProduct->pivot->custom_price;
        }

        // Check if product already in cart
        $existingCartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $validated['product_id'])
            ->where('vendor_id', $validated['vendor_id'])
            ->where('size', $validated['size'] ?? null)
            ->first();

        if ($existingCartItem) {
            $existingCartItem->increment('quantity', $validated['quantity']);
            $message = 'Product quantity updated in cart';
        } else {
            Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
                'vendor_id' => $validated['vendor_id'],
                'quantity' => $validated['quantity'],
                'size' => $validated['size'] ?? null,
                'price' => $price,
            ]);
            $message = 'Product added to cart';
        }

        // Handle Buy Now flag
        if ($request->input('buy_now') === '1') {
            return redirect()->route('customer.checkout');
        }

        return redirect()->back()->with('success', $message);
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

        // Check vendor is still open
        if (!$cartItem->vendor->is_open) {
            return redirect()->back()->with('error', 'This shop is currently closed. You cannot update items from this shop.');
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