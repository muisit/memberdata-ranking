<script lang="ts" setup>
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import type { Player, FieldDefinition } from '@/stores/auth';
const props = defineProps<{
    visible:boolean;
}>();

const auth = useAuthStore();
const player:Ref<Player> = ref({id: 0, name: '', rank: 1000});
const visible = ref(false);

watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            if (!auth.configuration.groupingvalues?.includes(auth.currentGroup) && auth.configuration.groupingvalues.length) {
                auth.currentGroup = 'all';
            }
            auth.getPlayers();
        }
    },
    { immediate: true}
)

function onClose()
{
    visible.value = false;
    auth.updatePlayerInList(player.value);
    auth.sortPlayers('gRni');
}

function onUpdate(fieldDef:FieldDefinition)
{
}

function filterPlayers()
{
    return auth.playersList.filter((player) => {
        if (auth.currentGroup == 'all' && player.groupname) return true;
        if (player.groupname == auth.currentGroup) return true;
        return false;
    })
}

import GroupSelector from './GroupSelector.vue';
import PlayerDialog from './PlayerDialog.vue';
import { ElButton, ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div>
        <div class="grid-header">
            <GroupSelector />
        </div>
        <div class="grid">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Group</th>
                        <th>Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="playerData in filterPlayers()" :key="playerData.id" @dblclick="() => { player = playerData; visible = true;}">
                        <td>{{ playerData.name }}</td>
                        <td>{{ playerData.groupname }}</td>
                        <td>{{ playerData.rank }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <PlayerDialog :player="player" :visible="visible" @on-close="onClose" @on-update="onUpdate" />
    </div>
</template>