<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Repositories\OrdersRepository;
use Exception;
use Illuminate\Support\Facades\App;
use Throwable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

#[Signature('app:scrape-orders')]
#[Description('Scrape orders')]
class ScrapeOrders extends Command
{
    public function __construct(private OrdersRepository $ordersRepository)
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::disableQueryLog();
        $dispatcher = DB::connection()->getEventDispatcher();
        DB::connection()->unsetEventDispatcher();

        $ordersGenerator = $this->ordersRepository->getOrdersGenerator();
        try {
            foreach($ordersGenerator as $ordersBatch) {
                $this->info('Received a batch');
                foreach ($ordersBatch as $order) {
                    try {
                        $this->validate($order);
                    } catch (Exception $e) {
                        Log::channel('single')->error($order);
                        throw $e;
                    }
                }

                Order::insert($ordersBatch);

                gc_collect_cycles();
            }
        } catch (Throwable $e) {
            Order::truncate();
            throw $e;
        }

        DB::enableQueryLog();
        DB::connection()->setEventDispatcher($dispatcher);

        $this->info('Success');
    }

    private function validate(array $order): void
    {
        Validator::make($order, [
            'g_number' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d H:i:s'],
            'last_change_date' => ['required', 'date_format:Y-m-d'],
            'supplier_article' => ['required', 'string', 'max:255'],
            'tech_size' => ['required', 'string', 'max:255'],
            'barcode' => ['required', 'integer:strict'],
            'total_price' => ['required', 'numeric'],
            'discount_percent' => ['required', 'integer'],
            'warehouse_name' => ['required', 'string', 'max:255'],
            'oblast' => ['required', 'string', 'max:255'],
            'income_id' => ['required', 'integer:strict'],
            'odid' => ['string', 'max:255', 'nullable'],
            'nm_id' => ['required', 'integer:strict'],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'is_cancel' => ['required', 'boolean'],
            'cancel_dt' => ['date_format:Y-m-d', 'nullable'],
        ])
            ->validate();
    }
}
