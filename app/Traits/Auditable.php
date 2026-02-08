<?php

namespace App\Traits;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the Auditable trait for the model.
     */
    public static function bootAuditable()
    {
        static::observe(AuditObserver::class);
    }
}
