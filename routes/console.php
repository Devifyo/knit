<?php

declare(strict_types=1);

use App\Modules\Support\Console\CheckTicketSla;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Escalate SLA-breached tickets across all workspaces (knit_scheduler container).
Schedule::command(CheckTicketSla::class)->everyMinute()->withoutOverlapping();
