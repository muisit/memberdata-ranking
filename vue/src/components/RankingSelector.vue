<script lang="ts" setup>
import { useAuthStore } from '@/stores/auth';
import lang from '@/lib/lang';
const auth = useAuthStore();

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <label>{{ lang.RANKINGSELECT }}</label>
    <ElSelect v-model="auth.currentRanking">
        <ElOption v-for="(v, index) in auth.rankattributes" :key="index" :value="v" :label="v" />
    </ElSelect>
</template>