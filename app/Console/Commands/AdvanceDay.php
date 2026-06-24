<?php

namespace App\Console\Commands;

use App\Services\ClockService;
use Illuminate\Console\Command;

class AdvanceDay extends Command
{
    protected $signature = 'seapedia:advance-day';

    protected $description = 'Advance the simulated clock by one day (§5.7).';

    public function __construct(private readonly ClockService $clock)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $next = $this->clock->advance(1);

        $this->info("Simulated clock advanced to {$next->toDateString()}.");

        return self::SUCCESS;
    }
}
