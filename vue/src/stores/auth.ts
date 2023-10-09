import { ref } from 'vue'
import type { Ref } from 'vue';
import { defineStore } from 'pinia'
import { players, configuration as configurationAPI, basicSettings } from '@/lib/api';
import lang from '@/lib/lang';
import type {Configuration, Player, PlayerById, Sheet } from '@/lib/types';

export const useAuthStore = defineStore('auth', () => {
    const nonce = ref('');
    const baseUrl = ref('');
    const playersList:Ref<Array<Player>> = ref([]);
    const playerById:Ref<PlayerById> = ref({});
    const currentGroup = ref('all');
    const configuration:Ref<Configuration> = ref({validgroups:[]});
    const sheets:Ref<Array<Sheet>> = ref([]);
    const attributes:Ref<Array<string>> = ref([]);
    const groupingvalues:Ref<Array<string>> = ref([]);

    function getConfiguration()
    {
        return configurationAPI().then((data:any) => {
            configuration.value.base_rank = '' + (data.data.base_rank || 1000);
            configuration.value.k_value = '' + (data.data.k_value || 32);
            configuration.value.c_value = '' + (data.data.c_value || 400);
            configuration.value.namefield = data.data.namefield || '';
            configuration.value.groupingfield = data.data.groupingfield || 'none';
            configuration.value.validgroups = data.data.validgroups || [];
            configuration.value.sheet = data.data.sheet || 0;
        });
    }

    function getBasicSettings(sheet:number, groupingfield:string)
    {
        return basicSettings(sheet, groupingfield).then((data:any) => {
            attributes.value = data.data.attributes || [];
            groupingvalues.value = data.data.groupingvalues || [];
            sheets.value = data.data.sheets || [];
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
            console.log(e);
            alert(lang.ERROR_PLAYERS);
        })
    }

    function updatePlayerInList(player:Player)
    {
        let wasFound = false;
        const newlist = playersList.value.map((data) => {
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
            for (let i = 0; i< sortingvalue.length; i++) {
                const c = sortingvalue[i];
                let v1 = null;
                let v2 = null;
                let comp = -1;
                switch (c) {
                    case 'n':
                        comp = 1;
                        // falls through
                    case 'N':
                        v1 = pa.name;
                        v2 = pb.name;
                        break;
                    case 'i':
                        comp = 1;
                        // falls through
                    case 'I':
                        v1 = pa.id;
                        v2 = pb.id;
                        break;
                    case 'g':
                        comp = 1;
                        // falls through
                    case 'G':
                        v1 = pa.groupname;
                        v2 = pb.groupname;
                        break;
                    case 'r':
                        comp = 1;
                        // falls through
                    case 'R':
                        v1 = pa.rank;
                        v2 = pb.rank;
                        break;
                    default:
                        break;
                }

                if (!v1 && v2) return -1*comp;
                else if(v1 && !v2) return comp;
                else if (v1 && v2 && v1 != v2) {
                    if (v1 > v2) return comp;
                    return -1 * comp;
                }
            }
            return 0;
        });
    }

    return { 
        nonce, baseUrl, currentGroup, configuration, attributes, sheets, groupingvalues,
        getConfiguration, getBasicSettings,
        getPlayers, updatePlayerInList, sortPlayers, playersList, playerById
    }
})
