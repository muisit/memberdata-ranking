export interface Rankings {
    [key:string]: number;
}

export interface Player {
    id: number;
    name: string;
    groupname?: string;
    status?: string;
    rankings: Rankings;
    position?: number; // transient position recalculated each time we adjust the filter
}

export interface PlayerById {
    [key:string]: Player;
}

export interface FieldDefinition {
    field: string;
    value: string;
}

export interface Sheet {
    id: number;
    name: string;
}

export interface Configuration {
    base_rank?: string;
    s_value?: string;
    c_value?: string;
    k_value?: string;
    l_value?: string;
    token ?:string;
    namefield?: string;
    groupingfield?: string|null;
    validgroups: Array<string>;
    sheet?: number;
}

export interface BasicSettings {
    attributes?: Array<string>;
    sheets?: Array<Sheet>;
    groupingvalues?: Array<string|null>;
}

export interface Result {
    id: number;
    match_id?: number;
    player_id?: number;
    score ?: number;
    expected ?: number;
    rank_start ?: number;
    rank_change ?: number;
    rank_end ?: number;
    c_value ?: number;
    s_value ?: number;
    k_value ?: number;
    l_value ?: number;
    is_dirty?: string;
    modified?: string;
    modifier?: number;
}

export interface Match {
    id: number;
    matchtype: string;
    entered_at: string;
    results: Array<Result>;
}


export interface APIResult {
    success?: boolean;
    data?: any;
}
