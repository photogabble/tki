export type ZonePermission = 'Y' | 'N' | 'L';

export interface ZoneResource {
    name: String;
    owner: String;
    isFriendly: ZonePermission;
    isEditable: Boolean;
    allow_beacon: ZonePermission;
    allow_attack: ZonePermission;
    allow_planetattack: ZonePermission;
    allow_warpedit: ZonePermission;
    allow_planet: ZonePermission;
    allow_trade: ZonePermission;
    allow_defenses: ZonePermission;
    max_hull: Number;
    over_size: Boolean;
}