<script lang="ts" setup>
import { ref } from 'vue';
import { useAuthStore } from './stores/auth';
import lang from '@/lib/lang.js';
const props = defineProps<{
    nonce:string;
    url:string;
}>();

const auth = useAuthStore();
auth.nonce = props.nonce;
auth.baseUrl = props.url;
auth.getConfiguration();

const tabindex = ref('matches');

import { ElTabs, ElTabPane } from 'element-plus';
import MatchView from './components/MatchView.vue';
import PlayersView from './components/PlayersView.vue';
import ConfigView from './components/ConfigView.vue';
</script>
<template>
  <div>
    <h1>{{ lang.ADMIN_PAGE }}</h1>
    <ElTabs v-model="tabindex">
      <ElTabPane :label="lang.TAB_MATCHES" name="matches"><MatchView :visible="tabindex == 'matches'"/></ElTabPane>
      <ElTabPane :label="lang.TAB_RANKING" name="ranking"><PlayersView :visible="tabindex == 'ranking'"/></ElTabPane>
      <ElTabPane :label="lang.TAB_SETTINGS" name="settings"><ConfigView :visible="tabindex == 'settings'"/></ElTabPane>
    </ElTabs>
  </div>
</template>
