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
    inProgress: boolean;
    id: string;
}
