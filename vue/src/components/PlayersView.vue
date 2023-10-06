<script lang="ts" setup>
import { watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import lang from '@/lib/lang';
const props = defineProps<{
    visible:boolean;
}>();

const auth = useAuthStore();

watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            if (!auth.configuration.groupingvalues?.includes(auth.currentGroup) && auth.configuration.groupingvalues?.length) {
                auth.currentGroup = 'all';
            }
            auth.getPlayers();
        }
    },
    { immediate: true}
)

function filterPlayers()
{
    return auth.playersList.filter((player) => {
        if (auth.currentGroup == 'all' && player.groupname) return true;
        if (player.groupname == auth.currentGroup) return true;
        return false;
    })
}

import GroupSelector from './GroupSelector.vue';
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
                        <th>{{ lang.NAME }}</th>
                        <th>{{ lang.GROUP }}</th>
                        <th>{{ lang.RANK }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="playerData in filterPlayers()" :key="playerData.id">
                        <td>{{ playerData.name }}</td>
                        <td>{{ playerData.groupname }}</td>
                        <td>{{ playerData.rank }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>