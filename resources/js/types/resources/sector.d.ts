import type {ZoneResource} from "@/types/resources/zone";
import type {LinkResource} from "@/types/resources/link";
import type {SectorDefenseResource} from "@/types/resources/sector-defense";
import type {PlanetResource} from "@/types/resources/planet";

export type SectorType = 'unknown' | 'none' | 'port-goods' | 'port-energy' | 'port-ore' | 'port-special';

export interface SectorResource
{
    id: number
    zone: ZoneResource
    planets?: Array<PlanetResource>;
    ports?: Array<any>;
    defenses?: Array<SectorDefenseResource>;
    links?: Array<LinkResource>;
}