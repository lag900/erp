<?php

namespace App\Observers;

use App\Models\User;
use App\Traits\LogsActivity;

class UserObserver
{
    use LogsActivity;

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        
        // Remove timestamps and security fields from log details
        unset($changes['updated_at']);
        unset($changes['password']);
        unset($changes['remember_token']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'attributes' => $changes,
            'old' => array_intersect_key($user->getOriginal(), $changes),
        ];

        $description = "Updated user profile: {$user->name}";
        
        if (array_key_exists('image', $changes)) {
            $description = "Changed profile picture for user: {$user->name}";
        }

        $this->logActivity('updated', $description, $properties);
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->logActivity('created', "Created new user account: {$user->name}", [
            'attributes' => $user->getAttributes(),
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->logActivity('deleted', "Deleted user account: {$user->name}");
    }
}
