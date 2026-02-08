<?php

namespace App\Listeners;

use App\Services\AuditService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;

class AuthAuditListener
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        $this->auditService->log(
            'login',
            'Authentication',
            $event->user,
            null,
            null,
            'success',
            'User logged in.'
        );
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            $this->auditService->log(
                'logout',
                'Authentication',
                $event->user,
                null,
                null,
                'success',
                'User logged out.'
            );
        }
    }

    /**
     * Handle failed login events.
     */
    public function handleFailed(Failed $event): void
    {
        $this->auditService->log(
            'failed_login',
            'Authentication',
            $event->user, // Might be null if user not found
            null,
            null,
            'failed',
            'Failed login attempt for credentials: ' . json_encode(Arr::except($event->credentials, ['password']))
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
        ];
    }
}
