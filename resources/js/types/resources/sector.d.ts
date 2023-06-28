import type {ZoneResource} from "@/types/resources/zone";
import type {LinkResource} from "@/types/resources/link";
import type {SectorDefenseResource} from "@/types/resources/sector-defense";
import type {PlanetResource} from "@/types/resources/planet";

export type SectorType = 'unknown' | 'none' | 'port-goods' | 'port-energy' | 'port-ore' | 'port-special';

export interface SectorResource
{
    id: number;
    beacon: string;
    port_type: SectorType;

    // Scanned Resources:
    zone?: ZoneResource;
    planets?: Array<PlanetResource>;
    ports?: Array<any>;
    defenses?: Array<SectorDefenseResource>;
    links?: Array<LinkResource>;
}

export interface SectorResourceWithPlayerMeta extends SectorResource
{
    is_current_sector: boolean
    has_visited: boolean;
    has_danger: boolean;
}
