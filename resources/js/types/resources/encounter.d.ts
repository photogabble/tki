export type EncounterType = string;

export interface EncounterResource {
    id: number;
    title: string;
    type: EncounterType;
    messages: Array<string>;
    options: Record<string, string>
}