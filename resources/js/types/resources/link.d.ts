import {SectorResourceWithPlayerMeta} from "@/types/resources/sector";

export interface LinkResource {
    to_sector_id: number;
    hasVisited: Boolean;
    hasDanger: Boolean;
}

export interface WarpRouteResource {
    route: {
        start: LinkResource;
        waypoints: Array<LinkResource>;
    };
    remaining: number;
    next: number;
    sectors: Array<number>;
    id: string;
}

export interface CalculateWarpMovesResource {
    result: WarpRouteResource;
    engage: string;
}