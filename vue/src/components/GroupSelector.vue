<script lang="ts" setup>
import { useAuthStore } from '@/stores/auth';
import lang from '@/lib/lang';
const auth = useAuthStore();

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <ElSelect v-model="auth.currentGroup">
        <ElOption value="all" :label="lang.ALL"/>
        <ElOption v-for="v in auth.configuration.validgroups" :key="v || 'none'" :value="v || 'none'" :label="v || 'None'" />
    </ElSelect>
</template>