import type {PageProps as InertiaPageProps} from "@inertiajs/core";
import type {PageProps as GlobalPageProps} from "./index";
import {SectorResourceWithPlayerMeta} from "@/types/resources/sector";
import {PaginatedResource} from "@/types/laravel/pagination";

export interface RealSpaceMove {
    sector: SectorResourceWithPlayerMeta;
    can_navigate: boolean;
    turns: number;
    turns_available: number;
    energy_scooped: number;
}

export type PageProps = GlobalPageProps & {
    sectors: PaginatedResource<SectorResourceWithPlayerMeta>;
    rsMove?: RealSpaceMove;
    navCom?: unknown;
}

export interface GalaxyOverviewPageProps extends InertiaPageProps, PageProps {
}