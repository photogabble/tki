import {PageProps as InertiaPageProps} from "@inertiajs/core";

export interface RankingResource {
    name: string
    turns_used: number
    score: number
    last_login: {
        nice: string,
        unix: number,
    }
    rating
    efficiency: number
    team: any; // todo TeamResource
}

export interface PlayerRankingParams {
    sort_players_by: string;
    sort_players_direction?: string;
}

export interface PaginationLink {
    active: boolean;
    label: string;
    url: string;
}

export interface PaginatedRankingResource {
    data: Array<RankingResource>;
    links: {
        first: string,
        last: string,
        next: null | string,
        prev: null | string,
    };
    meta: {
        current_page: number,
        from: number,
        last_page: number,
        links: Array<PaginationLink>,
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    player: {
        sorts: Array<string>;
        sorting_by: string;
        sorting_direction: 'ASC' | 'DESC';
        ranking: PaginatedRankingResource;
    }
}

export interface RankingPageProps extends InertiaPageProps, PageProps {
}