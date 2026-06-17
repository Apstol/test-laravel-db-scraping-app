<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Repositories\StocksRepository;
use Exception;
use Throwable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

#[Signature('app:scrape-stocks')]
#[Description('Scrape stocks')]
class ScrapeStocks extends Command
{
    public function __construct(private StocksRepository $stocksRepository)
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::disableQueryLog();
        $dispatcher = DB::connection()->getEventDispatcher();
        DB::connection()->unsetEventDispatcher();

        $stocksGenerator = $this->stocksRepository->getStocksGenerator();
        try {
            foreach($stocksGenerator as $stocksBatch) {
                $this->info('Received a batch');
                foreach ($stocksBatch as $stock) {
                    try {
                        $this->validate($stock);
                    } catch (Exception $e) {
                        Log::channel('single')->error($stock);
                        throw $e;
                    }
                }

                Stock::insert($stocksBatch);

                gc_collect_cycles();
            }
        } catch (Throwable $e) {
            Stock::truncate();
            throw $e;
        }

        DB::enableQueryLog();
        DB::connection()->setEventDispatcher($dispatcher);

        $this->info('Success');
    }

    private function validate(array $stock): void
    {
        Validator::make($stock, [
            'date' => ['required', 'date_format:Y-m-d'],
            'last_change_date' => ['date_format:Y-m-d', 'nullable'],
            'supplier_article' => ['string', 'nullable', 'max:255'],
            'tech_size' => ['string', 'nullable', 'max:255'],
            'barcode' => ['required', 'integer:strict'],
            'quantity' => ['required', 'integer'],
            'is_supply' => ['boolean:strict', 'nullable'],
            'is_realization' => ['boolean:strict', 'nullable'],
            'quantity_full' => ['integer:strict', 'nullable'],
            'warehouse_name' => ['required', 'string', 'max:255'],
            'in_way_to_client' => ['integer:strict', 'nullable'],
            'in_way_from_client' => ['integer:strict', 'nullable'],
            'nm_id' => ['required', 'integer:strict'],
            'subject' => ['string', 'nullable', 'max:255'],
            'category' => ['string', 'nullable', 'max:255'],
            'brand' => ['string', 'nullable', 'max:255'],
            'sc_code' => ['integer:strict', 'nullable'],
            'price' => ['integer', 'nullable'],
            'discount' => ['integer', 'nullable'],
        ])
            ->validate();
    }
}
