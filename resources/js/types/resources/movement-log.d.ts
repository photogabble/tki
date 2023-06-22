import {SectorResource} from "@/types/resources/sector";

export interface MovementLogResource {
    sector_id: number;
    timestamp: number; // Unix
    sector?: SectorResource;
}