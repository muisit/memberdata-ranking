<script lang="ts" setup>
import { ref, watch } from 'vue';
import { is_valid } from '@/lib/functions';
import type { Match, Player } from '@/lib/types';
import { useAuthStore } from '@/stores/auth';
import { saveMatch, removeMatch as removeMatchAPI } from '@/lib/api';
import { dayjs } from 'element-plus';
import lang from '@/lib/lang';
const props = defineProps<{
    visible:boolean;
    matchdata: Match;
}>();
const emits = defineEmits(['onClose', 'onSave', 'onUpdate']);

const auth = useAuthStore();
const actionsDisabled = ref(false);

function closeForm()
{
    emits('onClose');
}

function removeMatch()
{
    if (is_valid(props.matchdata.id)) {
        if (confirm(lang.CONFIRM_DELETE_MATCH)) {
            actionsDisabled.value = true;
            removeMatchAPI(props.matchdata).then(() => {
                emits('onSave');
                actionsDisabled.value = false;
            });
        }
    }
}

function submitForm()
{
    if (actionsDisabled.value) return false;
    actionsDisabled.value = true;
    let retval = true;
    let matchdata = Object.assign({}, props.matchdata);

    matchdata.results[0].player_id = 0;
    matchdata.results[1].player_id = 0;
    auth.playersList.forEach((player:Player) => {
        let name = player.name.toLocaleLowerCase();
        if (player1.value.toLocaleLowerCase() == name) {
            matchdata.results[0].player_id = player.id;
        }
        if (player2.value.toLocaleLowerCase() == name) {
            matchdata.results[1].player_id = player.id;
        }
    });

    if (!is_valid(matchdata.results[0].player_id)) {
        alert(lang.MATCH_VALID_PLAYER1);
        retval = false;
    }
    if (!is_valid(matchdata.results[1].player_id)) {
        alert(lang.MATCH_VALID_PLAYER2);
        retval = false;
    }
    if (retval && matchdata.results[0].player_id == matchdata.results[1].player_id) {
        alert(lang.MATCH_NO_DOUBLE_PLAYER);
        retval = false;
    }
    var dt = dayjs(matchdata.entered_at);
    if (!dt.isValid()) {
        alert(lang.MATCH_VALID_DATE);
        retval = false;
    }

    if (retval && parseInt('' + matchdata.results[0].score) < 0 || isNaN(matchdata.results[0].score || 0)) {
        alert(lang.MATCH_VALID_SCORE1);
    }
    if (retval && parseInt('' + matchdata.results[1].score) < 0 || isNaN(matchdata.results[1].score || 0)) {
        alert(lang.MATCH_VALID_SCORE2);
    }
    matchdata.matchtype = auth.currentRanking;

    if (retval) {
        saveMatch(matchdata, auth.token)
            .then((data:any) => {
                if (data && data.success) {
                    emits('onSave');
                }
                else {
                    alert(lang.ERROR_MATCH_SAVE);
                }
                actionsDisabled.value = false;
            })
            .catch((e:any) => {
                console.log(e);
                alert(lang.ERROR_NETWORK);
                actionsDisabled.value = false;
            });
    }
    else {
        actionsDisabled.value = false;
    }
}

function update(fieldName:string, value: any)
{
    emits('onUpdate', {field: fieldName, value: value});
}

const player1 = ref(modelValue('player_1'));
const player2 = ref(modelValue('player_2'));
watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            player1.value = modelValue('player_1');
            player2.value = modelValue('player_2');
        }
    }
)

interface PlayerById {
    [key:number]: Player;
}

function modelValue(field:string)
{
    let players = validPlayers();
    let playerById:PlayerById = {};
    players.forEach((p:Player) => playerById[p.id] = p);

    let index = 0;
    if (field == 'player_2') {
        index = 1;
    }
    let pid = props.matchdata.results[index].player_id || 0;
    if (playerById[pid]) {
        return playerById[pid].name;
    }
    return '';
}

function validPlayers()
{
    return auth.playersList.filter((player) => {
        if (player.groupname && auth.currentGroup == 'all') return true;
        if (player.groupname == auth.currentGroup) return true;
        return false;
    }).sort((a:Player, b:Player) => {
        if (a.name == b.name) return a.id > b.id ? 1 : -1;
        return a.name > b.name ? 1 : -1;
    });
}

function querySearch (queryString: string, cb: any) {
    queryString = queryString.toLocaleLowerCase();
    const results = validPlayers().filter((p:Player) => p.name.toLowerCase().indexOf(queryString) === 0);
    cb(results.map((p) => { return {value: p.name, link: p} }));
}

function onBlurSelect(attribute:string)
{
    let val = attribute == 'player_1' ? player1.value : player2.value;
    let results = validPlayers().filter((p:Player) => p.name.toLowerCase() == val);
    if (results.length) {
        if (attribute == 'player_1') {
            update('player_1', results[0].id);
            player1.value = results[0].name;
        }
        else {
            update('player_2', results[0].id);
            player2.value = results[0].name;
        }
    }
}

import { ElDialog, ElForm, ElFormItem, ElInput, ElButton, ElAutocomplete } from 'element-plus';
</script>
<template>
    <ElDialog :model-value="props.visible" :title="lang.DIALOG_MATCH" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <ElForm>
        <ElFormItem :label="lang.PLAYER1">
            <ElAutocomplete
                v-model="player1"
                :fetch-suggestions="querySearch"
                :trigger-on-focus="false"
                :debounce="0"
                clearable
                :placeholder="lang.PICKPLAYER"
                @select="(p) => update('player_1', p.id)"
                @blur="() => onBlurSelect('player_1')"
            />
        </ElFormItem>
        <ElFormItem :label="lang.PLAYER2">
            <ElAutocomplete
                v-model="player2"
                :fetch-suggestions="querySearch"
                :trigger-on-focus="false"
                :debounce="0"
                clearable
                :placeholder="lang.PICKPLAYER"
                @select="(p) => update('player_1', p.id)"
                @blur="() => onBlurSelect('player_2')"
            />
        </ElFormItem>
        <ElFormItem :label="lang.SCORE1">
          <ElInput :model-value="props.matchdata.results[0].score || 0" @update:model-value="(e) => update('score_1', e)"/>
        </ElFormItem>
        <ElFormItem :label="lang.SCORE2">
          <ElInput :model-value="props.matchdata.results[1].score || 0" @update:model-value="(e) => update('score_2', e)"/>
        </ElFormItem>
        <ElFormItem :label="lang.MATCHDATE">
          <ElInput :model-value="props.matchdata.entered_at" @update:model-value="(e) => update('entered_at', e)"/>
        </ElFormItem>
      </ElForm>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="warning" :disabled="actionsDisabled" @click="removeMatch" v-if="is_valid(props.matchdata.id)">{{ lang.REMOVE }}</ElButton>
          <ElButton type="warning" :disabled="actionsDisabled" @click="closeForm">{{ lang.CANCEL }}</ElButton>
          <ElButton type="primary" :disabled="actionsDisabled" @click="submitForm">{{ lang.SAVE }}</ElButton>
        </span>
      </template>
    </ElDialog>
</template>