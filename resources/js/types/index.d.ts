import {ShipResource} from "@/types/resources/ship";
import {PresetResource} from "@/types/resources/preset";
import {MovementLogResource} from "@/types/resources/movement-log";

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    lang: string;

    ship?: ShipResource;
    movement_log?: Array<MovementLogResource>;
    presets: Array<PresetResource>;

    turns: number;
    turns_used: number;
    credits: number;
    score: number;
}

export interface Owner {
    id: number;
    name: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
        online: Boolean;
    };
    stats: {
        total_players: number;
    }
};
