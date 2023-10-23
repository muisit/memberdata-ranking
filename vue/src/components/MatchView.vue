<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { matches, reassessMatches } from '@/lib/api';
import type { FieldDefinition, Match, Result } from '@/lib/types';
import { useAuthStore } from '@/stores/auth';
import { dayjs } from 'element-plus';
import lang from '@/lib/lang';
const props = defineProps<{
    visible:boolean;
}>();

const auth = useAuthStore();
watch (
    () => props.visible,
    (nw) => {
        if (nw) {
            if (!auth.groupingvalues?.includes(auth.currentGroup) && auth.groupingvalues?.length) {
                auth.currentGroup = 'all';
            }
            auth.getPlayers()
                .then(() => {
                    if (!auth.rankattributes.includes(auth.currentRanking)) {
                        auth.currentRanking = auth.rankattributes[0];
                    }
                    updateMatches();
                });
        }
    },
    { immediate: true}
);
watch(
    () => auth.currentRanking,
    () => {
        updateMatches();
    },
    { immediate: true }
)

const matchList:Ref<Array<Match>> = ref([]);
const matchCount = ref(0);
function updateMatches()
{
    if (auth.currentRanking && auth.currentRanking.length && auth.rankattributes.includes(auth.currentRanking)) {
        matches(auth.currentRanking, auth.isfrontend ? 10 : 20, 0).then((data:any) => {
            if (data.data) {
                matchList.value = data.data.list || [];
                matchCount.value = data.data.count;
            }
        });
    }
}

const matchValue:Ref<Match> = ref({id:0, entered_at: '', results: [{id:0, player_id: 0}, {id:0, player_id: 0}], matchtype: ''});
const visibleDialog:Ref<boolean> = ref(false);

function onClose()
{
    visibleDialog.value = false;
}

function onSave()
{
    visibleDialog.value = false;
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
    matchValue.value = {id: 0, entered_at:dayjs().format(lang.DATEFORMATWITHOUTMINUTES), results:[{id:0, player_id: 0}, {id:0, player_id: 0}], matchtype: auth.currentRanking};
    visibleDialog.value = true;
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

function isDirtyMatch(matchData:Match)
{
    return matchData.results[0].is_dirty == 'Y' || matchData.results[1].is_dirty == 'Y';
}

import MatchDialog from './MatchDialog.vue';
import GroupSelector from './GroupSelector.vue';
import RankingSelector from './RankingSelector.vue';
import { ElIcon, ElButton } from 'element-plus';
import { Edit, Plus } from '@element-plus/icons-vue';
</script>
<template>
    <div>
        <div class="grid-header">
            <GroupSelector />
            <RankingSelector />
            <div class="grid-actions">
                <ElButton @click="reassess" type="primary" v-if="!auth.isfrontend">{{ lang.REASSESS }}</ElButton>
                <ElButton @click="addNew" type="primary" v-if="auth.token.length">
                    <ElIcon size="large"><Plus/></ElIcon>
                    &nbsp;{{ lang.ADD }}
                </ElButton>
            </div>
        </div>
        <div class="grid">
            <table>
                <thead>
                    <tr>
                        <th v-if="!auth.isfrontend"></th>
                        <th>{{ lang.PLAYERS }}</th>
                        <th>{{ lang.SCORE }}</th>
                        <th>{{ lang.RANK }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="matchData in matchList" :key="matchData.id" :class="{isdirty: isDirtyMatch(matchData)}">
                        <td v-if="!auth.isfrontend"><ElIcon size='large' @click="() => { matchValue = matchData; visibleDialog = true;}"><Edit/></ElIcon></td>
                        <td>{{ getPlayersFromResults(matchData) }}</td>
                        <td>{{ getScoreFromResults(matchData) }}</td>
                        <td>{{ formatMatchDate(matchData.entered_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <MatchDialog :matchdata="matchValue" :visible="visibleDialog" @on-close="onClose" @on-save="onSave" @on-update="onUpdate"/>
    </div>
</template>