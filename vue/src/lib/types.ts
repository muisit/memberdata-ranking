export interface Player {
    id: number;
    name: string;
    groupname?: string;
    status?: string;
    rank: number;
}

export interface PlayerById {
    [key:string]: Player;
}

export interface FieldDefinition {
    field: string;
    value: string;
}

export interface Configuration {
    base_rank?: string;
    s_value?: string;
    c_value?: string;
    k_value?: string;
    l_value?: string;
    attributes?: Array<string>;
    namefield?: string;
    groupingfield?: string|null;
    groupingvalues?: Array<string|null>;
    validgroups: Array<string>;
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
    entered_at: string;
    results: Array<Result>;
}


export interface APIResult {
    success?: boolean;
    data?: any;
}
