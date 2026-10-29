<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Stripe\Exception\ApiErrorException;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid {--minutes=60 : Age after which an unpaid card order is cancelled}';

    protected $description = 'Cancel unpaid card orders, close their PaymentIntent and release the reserved stock';

    public function handle(OrderService $orders): int
    {
        $cancelled = 0;

        Order::where('status', 'pending')
            ->where('payment_status', 'pending')
            ->whereNotNull('payment_reference')
            ->where('created_at', '<', now()->subMinutes((int) $this->option('minutes')))
            ->each(function (Order $order) use ($orders, &$cancelled) {
                try {
                    $orders->cancelOrder($order);
                    $cancelled++;
                } catch (InvalidArgumentException|ApiErrorException $e) {
                    // Payment in flight or provider hiccup: leave it for the next run.
                    $this->warn("Skipped {$order->order_number}: {$e->getMessage()}");
                }
            });

        $this->info("Cancelled {$cancelled} unpaid order(s).");

        return self::SUCCESS;
    }
}
