import {ShipResource} from "@/types/ship";
import {PresetResource} from "@/types/preset";

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    lang: string;
    ship: ShipResource;
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
};
