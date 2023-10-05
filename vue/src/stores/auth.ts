import { ref } from 'vue'
import type { Ref } from 'vue';
import { defineStore } from 'pinia'
import { players, configuration as configurationAPI } from '@/lib/api';

export interface Player {
    id: number;
    name: string;
    groupname?: string;
    status?: string;
    rank: number;
}

interface PlayerById {
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

export const useAuthStore = defineStore('auth', () => {
    const nonce = ref('');
    const baseUrl = ref('');
    const playersList:Ref<Array<Player>> = ref([]);
    const playerById:Ref<PlayerById> = ref({});
    const currentGroup = ref('all');
    const configuration:Ref<Configuration> = ref({validgroups:[]});

    function getConfiguration()
    {
        configurationAPI().then((data:any) => {
            configuration.value.base_rank = '' + (data.data.base_rank || 1000);
            configuration.value.k_value = '' + (data.data.k_value || 32);
            configuration.value.c_value = '' + (data.data.c_value || 400);
            configuration.value.attributes = data.data.attributes || [];
            configuration.value.namefield = data.data.namefield || '';
            configuration.value.groupingfield = data.data.groupingfield || 'none';
            configuration.value.groupingvalues = data.data.groupingvalues || [];
            configuration.value.validgroups = data.data.validgroups || [];
        });
    }

    function getPlayers()
    {
        return players().then((data:any) => {
            playersList.value = [];
            playerById.value = {};
            if (data.data) {
                
                playersList.value = data.data;
                playerById.value = {};
                playersList.value.forEach((player) => {
                    playerById.value['p' + player.id] = player;
                });
            }
            return playersList.value;
        })
        .catch((e:any) => {
            alert('There was a problem retrieving the list of players. Please try again');
        })
    }

    function updatePlayerInList(player:Player)
    {
        var wasFound = false;
        var newlist = playersList.value.map((data) => {
            if (data.id == player.id) {
                wasFound = true;
                return player;
            }
            return data;
        });
        if (!wasFound) {
            newlist.push(player);
        }
        playersList.value = newlist;

        playerById.value = {};
        playersList.value.forEach((player) => {
            playerById.value['p' + player.id] = player;
        });
    }

    function sortPlayers(sortingvalue:string)
    {
        playersList.value.sort((pa, pb) => {
            for (var i = 0; i< sortingvalue.length; i++) {
                var c = sortingvalue[i];
                var v1 = null;
                var v2 = null;
                var comp = -1;
                switch (c) {
                    case 'n':
                        comp = 1;
                    case 'N':
                        v1 = pa.name;
                        v2 = pb.name;
                        break;
                    case 'i':
                        comp = 1;
                    case 'I':
                        v1 = pa.id;
                        v2 = pb.id;
                        break;
                    case 'g':
                        comp = 1;
                    case 'G':
                        v1 = pa.groupname;
                        v2 = pb.groupname;
                        break;
                    case 'r':
                        comp = 1;
                    case 'R':
                        v1 = pa.rank;
                        v2 = pb.rank;
                        break;
                    default:
                        break;
                }

                if (v1 != v2) {
                    console.log('comparing ', v1, v2, v1>v2, comp);
                    if (v1 > v2) return comp;
                    return -1 * comp;
                }
            }
            return 0;
        });
    }

    return { 
        nonce, baseUrl, currentGroup, configuration,
        getConfiguration,
        getPlayers, updatePlayerInList, sortPlayers, playersList, playerById
    }
})
