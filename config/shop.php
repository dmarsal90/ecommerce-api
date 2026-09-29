<?php

return [
    'tax_rate' => env('SHOP_TAX_RATE', 0.08),
    'free_shipping_threshold' => env('SHOP_FREE_SHIPPING_THRESHOLD', 50),
    'shipping_cost' => env('SHOP_SHIPPING_COST', 9.99),
    'currency' => env('SHOP_CURRENCY', 'USD'),
];
