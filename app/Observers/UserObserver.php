<?php

namespace Tki\Observers;

use Tki\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Before the User record is deleted, remove them from the
     * universe.
     */
    public function deleting(User $user): void
    {
        // TODO: Kill Character
        //(new Character)->kill();

        // TODO: Cancel Bounty
        //(new Bounty)->cancel();

        // KBI has the planet records be deleted, while BN has the
        // user association removed.
    }
}
