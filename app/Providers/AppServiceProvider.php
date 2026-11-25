<?php

namespace App\Providers;

use App\Models\User;
use App\Services\SystemAudit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SystemAudit::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Para compatibilidad con MySQL con límite de índice 767 bytes
        Schema::defaultStringLength(191);

        foreach (['created', 'updated', 'deleted'] as $action) {
            Event::listen('eloquent.'.$action.': *', function (string $event, array $payload) use ($action): void {
                app(SystemAudit::class)->modelChanged($action, $payload[0]);
            });
        }
        DB::listen(fn ($event) => app(SystemAudit::class)->databaseChanged($event));
        foreach ([TransactionCommitted::class, TransactionRolledBack::class] as $eventClass) {
            Event::listen($eventClass, function ($event): void {
                app(SystemAudit::class)->transactionFinished(
                    $event->connectionName, $event->connection->transactionLevel(), $event instanceof TransactionRolledBack
                );
            });
        }
        $this->app->terminating(fn () => app(SystemAudit::class)->flush());
        foreach ([JobProcessed::class, JobExceptionOccurred::class] as $eventClass) {
            Event::listen($eventClass, fn () => app(SystemAudit::class)->flush());
        }
        Event::listen(MessageLogged::class, fn ($event) => app(SystemAudit::class)->messageLogged($event));
        Event::listen(Login::class, function (Login $event): void {
            app(SystemAudit::class)->record('login', 'Inicio de sesión.', [], $event->user instanceof User ? $event->user : null);
        });
        Event::listen(Logout::class, function (Logout $event): void {
            app(SystemAudit::class)->record('logout', 'Cierre de sesión.', [], $event->user instanceof User ? $event->user : null);
        });
        Event::listen(Failed::class, function (Failed $event): void {
            app(SystemAudit::class)->record('login_failed', 'Credenciales rechazadas.', [
                'metadata' => ['attempted_username' => $event->credentials['username'] ?? null],
            ], $event->user instanceof User ? $event->user : null);
        });
    }
}
