import type {PageProps as InertiaPageProps} from "@inertiajs/core";
import type {PageProps as GlobalPageProps} from "./index";
import {MovementLogResource} from "@/types/resources/movement-log";
import {SectorResource} from "@/types/resources/sector";

export type PageProps = GlobalPageProps & {
    movement_log: Array<MovementLogResource>;
    current_sector: SectorResource;
}

export interface GalaxyOverviewPageProps extends InertiaPageProps, PageProps {
}