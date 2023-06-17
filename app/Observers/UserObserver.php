<?php

namespace Tki\Observers;

use Tki\Actions\Bounty;
use Tki\Actions\Character;
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
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // TODO: Kill Character
        //(new Character)->kill();

        // TODO: Cancel Bounty
        //(new Bounty)->cancel();

        // KBI has the planet records be deleted, while BN has the
        // user association removed.
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
