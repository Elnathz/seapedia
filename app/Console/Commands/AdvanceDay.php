<?php

namespace App\Console\Commands;

use App\Services\ClockService;
use App\Services\OverdueService;
use Illuminate\Console\Command;

class AdvanceDay extends Command
{
    protected $signature = 'seapedia:advance-day';

    protected $description = 'Advance the simulated clock by one day and run the overdue sweep (§5.7, §5.9).';

    public function __construct(
        private readonly ClockService $clock,
        private readonly OverdueService $overdue,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $next = $this->clock->advance(1);
        $swept = $this->overdue->sweep();

        $this->info("Simulated clock advanced to {$next->toDateString()}. Refunded {$swept['refunded_count']} overdue order(s).");

        return self::SUCCESS;
    }
}
