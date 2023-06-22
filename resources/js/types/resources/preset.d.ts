import type {LinkResource} from "@/types/resources/link";

export interface PresetResource
{
    link: LinkResource;
    // TODO: PresetType
    type: string;
}