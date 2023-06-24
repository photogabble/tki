<?php

namespace Tki\Http\Middleware;

use Tki\Http\Resources\UserResource;
use Tki\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? new UserResource($user) : null,
                'online' => !is_null($user),
            ],
            'stats' => [
                // TODO: include values from Footer.php here
                'total_players' => User::count(), // TODO Cache
                'players_online' => 0, //User::loggedInCount(),
                'scheduler_next_run' => 0, // Scheduler::nextRun()->unix(),
                'sched_ticks' => config('scheduler.sched_ticks')
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}
