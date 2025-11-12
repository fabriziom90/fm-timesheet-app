<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }
    
    public function update(User $user, TimeEntry $entry)
    {
        return $user->id === $entry->user_id;
    }
    
    public function delete(User $user, TimeEntry $entry)
    {
        return $user->id === $entry->user_id;
    }
}
