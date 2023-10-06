<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { matches, reassessMatches } from '@/lib/api';
import type { FieldDefinition, Match, Result } from '@/stores/auth';
import { useAuthStore } from '@/stores/auth';
import { dayjs } from 'element-plus';
import lang from '@/lib/lang.js';
const props = defineProps<{
    visible:boolean;
}>();

const auth = useAuthStore();
watch (
    () => props.visible,
    (nw) => {
        if (nw) {
            if (!auth.configuration.groupingvalues?.includes(auth.currentGroup) && auth.configuration.groupingvalues?.length) {
                auth.currentGroup = 'all';
            }
            auth.getPlayers()
                .then(() => {
                    updateMatches();
                });
        }
    },
    { immediate: true}
)

const matchList:Ref<Array<Match>> = ref([]);
const matchCount = ref(0);
function updateMatches()
{
    matches().then((data:any) => {
        if (data.data) {
            matchList.value = data.data.list || [];
            matchCount.value = data.data.count;
        }
    });
}

const matchValue:Ref<Match> = ref({id:0, entered_at: '', results: [{id:0, player_id: 0}, {id:0, player_id: 0}]});
const visible:Ref<boolean> = ref(false);

function onClose()
{
    visible.value = false;
}

function onSave()
{
    visible.value = false;
    updateMatches();
}

function onUpdate(fieldDef:FieldDefinition)
{
    switch(fieldDef.field)
    {
        case 'player_1':
            matchValue.value.results[0].player_id = parseInt(fieldDef.value);
            break;
        case 'player_2':
            matchValue.value.results[1].player_id = parseInt(fieldDef.value);
            break;
        case 'score_1':
            matchValue.value.results[0].score = parseInt(fieldDef.value);
            break;
        case 'score_2':
            matchValue.value.results[1].score = parseInt(fieldDef.value);
            break;
        case 'entered_at':
            matchValue.value.entered_at = fieldDef.value;
            break;
    }
}

function addNew()
{
    matchValue.value = {id: 0, entered_at:'', results:[{id:0, player_id: 0}, {id:0, player_id: 0}]};
    visible.value = true;
}

function reassess()
{
    reassessMatches()
        .then(() => {
            updateMatches();
            alert(lang.MSG_REASSESSED);
        });
}

function getPlayersFromResults(matchData:Match)
{
    if (matchData.results && matchData.results.length == 2) {
        return getPlayerFromResult(matchData.results[0]) + ' vs ' + getPlayerFromResult(matchData.results[1]);
    }
    return '';
}

function getPlayerFromResult(result:Result)
{
    let scorechange:string = '' + result.rank_change;
    if (result.rank_change && result.rank_change > 0) {
        scorechange = '+' + scorechange;
    }
    let memberId = 'p' + result.player_id;
    if (auth.playerById[memberId]) {
        return auth.playerById[memberId].name + '(' + scorechange + ')';
    }
    return lang.UNKNOWNPLAYER;
}

function getScoreFromResults(matchData:Match)
{
    if (matchData.results && matchData.results.length == 2) {
        return matchData.results[0].score + ' - ' + matchData.results[1].score;
    }
    return '';
}

function formatMatchDate(dt:string)
{
    return dayjs(dt).format(lang.DATEFORMATWITHOUTMINUTES);
}

import MatchDialog from './MatchDialog.vue';
import { ElButton, ElSelect, ElOption } from 'element-plus';
import GroupSelector from './GroupSelector.vue';
</script>
<template>
    <div>
        <div class="grid-header">
            <GroupSelector />
            <ElButton @click="reassess" type="primary">{{ lang.REASSESS }}</ElButton>
            <ElButton @click="addNew" type="primary">{{ lang.ADD }}</ElButton>
        </div>
        <div class="grid">
            <table>
                <thead>
                    <tr>
                        <th>{{ lang.PLAYERS }}</th>
                        <th>{{ lang.SCORE }}</th>
                        <th>{{ lang.RANK }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="matchData in matchList" :key="matchData.id" @dblclick="() => { matchValue = matchData; visible = true;}">
                        <td>{{ getPlayersFromResults(matchData) }}</td>
                        <td>{{ getScoreFromResults(matchData) }}</td>
                        <td>{{ formatMatchDate(matchData.entered_at) }}</td>
                        <td>{{ matchData.id }}: {{  matchData.results[0].is_dirty }} / {{  matchData.results[1].is_dirty }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <MatchDialog :matchdata="matchValue" :visible="visible" @on-close="onClose" @on-save="onSave" @on-update="onUpdate"/>
    </div>
</template>