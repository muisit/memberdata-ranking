<script lang="ts" setup>
import { watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import type { Player } from '@/lib/types';
import lang from '@/lib/lang';
const props = defineProps<{
    visible:boolean;
}>();

const auth = useAuthStore();

watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            if (!auth.groupingvalues?.includes(auth.currentGroup) && auth.groupingvalues?.length) {
                auth.currentGroup = 'all';
            }
            if (!auth.rankattributes.includes(auth.currentRanking) && auth.rankattributes.length) {
                auth.currentRanking = auth.rankattributes[0];
            }
            auth.getPlayers();
        }
    },
    { immediate: true}
)

function filterPlayers()
{
    var newlist = auth.playersList.filter((player:Player) => {
        var ranking = player.rankings[auth.currentRanking] || 0;
        // skip players without any ranking points yet
        if (ranking == 0) return false;
        if (auth.currentGroup == 'all' && player.groupname) return true;
        if (player.groupname == auth.currentGroup) return true;
        return false;
    }).sort((p1:Player, p2:Player) => {
        var rank1 = p1.rankings[auth.currentRanking] || 1000;
        var rank2 = p2.rankings[auth.currentRanking] || 1000;

        if (rank1 > rank2) return -1;
        if (rank1 < rank2) return 1;

        if (p1.name > p2.name) return 1;
        if (p1.name < p2.name) return -1;
        return p1.id > p2.id ? 1  :-1;
    });
    var currentPos = 0;
    var currentPoints = 999999;
    return newlist.map((e:Player,i:number) => {
        console.log(e,i);
        if (e.rankings[auth.currentRanking] < currentPoints) {
            currentPos = i + 1;
        }
        e.position = currentPos;
        currentPoints = e.rankings[auth.currentRanking];
        return e;
    });
}

import GroupSelector from './GroupSelector.vue';
import RankingSelector from './RankingSelector.vue';
</script>
<template>
    <div>
        <div class="grid-header">
            <GroupSelector />
            <RankingSelector />
        </div>
        <div class="grid">
            <span v-if="!auth.isfrontend">
                {{ lang.SHORTCODE }}:<br/>
                [memberdata-ranking-list group='{{  auth.currentGroup }}' type='{{ auth.currentRanking}}']<br/>
            </span>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ lang.NAME }}</th>
                        <th>{{ lang.GROUP }}</th>
                        <th>{{ lang.RANK }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="playerData in filterPlayers()" :key="playerData.id">
                        <td>{{ playerData.position }}</td>
                        <td>{{ playerData.name }}</td>
                        <td>{{ playerData.groupname }}</td>
                        <td>{{ playerData.rankings[auth.currentRanking] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>