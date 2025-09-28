<?php

namespace App\Console;

use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\MonthlyPaymentRemainder',
        'App\Console\Commands\monthlyAutoServiceOff',
        'App\Console\Commands\MonthlyInvoiceCreate',
        'App\Console\Commands\InvoiceAndProfileMissMatchFixing',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sms:paymentRemainder')->daily();
        $schedule->command('sms:invoiceCreate')->everySixHours();
        // $schedule->command('sms:invoiceCreate')->everyMinute();
        $schedule->command('sms:internetServiceOff')->everyTenMinutes();
        $schedule->command('fix:invoiceAndProfileMissmatch')->everySixHours();

        // $schedule->command('activitylog:clean')->daily();
        $schedule->call(function () {
            Activity::where('updated_at', '<', Carbon::now()->subDays(40))->delete();
        })->daily();
        $schedule->command('queue:work')->daily()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
