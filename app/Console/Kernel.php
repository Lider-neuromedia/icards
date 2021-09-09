<?php

namespace App\Console;

use App\Mail\RenewSubscriptionNotified;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Enviar notificaciones de vencimiento de suscripción.
        $schedule->call(function () {
            $clients = User::query()
                ->whereRole(User::ROLE_CLIENT)
                ->whereHas('subscriptions', function ($q) {
                    $q->whereDate('finish_at', Carbon::now()->addDays(15))
                        ->orWhereDate('finish_at', Carbon::now()->addDays(30));
                })
                ->get();

            foreach ($clients as $client) {
                $days = $client->getSubscriptionDaysLeft();
                if ($days == 15 || $days == 30) {
                    Mail::to($client)->send(new RenewSubscriptionNotified($client));
                }
            }
        })->dailyAt('12:00');
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
