<?php

namespace Tki\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tki\Http\Resources\EncounterResource;
use Tki\Models\Encounter;

class EncounterController extends Controller
{
    public function current(Request $request): EncounterResource|JsonResponse
    {
        if ($currentEncounter = $request->user()->currentEncounter) {
            return new EncounterResource($currentEncounter);
        }

        return new JsonResponse(['message' => 'No current encounter'], 404);
    }

    public function doAction(string $action, Request $request)
    {
        if (!$encounter = $request->user()->currentEncounter) {

            throw new \Exception('rah');

            if ($request->expectsJson()) {
                return new JsonResponse(['message' => 'No current encounter'], 404);
            } else {
                return redirect()->back()->with([
                    'message' => 'No current encounter',
                ]);
            }
        }
        /** @var Encounter $encounter */
        $encounter->action()->do($action, $request->all());

        if ($request->expectsJson()) {
            return new EncounterResource($request->user()->currentEncounter()->get());
        }

        return redirect()->back();
    }
}
