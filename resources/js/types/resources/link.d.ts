import {SectorResourceWithPlayerMeta} from "@/types/resources/sector";

export interface LinkResource {
    to_sector_id: number;
    hasVisited: Boolean;
    hasDanger: Boolean;
}

export interface WarpRouteResource {
    start: LinkResource;
    path: Array<LinkResource>;
}