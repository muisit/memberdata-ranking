<script lang="ts" setup>
import { useAuthStore } from '@/stores/auth';
import lang from '@/lib/lang';
const auth = useAuthStore();

import { ElSelect, ElOption } from 'element-plus';
</script>
<template>
    <div>
        <label for="groupselector">{{ lang.GROUPSELECT }}</label>
        <ElSelect v-model="auth.currentGroup" id="groupselector">
            <ElOption value="all" :label="lang.ALL"/>
            <ElOption v-for="v in auth.configuration.validgroups" :key="v || 'none'" :value="v || 'none'" :label="v || lang.NONE" />
        </ElSelect>
    </div>        
</template>