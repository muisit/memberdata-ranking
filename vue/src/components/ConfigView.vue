<script lang="ts" setup>
import { watch, ref } from 'vue';
import { saveconfiguration } from '@/lib/api.js';
import { useAuthStore } from '@/stores/auth';
const props = defineProps<{
    visible:boolean;
}>();

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
        alert('Configuration saved');
    });
}
import { ElButton, ElInput, ElSelect, ElOption, ElCheckbox } from 'element-plus';
</script>
<template>
  <div>
    <p>
      The formula used is as follows. Given a match between two participants A and B, scoring Pa points (participant A) and Pb points (participant B),
      and given the original ranking Ra and Rb and the configuration values below:<br/>
      &nbsp;&nbsp;R'a = Ra + K*(Sa - Ea)<br/>
      &nbsp;&nbsp;Sa = (Pa + Va) / (Pa + Pb + 1), with Va = 1 if A wins and Va = 0 if A loses<br/>
      &nbsp;&nbsp;Ea = Qa / (Qa + Qb)<br/>
      &nbsp;&nbsp;Qa = 10<sup>(Ra/c)</sup><br/>
      &nbsp;&nbsp;Qb = 10<sup>(Rb/c)</sup><br/>
    </p>
    <p>Please adjust the configuration settings as you seem fit.</p>
    <table>
      <tr>
        <td valign="top">Rank base</td>
        <td valign="top">
          <ElInput :model-value="auth.configuration.base_rank" @update:model-value="(e) => auth.configuration.base_rank = e"/>
        </td>
        <td>The base rank is the starting value for new participants. Default value of 1000.
        </td>
      </tr>
      <tr>
        <td valign="top">K-value</td>
        <td valign="top">
          <ElInput :model-value="auth.configuration.k_value" @update:model-value="(e) => auth.configuration.k_value = e"/>
        </td>
        <td>The K-value determines the amount of points a participant receives or loses at most when they win a match. This number is
          adjusted according to the actual result with respect to the expected result. The default value is 32.
        </td>
      </tr>
      <tr>
        <td valign="top">C-value</td>
        <td valign="top">
          <ElInput :model-value="auth.configuration.c_value" @update:model-value="(e) => auth.configuration.c_value = e"/>
        </td>
        <td>The C-value determines the factor with which expectations are compared. Large C values make it more difficult to determine
          an expected score difference between two fencers (everyone seems to be of more or less the same rank),
          whereas small C-values exagerate small ranking differences and will cause people
          to continuously switch after entering match results. The default value is 400.
        </td>
      </tr>
      <tr><td colspan="2"><hr></td></tr>
      <tr>
        <td valign="top">Name</td>
        <td valign="top">
          <ElSelect :model-value="auth.configuration.namefield" @update:model-value="(e) => auth.configuration.namefield = e">
            <ElOption v-for="name in auth.configuration.attributes" :key="name" :label="name" :value="name"/>
          </ElSelect>
        </td>
        <td>Select the attribute to use as name field</td>
      </tr>
      <tr>
        <td valign="top">Group</td>
        <td valign="top">
          <ElSelect :model-value="auth.configuration.groupingfield" @update:model-value="(e) => auth.configuration.groupingfield = e">
            <ElOption label="No grouping" value="none"/>
            <ElOption v-for="name in auth.configuration.attributes" :key="name" :label="name" :value="name"/>
          </ElSelect>
        </td>
        <td>Select the attribute to use as a grouping field</td>
      </tr>
      <tr>
        <td valign="top">Values</td>
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
        <td>Select the valid groups</td>
      </tr>
    </table>
    <div class="save-button">
      <ElButton type="primary" @click="saveConfig">Save</ElButton>
    </div>
  </div>
</template>
../lib/api.js