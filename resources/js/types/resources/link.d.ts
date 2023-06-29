import {SectorResourceWithPlayerMeta} from "@/types/resources/sector";

export interface LinkResource {
    to_sector_id: number;
    hasVisited: Boolean;
    hasDanger: Boolean;
}

export interface WarpRouteResource {
    start: LinkResource;
    waypoints: Array<LinkResource>;
}

export interface CalculateWarpMovesResource {
    result: WarpRouteResource;
    engage: string;
}