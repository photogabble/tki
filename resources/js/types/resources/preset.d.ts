import type {LinkResource} from "@/types/resources/link";

export interface PresetResource
{
    id: number;
    link: LinkResource;
    // TODO: PresetType
    type: string;
}