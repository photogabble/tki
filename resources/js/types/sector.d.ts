import {ZoneResource} from "@/types/zone-info";
import {LinkResource} from "@/types/link";
import {SectorDefenseResource} from "@/types/sector-defense";
import {PlanetResource} from "@/types/planet";

export interface SectorResource
{
    id: number
    zone: ZoneResource
    planets: Array<PlanetResource>;
    ports: Array<any>;
    defenses: Array<SectorDefenseResource>;
    links: Array<LinkResource>;
}