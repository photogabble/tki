<?php

namespace Tki\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Tki\Models\User;
use Tki\Types\WarpRoute;

/**
 * @mixin WarpRoute
 * @property-read WarpRoute $resource
 */
class WarpRouteResource extends JsonResource
{
    protected User $user;

    public function __construct(WarpRoute $resource, User $user)
    {
        parent::__construct($resource);
        $this->user = $user;
    }

    public function toArray(Request $request): array
    {
        $remaining = $this->resource->remaining($this->user->ship->sector_id);
        $sectors = $this->resource->sectors;

        return [
            'route' => $this->resource,
            'remaining' => $remaining,
            'next' => $this->resource->next($this->user->ship->sector_id),
            'sectors' => $sectors,
            'inProgress' => $remaining > 0 && $remaining < (count($sectors) - 1), // minus one due to sectors including origin
            'id' => $this->resource->toUrlParam($request),
        ];
    }
}
