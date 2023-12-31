import { ref } from 'vue'
import type { Ref } from 'vue';
import { defineStore } from 'pinia'
import { players, configuration as configurationAPI, basicSettings } from '@/lib/api';
import lang from '@/lib/lang';
import type {Configuration, Player, PlayerById, Sheet } from '@/lib/types';

export const useAuthStore = defineStore('auth', () => {
    const nonce = ref('');
    const baseUrl = ref('');
    const token = ref('');
    const isfrontend = ref(false);
    const playersList:Ref<Array<Player>> = ref([]);
    const playerById:Ref<PlayerById> = ref({});
    const currentGroup = ref('all');
    const currentRanking = ref('');
    const configuration:Ref<Configuration> = ref({validgroups:[]});
    const sheets:Ref<Array<Sheet>> = ref([]);
    const attributes:Ref<Array<string>> = ref([]);
    const groupingvalues:Ref<Array<string>> = ref([]);
    const rankattributes:Ref<Array<string>> = ref([]);

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
            configuration.value.token = data.data.token || '';
        });
    }

    function getBasicSettings(sheet:number, groupingfield:string)
    {
        return basicSettings(sheet, groupingfield).then((data:any) => {
            attributes.value = data.data.attributes || [];
            groupingvalues.value = data.data.groupingvalues || [];
            sheets.value = data.data.sheets || [];
            rankattributes.value = data.data.rankAttributes || [];
            if (!currentRanking.value || currentRanking.value.length == 0) {
                currentRanking.value = rankattributes.value[0];
            }
        });
    }

    function getPlayers()
    {
        return players(token.value).then((data:any) => {
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

    return { 
        nonce, baseUrl, token, isfrontend, currentGroup, currentRanking, configuration,
        attributes, sheets, groupingvalues, rankattributes,
        getConfiguration, getBasicSettings,
        getPlayers, updatePlayerInList, playersList, playerById
    }
})
