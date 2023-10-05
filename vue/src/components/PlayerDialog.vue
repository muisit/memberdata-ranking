<script lang="ts" setup>
import { ref } from 'vue';
import { savePlayer, removePlayer } from '@/lib/api.js';
import { is_valid } from '@/lib/functions';

const props = defineProps<{
    visible:boolean;
    player:object;
}>();
const emits = defineEmits(['onClose', 'onUpdate']);

function closeForm()
{
    emits('onClose');
}

function submitForm()
{
    return savePlayer(props.player)
        .then((data) => {
            if(data.data) {
                // update fields to account for back-office field validation changes
                update('id', data.data.id);
                update('rank', data.data.rank);
                closeForm();
            }
        })
        .catch((e) => {
            if (e.cause && e.cause.messages) {
                var message = e.cause.messages;
                if (message.join) message = message.join('\r\n');
                alert('Validation errors were encountered: ' + message);
            }
            else {
                alert("There was an error storing the data. Please try again");
            }
        });
}

function update(fieldName:string, value: any)
{
    emits('onUpdate', {field: fieldName, value: value});
}

function remove()
{
    if(is_valid(props.player.id)) {
        if (confirm("Please confirm deleting this player from the database")) {
            removePlayer(props.player).then((data) => {
                alert("Player removed");
                closeForm();
            })
        }
    }
}

import { ElDialog, ElForm, ElFormItem, ElInput, ElButton } from 'element-plus';
</script>
<template>
    <ElDialog :model-value="props.visible" title="Edit Player Information" :close-on-click-modal="false"  :before-close="(done) => { closeForm(); done(false); }">
      <ElForm>
        <ElFormItem label="Name">
          <ElInput :model-value="props.player.name" @update:model-value="(e) => update('name', e)"/>
        </ElFormItem>
        <ElFormItem label="Group">
          <ElInput :model-value="props.player.groupname" @update:model-value="(e) => update('groupname', e)"/>
        </ElFormItem>
        <ElFormItem label="Rank">
          {{ props.player.rank}}
        </ElFormItem>
      </ElForm>
      <template #footer>
        <span class="dialog-footer">
          <ElButton v-if="is_valid(props.player.id)" type="warning" @click="remove">Remove</ElButton>
          <ElButton type="warning" @click="closeForm">Cancel</ElButton>
          <ElButton type="primary" @click="submitForm">Save</ElButton>
        </span>
      </template>
    </ElDialog>
</template>../lib/api.js../lib/functions.js