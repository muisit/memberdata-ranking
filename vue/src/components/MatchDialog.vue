<script lang="ts" setup>
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

function closeForm()
{
    emits('onClose');
}

function removeMatch()
{
    if (is_valid(props.matchdata.id)) {
        if (confirm(lang.CONFIRM_DELETE_MATCH)) {
            removeMatchAPI(props.matchdata).then(() => emits('onSave'));
        }
    }
}

function submitForm()
{
    let retval = true;
    if (!is_valid(props.matchdata.results[0].player_id)) {
        alert(lang.MATCH_VALID_PLAYER1);
        retval = false;
    }
    if (!is_valid(props.matchdata.results[1].player_id)) {
        alert(lang.MATCH_VALID_PLAYER2);
        retval = false;
    }
    if (retval && props.matchdata.results[0].player_id == props.matchdata.results[1].player_id) {
        alert(lang.MATCH_NO_DOUBLE_PLAYER);
        retval = false;
    }
    var dt = dayjs(props.matchdata.entered_at);
    if (!dt.isValid()) {
        alert(lang.MATCH_VALID_DATE);
        retval = false;
    }

    if (retval && parseInt('' + props.matchdata.results[0].score) < 0 || isNaN(props.matchdata.results[0].score || 0)) {
        alert(lang.MATCH_VALID_SCORE1);
    }
    if (retval && parseInt('' + props.matchdata.results[1].score) < 0 || isNaN(props.matchdata.results[1].score || 0)) {
        alert(lang.MATCH_VALID_SCORE2);
    }

    if (retval) {
        saveMatch(props.matchdata)
            .then((data:any) => {
                if (data && data.success) {
                    emits('onSave');
                }
                else {
                    alert(lang.ERROR_MATCH_SAVE);
                }
            })
            .catch((e:any) => {
                console.log(e);
                alert(lang.ERROR_NETWORK);
            });
    }
}

function update(fieldName:string, value: any)
{
    emits('onUpdate', {field: fieldName, value: value});
}

function modelValue(field:string)
{
    if (field == 'player_1') {
        return '' + props.matchdata.results[0].player_id;
    }
    return '' + props.matchdata.results[1].player_id;
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

import { ElDialog, ElForm, ElFormItem, ElInput, ElButton, ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <ElDialog :model-value="props.visible" :title="lang.DIALOG_MATCH" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <ElForm>
        <ElFormItem :label="lang.PLAYER1">
          <ElSelect :model-value="modelValue('player_1')" @update:model-value="(e) => update('player_1', e)">
            <ElOption value="0" :label="lang.PICKPLAYER"/>
            <ElOption v-for="player in validPlayers()" :key="player.id" :value="player.id" :label="player.name"/>
          </ElSelect>
        </ElFormItem>
        <ElFormItem :label="lang.PLAYER2">
          <ElSelect :model-value="modelValue('player_2')" @update:model-value="(e) => update('player_2', e)">
            <ElOption value="0" :label="lang.PICKPLAYER"/>
            <ElOption v-for="player in validPlayers()" :key="player.id" :value="player.id" :label="player.name"/>
          </ElSelect>
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
          <ElButton type="warning" @click="removeMatch" v-if="is_valid(props.matchdata.id)">{{ lang.REMOVE }}</ElButton>
          <ElButton type="warning" @click="closeForm">{{ lang.CANCEL }}</ElButton>
          <ElButton type="primary" @click="submitForm">{{ lang.SAVE }}</ElButton>
        </span>
      </template>
    </ElDialog>
</template>@/lib/lang.js