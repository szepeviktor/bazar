<?php

namespace Bazar\Cart;

use Bazar\Models\Cart;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;

class CookieDriver extends Driver
{
    public const EXPIRE_DAYS = 10;

    /**
     * Resolve the cart instance.
     *
     * @return \Bazar\Models\Cart
     */
    protected function resolve(): Cart
    {
        $cart = Cart::firstOrCreate([
            'token' => Cookie::get('cart_token'),
        ]);

        Cookie::queue('cart_token', $cart->token, self::EXPIRE_DAYS * Carbon::SECONDS_PER_MINUTE * Carbon::MINUTES_PER_HOUR * Carbon::HOURS_PER_DAY);

        return $cart;
    }
}
