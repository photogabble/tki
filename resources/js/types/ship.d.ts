import {SectorResource} from "@/types/sector";

// TODO Ship Sizes
export type ShipSize = 'S' | 'M' | 'L';

export type CurrentMax = {
    current: number;
    max: number;
}

export interface ShipCargo
{
    ore: number
    organics: number
    goods: number
    energy: number
    colonists: number
    holds_used: number;
    holds_max: number;
}

export interface ShipDevices
{
    beacon: number
    warp_edit: number
    genesis: number
    mine_deflector: number
    emergency_warp: number
    lssd_installed: boolean
    escape_pod_installed: boolean
    fuel_scoop_installed: boolean
}

export interface ShipFitting
{
    hull: number
    engines: number
    power: number
    computer: number
    sensors: number
    armor: number
    shields: number
    beams: number
    torp_launchers: number
    cloak: number
}

export interface ShipResource
{
    name: string;
    level: number;

    armor: CurrentMax,

    weapons: {
        fighters: CurrentMax,
        torpedoes: CurrentMax,
    };

    sector_id: number;
    sector: SectorResource;
    devices: ShipDevices;
    cargo_holds: ShipCargo;
    energy: CurrentMax;
    fitting: ShipFitting;
    avg_fitting_level: number;
}