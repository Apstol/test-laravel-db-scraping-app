<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Repositories\SalesRepository;
use Exception;
use Illuminate\Support\Facades\App;
use Throwable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

#[Signature('app:scrape-sales')]
#[Description('Scrape sales')]
class ScrapeSales extends Command
{
    public function __construct(private SalesRepository $salesRepository)
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::disableQueryLog();
        $dispatcher = DB::connection()->getEventDispatcher();
        DB::connection()->unsetEventDispatcher();

        $salesGenerator = $this->salesRepository->getSalesGenerator();
        try {
            foreach($salesGenerator as $salesBatch) {
                $this->info('Received a batch');
                foreach ($salesBatch as $sale) {
                    try {
                        $this->validate($sale);
                    } catch (Exception $e) {
                        Log::channel('single')->error($sale);
                        throw $e;
                    }
                }

                Sale::insert($salesBatch);

                gc_collect_cycles();
            }
        } catch (Throwable $e) {
            Sale::truncate();
            throw $e;
        }

        DB::enableQueryLog();
        DB::connection()->setEventDispatcher($dispatcher);

        $this->info('Success');
    }

    private function validate(array $sale): void
    {
        Validator::make($sale, [
            'g_number' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
            'last_change_date' => ['required', 'date_format:Y-m-d'],
            'supplier_article' => ['required', 'string', 'max:255'],
            'tech_size' => ['required', 'string', 'max:255'],
            'barcode' => ['required', 'integer:strict'],
            'total_price' => ['required', 'numeric'],
            'discount_percent' => ['required', 'integer'],
            'is_supply' => ['required', 'boolean:strict'],
            'is_realization' => ['required', 'boolean:strict'],
            'promo_code_discount' => ['integer', 'nullable'],
            'warehouse_name' => ['required', 'string', 'max:255'],
            'country_name' => ['required', 'string', 'max:255'],
            'region_name' => ['required', 'string', 'max:255'],
            'income_id' => ['required', 'integer:strict'],
            'sale_id' => ['required', 'string', 'max:255'],
            'odid' => ['string', 'max:255', 'nullable'],
            'spp' => ['required', 'integer'],
            'for_pay' => ['required', 'numeric'],
            'finished_price' => ['required', 'numeric'],
            'price_with_disc' => ['required', 'numeric'],
            'nm_id' => ['required', 'integer:strict'],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'is_storno' => ['boolean', 'nullable'],
        ])
            ->validate();
    }
}
