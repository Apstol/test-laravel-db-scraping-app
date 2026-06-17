<?php

namespace App\Console\Commands;

use App\Models\Income;
use App\Repositories\IncomesRepository;
use Exception;
use Throwable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

#[Signature('app:scrape-incomes')]
#[Description('Scrape incomes')]
class ScrapeIncomes extends Command
{
    public function __construct(private IncomesRepository $incomesRepository)
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::disableQueryLog();
        $dispatcher = DB::connection()->getEventDispatcher();
        DB::connection()->unsetEventDispatcher();

        $incomesGenerator = $this->incomesRepository->getIncomesGenerator();
        try {
            foreach($incomesGenerator as $incomesBatch) {
                $this->info('Received a batch');
                foreach ($incomesBatch as $income) {
                    try {
                        $this->validate($income);
                    } catch (Exception $e) {
                        Log::channel('single')->error($income);
                        throw $e;
                    }
                }

                Income::insert($incomesBatch);

                gc_collect_cycles();
            }
        } catch (Throwable $e) {
            Income::truncate();
            throw $e;
        }

        DB::enableQueryLog();
        DB::connection()->setEventDispatcher($dispatcher);

        $this->info('Success');
    }

    private function validate(array $income): void
    {
        Validator::make($income, [
            'income_id' => ['required', 'integer:strict'],
            'number' => ['string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
            'last_change_date' => ['required', 'date_format:Y-m-d'],
            'supplier_article' => ['required', 'string', 'max:255'],
            'tech_size' => ['required', 'string', 'max:255'],
            'barcode' => ['required', 'integer:strict'],
            'quantity' => ['required', 'integer:strict'],
            'total_price' => ['required', 'integer'],
            'date_close' => ['required', 'date_format:Y-m-d'],
            'warehouse_name' => ['required', 'string', 'max:255'],
            'nm_id' => ['required', 'integer:strict'],
        ])
            ->validate();
    }
}
