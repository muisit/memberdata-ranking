<script lang="ts" setup>
import { ref } from 'vue';
import lang from '@/lib/lang';

import { useAuthStore } from './stores/auth';
const props = defineProps<{
    nonce:string;
    url:string;
    group:string;
    ranking:string;
    token:string;
}>();

const auth = useAuthStore();
auth.nonce = props.nonce;
auth.baseUrl = props.url;
auth.token = props.token;
auth.currentGroup = props.group;
auth.currentRanking = props.ranking;
auth.isfrontend = true;
auth.getConfiguration().then(() => auth.getBasicSettings(auth.configuration.sheet || 0, auth.configuration.groupingfield || ''));

const tabindex = ref('matches');
import MatchView from './components/MatchView.vue';
import PlayersView from './components/PlayersView.vue';
import { ElTabs, ElTabPane } from 'element-plus';
</script>
<template>
    <ElTabs v-model="tabindex">
      <ElTabPane :label="lang.TAB_MATCHES" name="matches"><MatchView :visible="tabindex == 'matches'"/></ElTabPane>
      <ElTabPane :label="lang.TAB_RANKING" name="ranking"><PlayersView :visible="tabindex == 'ranking'"/></ElTabPane>
    </ElTabs>
</template>
