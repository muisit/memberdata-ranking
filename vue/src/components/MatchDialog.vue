<script lang="ts" setup>
import { ref } from 'vue';
import { is_valid } from '@/lib/functions';
import type { Match } from '@/stores/auth';
import { saveMatch, removeMatch as removeMatchAPI } from '@/lib/api.js';
import { useAuthStore } from '@/stores/auth';
import { dayjs } from 'element-plus';
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
        if (confirm('Are you sure you want to remove this match permanently?')) {
            removeMatchAPI(props.matchdata).then(() => emits('onSave'));
        }
    }
}

function submitForm()
{
    let retval = true;
    if (!is_valid(props.matchdata.results[0].player_id)) {
        alert("Please pick a valid player for player 1");
        retval = false;
    }
    if (!is_valid(props.matchdata.results[1].player_id)) {
        alert("Please pick a valid player for player 2");
        retval = false;
    }
    if (retval && props.matchdata.results[0].player_id == props.matchdata.results[1].player_id) {
        alert("Person cannot score against him/herself");
        retval = false;
    }
    var dt = dayjs(props.matchdata.entered_at);
    if (!dt.isValid()) {
        alert("Please enter a valid date or date-time for the match");
        retval = false;
    }

    if (retval && parseInt('' + props.matchdata.results[0].score) < 0 || isNaN(props.matchdata.results[0].score || 0)) {
        alert("Please enter a valid score for player 1");
    }
    if (retval && parseInt('' + props.matchdata.results[1].score) < 0 || isNaN(props.matchdata.results[1].score || 0)) {
        alert("Please enter a valid score for player 2");
    }

    if (retval) {
        saveMatch(props.matchdata)
            .then((data:any) => {
                if (data && data.success) {
                    emits('onSave');
                }
                else {
                    alert("There was a problem storing this match's data. Please see if the data is allright and/or reload the page");
                }
            })
            .catch((e:any) => {
                console.log(e);
                alert("There was a network problem saving the data. Please try again or reload the page");
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
    }).sort((a, b) => {
        if (a.name == b.name) return a.id > b.id;
        return a.name > b.name;
    });
}

import { ElDialog, ElForm, ElFormItem, ElInput, ElButton, ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Edit Match Information" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <ElForm>
        {{ props.matchdata.results[0] }} / {{ props.matchdata.results[1] }} / {{ props.matchdata.results[0].player_id }}
        <ElFormItem label="Player 1">
          <ElSelect :model-value="modelValue('player_1')" @update:model-value="(e) => update('player_1', e)">
            <ElOption value="0" label="Pick a player"/>
            <ElOption v-for="player in validPlayers()" :key="player.id" :value="player.id" :label="player.name"/>
          </ElSelect>
        </ElFormItem>
        <ElFormItem label="Player 2">
          <ElSelect :model-value="modelValue('player_2')" @update:model-value="(e) => update('player_2', e)">
            <ElOption value="0" label="Pick a player"/>
            <ElOption v-for="player in validPlayers()" :key="player.id" :value="player.id" :label="player.name"/>
          </ElSelect>
        </ElFormItem>
        <ElFormItem label="Score Player 1">
          <ElInput :model-value="props.matchdata.results[0].score || 0" @update:model-value="(e) => update('score_1', e)"/>
        </ElFormItem>
        <ElFormItem label="Score Player 2">
          <ElInput :model-value="props.matchdata.results[1].score || 0" @update:model-value="(e) => update('score_2', e)"/>
        </ElFormItem>
        <ElFormItem label="Date">
          <ElInput :model-value="props.matchdata.entered_at" @update:model-value="(e) => update('entered_at', e)"/>
        </ElFormItem>
      </ElForm>
      <template #footer>
        <span class="dialog-footer">
          <ElButton type="warning" @click="removeMatch" v-if="is_valid(props.matchdata.id)">Remove</ElButton>
          <ElButton type="warning" @click="closeForm">Cancel</ElButton>
          <ElButton type="primary" @click="submitForm">Save</ElButton>
        </span>
      </template>
    </ElDialog>
</template>../lib/functions