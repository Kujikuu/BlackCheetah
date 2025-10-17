import{_ as v,V as y}from"./AppCombobox.vue_vue_type_script_setup_true_lang-DJkerDYB.js";import{d as V,r as c,g as b,o as g,m as a,au as d,w as P,f as s,b as l,t as p,e as n,v as _,az as A}from"./main-CA5_5-fk.js";import{b as D,a as w}from"./VList-Abz5zQko.js";import{V as x,a as r}from"./VRow-Ct-W2dAj.js";import{V as U}from"./VChip-C6OnuAx-.js";import{V as L}from"./VAvatar-DYIYaXHU.js";import{_ as k}from"./AppCardCode.vue_vue_type_style_index_0_lang-zHISpYb3.js";import"./form-bs2DZzDf.js";import"./VSelect-BuMSRPjV.js";import"./VTextField-tUni67cb.js";/* empty css                   */import"./VCounter-DEC0uZ17.js";import"./VImg-FsxkdVsl.js";import"./VField-8nFUoH-R.js";import"./easing-Bybner-F.js";import"./VInput-DhYwEXiV.js";import"./forwardRefs-C-GTDzx5.js";import"./dialog-transition-Dkald8FM.js";import"./VMenu-CPDfVQZz.js";import"./VOverlay-4pSpEGjx.js";import"./lazy-CN-iBTrZ.js";import"./scopeId-7q-wGPVf.js";import"./VCheckboxBtn-QLEgM55C.js";import"./VSelectionControl-DNfMVlT-.js";import"./filter-B6krjMCu.js";import"./ssrBoot-B8_MECfQ.js";import"./VDivider-Bv9wRwWR.js";/* empty css              */import"./VSlideGroup-CwmyEgo9.js";import"./vue3-perfect-scrollbar-D_rroTXd.js";import"./VCard-BlRUAaMY.js";import"./VCardText-A5LE6kSN.js";const R=V({__name:"DemoComboboxClearable",setup(f){const e=c(["Vuetify","Programming"]),m=["Programming","Design","Vue","Vuetify"];return(u,t)=>{const o=v;return g(),b(o,{modelValue:a(e),"onUpdate:modelValue":t[0]||(t[0]=i=>d(e)?e.value=i:null),items:m,label:"Combobox",multiple:"",placeholder:"deployment",clearable:""},null,8,["modelValue"])}}}),N=V({__name:"DemoComboboxNoDataWithChips",setup(f){const e=["Gaming","Programming","Vue","Vuetify"],m=c(["Vuetify"]),u=c(null);return P(m,t=>{t.length>5&&A(()=>m.value.pop())}),(t,o)=>{const i=v;return g(),b(i,{modelValue:a(m),"onUpdate:modelValue":o[0]||(o[0]=C=>d(m)?m.value=C:null),"search-input":a(u),"onUpdate:searchInput":o[1]||(o[1]=C=>d(u)?u.value=C:null),items:e,"hide-selected":"","hide-no-data":!1,placeholder:"deployment",hint:"Maximum of 5 tags",label:"Add some tags",multiple:"","persistent-hint":""},{"no-data":s(()=>[l(w,null,{default:s(()=>[l(D,null,{default:s(()=>[o[2]||(o[2]=p(' No results matching "')),n("strong",null,_(a(u)),1),o[3]||(o[3]=p('". Press ')),o[4]||(o[4]=n("kbd",null,"enter",-1)),o[5]||(o[5]=p(" to create a new one "))]),_:1,__:[2,3,4,5]})]),_:1})]),_:1},8,["modelValue","search-input"])}}}),T=V({__name:"DemoComboboxMultiple",setup(f){const e=c(["Vuetify","Programming"]),m=["Programming","Design","Vue","Vuetify"];return(u,t)=>{const o=v;return g(),b(x,null,{default:s(()=>[l(r,{cols:"12"},{default:s(()=>[l(o,{modelValue:a(e),"onUpdate:modelValue":t[0]||(t[0]=i=>d(e)?e.value=i:null),items:m,placeholder:"deployment",label:"Select a favorite activity or create a new one",multiple:""},null,8,["modelValue"])]),_:1}),l(r,{cols:"12"},{default:s(()=>[l(o,{modelValue:a(e),"onUpdate:modelValue":t[1]||(t[1]=i=>d(e)?e.value=i:null),items:m,placeholder:"deployment",label:"I use chips",multiple:"",chips:""},null,8,["modelValue"])]),_:1}),l(r,{cols:"12"},{default:s(()=>[l(o,{modelValue:a(e),"onUpdate:modelValue":t[2]||(t[2]=i=>d(e)?e.value=i:null),placeholder:"deployment",label:"I'm readonly",chips:"",multiple:"",readonly:""},null,8,["modelValue"])]),_:1}),l(r,{cols:"12"},{default:s(()=>[l(o,{modelValue:a(e),"onUpdate:modelValue":t[3]||(t[3]=i=>d(e)?e.value=i:null),items:m,placeholder:"deployment",label:"I use selection slot",multiple:""},{selection:s(({item:i})=>[l(U,{size:"small"},{prepend:s(()=>[l(L,{start:"",color:"primary",size:"16"},{default:s(()=>[p(_(String(i.title).charAt(0).toUpperCase()),1)]),_:2},1024)]),default:s(()=>[p(" "+_(i.title),1)]),_:2},1024)]),_:1},8,["modelValue"])]),_:1})]),_:1})}}}),$=V({__name:"DemoComboboxVariant",setup(f){const e=c(["Programming"]),m=["Programming","Design","Vue","Vuetify"];return(u,t)=>(g(),b(x,null,{default:s(()=>[l(r,{cols:"12"},{default:s(()=>[l(y,{modelValue:a(e),"onUpdate:modelValue":t[0]||(t[0]=o=>d(e)?e.value=o:null),items:m,multiple:"",placeholder:"deployment",variant:"solo",label:"solo"},null,8,["modelValue"])]),_:1}),l(r,{cols:"12"},{default:s(()=>[l(y,{modelValue:a(e),"onUpdate:modelValue":t[1]||(t[1]=o=>d(e)?e.value=o:null),multiple:"",items:m,placeholder:"deployment",variant:"outlined",label:"Outlined"},null,8,["modelValue"])]),_:1}),l(r,{cols:"12"},{default:s(()=>[l(y,{modelValue:a(e),"onUpdate:modelValue":t[2]||(t[2]=o=>d(e)?e.value=o:null),multiple:"",items:m,placeholder:"deployment",variant:"underlined",label:"Underlined"},null,8,["modelValue"])]),_:1}),l(r,{cols:"12"},{default:s(()=>[l(y,{modelValue:a(e),"onUpdate:modelValue":t[3]||(t[3]=o=>d(e)?e.value=o:null),multiple:"",items:m,placeholder:"deployment",variant:"filled",label:"Filled"},null,8,["modelValue"])]),_:1}),l(r,{cols:"12"},{default:s(()=>[l(y,{modelValue:a(e),"onUpdate:modelValue":t[4]||(t[4]=o=>d(e)?e.value=o:null),multiple:"",items:m,variant:"plain",placeholder:"deployment",label:"Plain"},null,8,["modelValue"])]),_:1})]),_:1}))}}),z=V({__name:"DemoComboboxDensity",setup(f){const e=c(["Vuetify","Programming"]),m=["Programming","Design","Vue","Vuetify"];return(u,t)=>{const o=v;return g(),b(o,{modelValue:a(e),"onUpdate:modelValue":t[0]||(t[0]=i=>d(e)?e.value=i:null),items:m,label:"Combobox",density:"compact",placeholder:"deployment",multiple:""},null,8,["modelValue"])}}}),S=V({__name:"DemoComboboxBasic",setup(f){const e=c("Programming"),m=["Programming","Design","Vue","Vuetify"];return(u,t)=>{const o=v;return g(),b(o,{modelValue:a(e),"onUpdate:modelValue":t[0]||(t[0]=i=>d(e)?e.value=i:null),items:m,placeholder:"deployment"},null,8,["modelValue"])}}}),j={ts:`<script lang="ts" setup>
const selectedItem = ref('Programming')
const items = ['Programming', 'Design', 'Vue', 'Vuetify']
<\/script>

<template>
  <AppCombobox
    v-model="selectedItem"
    :items="items"
    placeholder="deployment"
  />
</template>
`,js:`<script setup>
const selectedItem = ref('Programming')

const items = [
  'Programming',
  'Design',
  'Vue',
  'Vuetify',
]
<\/script>

<template>
  <AppCombobox
    v-model="selectedItem"
    :items="items"
    placeholder="deployment"
  />
</template>
`},B={ts:`<script lang="ts" setup>
const select = ref(['Vuetify', 'Programming'])
const items = ['Programming', 'Design', 'Vue', 'Vuetify']
<\/script>

<template>
  <AppCombobox
    v-model="select"
    :items="items"
    label="Combobox"
    multiple
    placeholder="deployment"
    clearable
  />
</template>
`,js:`<script setup>
const select = ref([
  'Vuetify',
  'Programming',
])

const items = [
  'Programming',
  'Design',
  'Vue',
  'Vuetify',
]
<\/script>

<template>
  <AppCombobox
    v-model="select"
    :items="items"
    label="Combobox"
    multiple
    placeholder="deployment"
    clearable
  />
</template>
`},M={ts:`<script lang="ts" setup>
const select = ref(['Vuetify', 'Programming'])
const items = ['Programming', 'Design', 'Vue', 'Vuetify']
<\/script>

<template>
  <AppCombobox
    v-model="select"
    :items="items"
    label="Combobox"
    density="compact"
    placeholder="deployment"
    multiple
  />
</template>
`,js:`<script setup>
const select = ref([
  'Vuetify',
  'Programming',
])

const items = [
  'Programming',
  'Design',
  'Vue',
  'Vuetify',
]
<\/script>

<template>
  <AppCombobox
    v-model="select"
    :items="items"
    label="Combobox"
    density="compact"
    placeholder="deployment"
    multiple
  />
</template>
`},W={ts:`<script lang="ts" setup>
const selectedItem = ref(['Vuetify', 'Programming'])
const items = ['Programming', 'Design', 'Vue', 'Vuetify']
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <AppCombobox
        v-model="selectedItem"
        :items="items"
        placeholder="deployment"
        label="Select a favorite activity or create a new one"
        multiple
      />
    </VCol>

    <VCol cols="12">
      <AppCombobox
        v-model="selectedItem"
        :items="items"
        placeholder="deployment"
        label="I use chips"
        multiple
        chips
      />
    </VCol>

    <VCol cols="12">
      <AppCombobox
        v-model="selectedItem"
        placeholder="deployment"
        label="I'm readonly"
        chips
        multiple
        readonly
      />
    </VCol>

    <VCol cols="12">
      <AppCombobox
        v-model="selectedItem"
        :items="items"
        placeholder="deployment"
        label="I use selection slot"
        multiple
      >
        <template #selection="{ item }">
          <VChip size="small">
            <template #prepend>
              <VAvatar
                start
                color="primary"
                size="16"
              >
                {{ String(item.title).charAt(0).toUpperCase() }}
              </VAvatar>
            </template>

            {{ item.title }}
          </VChip>
        </template>
      </AppCombobox>
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const selectedItem = ref([
  'Vuetify',
  'Programming',
])

const items = [
  'Programming',
  'Design',
  'Vue',
  'Vuetify',
]
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <AppCombobox
        v-model="selectedItem"
        :items="items"
        placeholder="deployment"
        label="Select a favorite activity or create a new one"
        multiple
      />
    </VCol>

    <VCol cols="12">
      <AppCombobox
        v-model="selectedItem"
        :items="items"
        placeholder="deployment"
        label="I use chips"
        multiple
        chips
      />
    </VCol>

    <VCol cols="12">
      <AppCombobox
        v-model="selectedItem"
        placeholder="deployment"
        label="I'm readonly"
        chips
        multiple
        readonly
      />
    </VCol>

    <VCol cols="12">
      <AppCombobox
        v-model="selectedItem"
        :items="items"
        placeholder="deployment"
        label="I use selection slot"
        multiple
      >
        <template #selection="{ item }">
          <VChip size="small">
            <template #prepend>
              <VAvatar
                start
                color="primary"
                size="16"
              >
                {{ String(item.title).charAt(0).toUpperCase() }}
              </VAvatar>
            </template>

            {{ item.title }}
          </VChip>
        </template>
      </AppCombobox>
    </VCol>
  </VRow>
</template>
`},F={ts:`<script lang="ts" setup>
const items = ['Gaming', 'Programming', 'Vue', 'Vuetify']
const selectedList = ref(['Vuetify'])
const search = ref(null)

watch(selectedList, value => {
  if (value.length > 5)
    nextTick(() => selectedList.value.pop())
})
<\/script>

<template>
  <AppCombobox
    v-model="selectedList"
    v-model:search-input="search"
    :items="items"
    hide-selected
    :hide-no-data="false"
    placeholder="deployment"
    hint="Maximum of 5 tags"
    label="Add some tags"
    multiple
    persistent-hint
  >
    <template #no-data>
      <VListItem>
        <VListItemTitle>
          No results matching "<strong>{{ search }}</strong>". Press <kbd>enter</kbd> to create a new one
        </VListItemTitle>
      </VListItem>
    </template>
  </AppCombobox>
</template>
`,js:`<script setup>
const items = [
  'Gaming',
  'Programming',
  'Vue',
  'Vuetify',
]

const selectedList = ref(['Vuetify'])
const search = ref(null)

watch(selectedList, value => {
  if (value.length > 5)
    nextTick(() => selectedList.value.pop())
})
<\/script>

<template>
  <AppCombobox
    v-model="selectedList"
    v-model:search-input="search"
    :items="items"
    hide-selected
    :hide-no-data="false"
    placeholder="deployment"
    hint="Maximum of 5 tags"
    label="Add some tags"
    multiple
    persistent-hint
  >
    <template #no-data>
      <VListItem>
        <VListItemTitle>
          No results matching "<strong>{{ search }}</strong>". Press <kbd>enter</kbd> to create a new one
        </VListItemTitle>
      </VListItem>
    </template>
  </AppCombobox>
</template>
`},G={ts:`<script lang="ts" setup>
const selectedItem = ref(['Programming'])
const items = ['Programming', 'Design', 'Vue', 'Vuetify']
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        :items="items"
        multiple
        placeholder="deployment"
        variant="solo"
        label="solo"
      />
    </VCol>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        multiple
        :items="items"
        placeholder="deployment"
        variant="outlined"
        label="Outlined"
      />
    </VCol>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        multiple
        :items="items"
        placeholder="deployment"
        variant="underlined"
        label="Underlined"
      />
    </VCol>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        multiple
        :items="items"
        placeholder="deployment"
        variant="filled"
        label="Filled"
      />
    </VCol>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        multiple
        :items="items"
        variant="plain"
        placeholder="deployment"
        label="Plain"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const selectedItem = ref(['Programming'])

const items = [
  'Programming',
  'Design',
  'Vue',
  'Vuetify',
]
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        :items="items"
        multiple
        placeholder="deployment"
        variant="solo"
        label="solo"
      />
    </VCol>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        multiple
        :items="items"
        placeholder="deployment"
        variant="outlined"
        label="Outlined"
      />
    </VCol>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        multiple
        :items="items"
        placeholder="deployment"
        variant="underlined"
        label="Underlined"
      />
    </VCol>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        multiple
        :items="items"
        placeholder="deployment"
        variant="filled"
        label="Filled"
      />
    </VCol>
    <VCol cols="12">
      <VCombobox
        v-model="selectedItem"
        multiple
        :items="items"
        variant="plain"
        placeholder="deployment"
        label="Plain"
      />
    </VCol>
  </VRow>
</template>
`},xe=V({__name:"combobox",setup(f){return(e,m)=>{const u=S,t=k,o=z,i=$,C=T,h=N,I=R;return g(),b(x,{class:"match-height"},{default:s(()=>[l(r,{cols:"12",md:"6"},{default:s(()=>[l(t,{title:"Basic",code:a(j)},{default:s(()=>[m[0]||(m[0]=n("p",null,"With Combobox, you can allow a user to create new values that may not be present in a provided items list.",-1)),l(u)]),_:1,__:[0]},8,["code"])]),_:1}),l(r,{cols:"12",md:"6"},{default:s(()=>[l(t,{title:"Density",code:a(M)},{default:s(()=>[m[1]||(m[1]=n("p",null,[p(" You can use "),n("code",null,"Density"),p(" prop to reduce combobox height and lower max height of list items. Available options are: "),n("code",null,"default"),p(", "),n("code",null,"comfortable"),p(", and "),n("code",null,"compact"),p(". ")],-1)),l(o)]),_:1,__:[1]},8,["code"])]),_:1}),l(r,{cols:"12",md:"6"},{default:s(()=>[l(t,{title:"Variant",code:a(G)},{default:s(()=>[m[2]||(m[2]=n("p",null,[p("Use "),n("code",null,"solo"),p(", "),n("code",null,"outlined"),p(", "),n("code",null,"underlined"),p(", "),n("code",null,"filled"),p(" and "),n("code",null,"plain"),p(" options of "),n("code",null,"variant"),p(" prop to change the look of combobox. ")],-1)),l(i)]),_:1,__:[2]},8,["code"])]),_:1}),l(r,{cols:"12",md:"6"},{default:s(()=>[l(t,{title:"Multiple",code:a(W)},{default:s(()=>[m[3]||(m[3]=n("p",null,"Previously known as tags - user is allowed to enter more than 1 value",-1)),l(C)]),_:1,__:[3]},8,["code"])]),_:1}),l(r,{cols:"12",md:"6"},{default:s(()=>[l(t,{title:"No data with chips",code:a(F)},{default:s(()=>[m[4]||(m[4]=n("p",null,"Previously known as tags - user is allowed to enter more than 1 value",-1)),l(h)]),_:1,__:[4]},8,["code"])]),_:1}),l(r,{cols:"12",md:"6"},{default:s(()=>[l(t,{title:"Clearable",code:a(B)},{default:s(()=>[m[5]||(m[5]=n("p",null,[p("Use "),n("code",null,"clearable"),p(" prop to clear combobox.")],-1)),l(I)]),_:1,__:[5]},8,["code"])]),_:1})]),_:1})}}});export{xe as default};
