<script lang="ts" setup>
import { watch, ref } from 'vue';
import { saveconfiguration } from '@/lib/api.js';
import { useAuthStore } from '@/stores/auth';
const props = defineProps<{
    visible:boolean;
}>();
import lang from '@/lib/lang.js';

const auth = useAuthStore();

watch(
    () => props.visible,
    (nw) => {
        if (nw) {
            auth.getConfiguration();
        }
    },
    { immediate: true }
)

function markValidGroup(onoff:boolean, groupname:string)
{
    console.log(onoff, groupname);
    if (onoff && !auth.configuration.validgroups.includes(groupname)) {
        auth.configuration.validgroups?.push(groupname);
    }
    else if(!onoff && auth.configuration.validgroups.includes(groupname)) {
        auth.configuration.validgroups = auth.configuration.validgroups.filter((v) => v != groupname);
    }
}

function saveConfig()
{
    saveconfiguration(auth.configuration).then((data:any) => {
        auth.configuration = data.data;
        alert(lang.CONFIG_SAVED);
    });
}
import { ElButton, ElInput, ElSelect, ElOption, ElCheckbox } from 'element-plus';
</script>
<template>
  <div>
    <p>
      {{ lang.DESCR1 }}<br/>
      &nbsp;&nbsp;R'a = Ra + K*(Sa - Ea)<br/>
      &nbsp;&nbsp;Sa = (Pa + Va) / (Pa + Pb + 1), {{ lang.SA_INFO }}<br/>
      &nbsp;&nbsp;Ea = Qa / (Qa + Qb)<br/>
      &nbsp;&nbsp;Qa = 10<sup>(Ra/c)</sup><br/>
      &nbsp;&nbsp;Qb = 10<sup>(Rb/c)</sup><br/>
    </p>
    <p>{{ lang.PLEASEADJUST }}</p>
    <table>
      <tr>
        <td valign="top">{{ lang.BASERANK }}</td>
        <td valign="top">
          <ElInput :model-value="auth.configuration.base_rank" @update:model-value="(e) => auth.configuration.base_rank = e"/>
        </td>
        <td>{{ lang.BASERANK_INFO }}
        </td>
      </tr>
      <tr>
        <td valign="top">{{ lang.KVALUE }}</td>
        <td valign="top">
          <ElInput :model-value="auth.configuration.k_value" @update:model-value="(e) => auth.configuration.k_value = e"/>
        </td>
        <td>{{  lang.KVALUE_INFO }}
        </td>
      </tr>
      <tr>
        <td valign="top">{{ lang.CVALUE }}</td>
        <td valign="top">
          <ElInput :model-value="auth.configuration.c_value" @update:model-value="(e) => auth.configuration.c_value = e"/>
        </td>
        <td>{{ lang.CVALUE_INFO }}
        </td>
      </tr>
      <tr><td colspan="2"><hr></td></tr>
      <tr>
        <td valign="top">{{ lang.NAME }}</td>
        <td valign="top">
          <ElSelect :model-value="auth.configuration.namefield" @update:model-value="(e) => auth.configuration.namefield = e">
            <ElOption v-for="name in auth.configuration.attributes" :key="name" :label="name" :value="name"/>
          </ElSelect>
        </td>
        <td>{{ lang.NAME_INFO }}</td>
      </tr>
      <tr>
        <td valign="top">{{ lang.GROUP }}</td>
        <td valign="top">
          <ElSelect :model-value="auth.configuration.groupingfield" @update:model-value="(e) => auth.configuration.groupingfield = e">
            <ElOption label="No grouping" value="none"/>
            <ElOption v-for="name in auth.configuration.attributes" :key="name" :label="name" :value="name"/>
          </ElSelect>
        </td>
        <td>{{ lang.GROUP_INFO }}</td>
      </tr>
      <tr>
        <td valign="top">{{  lang.GROUPS }}</td>
        <td valign="top">
          <div v-for="name in auth.configuration.groupingvalues" :key="name" class="selectlist">
              <ElCheckbox 
                v-if="name != null"
                :model-value="auth.configuration.validgroups?.includes(name || 'none')"
                @update:model-value="(e) => markValidGroup(e, name || 'none')"
              />
              <span v-if="name != null">{{ name || 'None' }}</span>
          </div>
        </td>
        <td>{{ lang.GROUPS_INFO }}</td>
      </tr>
    </table>
    <div class="save-button">
      <ElButton type="primary" @click="saveConfig">{{  lang.SAVE }}</ElButton>
    </div>
  </div>
</template>
