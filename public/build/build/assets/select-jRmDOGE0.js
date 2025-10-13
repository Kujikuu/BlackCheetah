import{_}from"./AppSelect.vue_vue_type_script_setup_true_lang-DpwmV3pY.js";import{d,r as f,g as u,o as S,b7 as I,b8 as B,b9 as w,ba as x,bb as O,m as r,au as v,f as a,b as e,e as s,v as k,t as i}from"./main-DeJzYu26.js";import{V as y}from"./VAvatar-CzFSvsQH.js";import{V as G}from"./VChip-C0NN1uvc.js";import{V as C,a as n}from"./VRow-Bc5Xg-OF.js";import{V as A}from"./VSelect-BwgOzOKs.js";import{_ as $}from"./AppCardCode.vue_vue_type_style_index_0_lang-HxxfEnhz.js";import"./form-BflVDgbn.js";import"./VImg-BGIMQfo8.js";import"./VSlideGroup-DsvfnNWq.js";/* empty css              */import"./VTextField-DotXvQxm.js";/* empty css                   */import"./VCounter-BpCv2xWf.js";import"./VField-BfX_Zcm2.js";import"./easing-Bybner-F.js";import"./VInput-D8-eiKxF.js";import"./forwardRefs-C-GTDzx5.js";import"./VList-DB22MoGh.js";import"./ssrBoot-BhA_RNt5.js";import"./VDivider-DLIyMVwP.js";import"./dialog-transition-DrC3XplS.js";import"./VMenu-B1FnI2KF.js";import"./VOverlay-Cf3_uLuL.js";import"./lazy-CbFk89iz.js";import"./scopeId-DjrbA9fE.js";import"./VCheckboxBtn-BguezhSu.js";import"./VSelectionControl-CzzB1Mlt.js";import"./vue3-perfect-scrollbar-BTBA_leG.js";import"./VCard-DM_RaJjQ.js";import"./VCardText-C91zLtFF.js";const N=d({__name:"DemoSelectSelectionSlot",setup(b){const l=[{name:"Sandra Adams",avatar:I},{name:"Ali Connors",avatar:B},{name:"Trevor Hansen",avatar:w},{name:"Tucker Smith",avatar:x},{name:"Britta Holt",avatar:O}],t=f(["Sandra Adams"]);return(m,o)=>{const p=_;return S(),u(p,{modelValue:r(t),"onUpdate:modelValue":o[0]||(o[0]=c=>v(t)?t.value=c:null),items:l,"item-title":"name","item-value":"name",label:"Select Item",placeholder:"Select Item",multiple:"",clearable:"","clear-icon":"tabler-x"},{selection:a(({item:c})=>[e(G,null,{prepend:a(()=>[e(y,{start:"",image:c.raw.avatar},null,8,["image"])]),default:a(()=>[s("span",null,k(c.title),1)]),_:2},1024)]),_:1},8,["modelValue"])}}}),j=d({__name:"DemoSelectMultiple",setup(b){const l=f(["Alabama"]),t=["Alabama","Alaska","American Samoa","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","District of Columbia","Federated States of Micronesia","Florida","Georgia","Guam"];return(m,o)=>{const p=_;return S(),u(p,{modelValue:r(l),"onUpdate:modelValue":o[0]||(o[0]=c=>v(l)?l.value=c:null),items:t,"menu-props":{maxHeight:"400"},label:"Select",multiple:"","persistent-hint":"",placeholder:"Select State"},null,8,["modelValue"])}}}),T=d({__name:"DemoSelectMenuProps",setup(b){const l=["Foo","Bar","Fizz","Buzz"];return(t,m)=>{const o=_;return S(),u(o,{items:l,"menu-props":{transition:"scroll-y-transition"},label:"Label",placeholder:"Select Item"})}}}),U=d({__name:"DemoSelectChips",setup(b){const l=["foo","bar","fizz","buzz"],t=f(["foo","bar","fizz","buzz"]);return(m,o)=>{const p=_;return S(),u(p,{modelValue:r(t),"onUpdate:modelValue":o[0]||(o[0]=c=>v(t)?t.value=c:null),items:l,placeholder:"Select Item",label:"Chips",chips:"",multiple:"","closable-chips":""},null,8,["modelValue"])}}}),M=d({__name:"DemoSelectIcons",setup(b){const l=f("Florida"),t=f("Texas"),m=["Alabama","Alaska","American Samoa","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","District of Columbia","Federated States of Micronesia","Florida","Georgia","Guam"];return(o,p)=>{const c=_;return S(),u(C,null,{default:a(()=>[e(n,{cols:"12"},{default:a(()=>[e(c,{modelValue:r(l),"onUpdate:modelValue":p[0]||(p[0]=V=>v(l)?l.value=V:null),items:m,label:"Select","prepend-icon":"tabler-map","single-line":"",variant:"filled",placeholder:"Select State"},null,8,["modelValue"])]),_:1}),e(n,{cols:"12"},{default:a(()=>[e(c,{modelValue:r(t),"onUpdate:modelValue":p[1]||(p[1]=V=>v(t)?t.value=V:null),items:m,"append-icon":"tabler-map",label:"Select","single-line":"",variant:"filled",placeholder:"Select State"},null,8,["modelValue"])]),_:1})]),_:1})}}}),R=d({__name:"DemoSelectCustomTextAndValue",setup(b){const l=f({state:"Florida",abbr:"FL"}),t=[{state:"Florida",abbr:"FL"},{state:"Georgia",abbr:"GA"},{state:"Nebraska",abbr:"NE"},{state:"California",abbr:"CA"},{state:"New York",abbr:"NY"}];return(m,o)=>{const p=_;return S(),u(p,{modelValue:r(l),"onUpdate:modelValue":o[0]||(o[0]=c=>v(l)?l.value=c:null),hint:`${r(l).state}, ${r(l).abbr}`,items:t,"item-title":"state","item-value":"abbr",label:"Select","persistent-hint":"","return-object":"","single-line":"",placeholder:"Select State"},null,8,["modelValue","hint"])}}}),H=d({__name:"DemoSelectVariant",setup(b){const l=["Foo","Bar","Fizz","Buzz"];return(t,m)=>(S(),u(C,null,{default:a(()=>[e(n,{cols:"12",sm:"6"},{default:a(()=>[e(A,{items:l,label:"Outlined",placeholder:"Select Item"})]),_:1}),e(n,{cols:"12",sm:"6"},{default:a(()=>[e(A,{items:l,label:"Filled",placeholder:"Select Item",variant:"filled"})]),_:1}),e(n,{cols:"12",sm:"6"},{default:a(()=>[e(A,{items:l,label:"Solo",placeholder:"Select Item",variant:"solo"})]),_:1}),e(n,{cols:"12",sm:"6"},{default:a(()=>[e(A,{items:l,label:"Plain",placeholder:"Select Item",variant:"plain"})]),_:1}),e(n,{cols:"12",sm:"6"},{default:a(()=>[e(A,{items:l,label:"Underlined",variant:"underlined",placeholder:"Select Item"})]),_:1})]),_:1}))}}),L=d({__name:"DemoSelectDensity",setup(b){const l=["Foo","Bar","Fizz","Buzz"];return(t,m)=>{const o=_;return S(),u(o,{items:l,label:"Density",density:"compact",placeholder:"Select Item"})}}}),P=d({__name:"DemoSelectBasic",setup(b){const l=["Foo","Bar","Fizz","Buzz"];return(t,m)=>{const o=_;return S(),u(o,{items:l,label:"Standard",placeholder:"Select Item"})}}}),Y={ts:`<script lang="ts" setup>
const items = ['Foo', 'Bar', 'Fizz', 'Buzz']
<\/script>

<template>
  <AppSelect
    :items="items"
    label="Standard"
    placeholder="Select Item"
  />
</template>
`,js:`<script setup>
const items = [
  'Foo',
  'Bar',
  'Fizz',
  'Buzz',
]
<\/script>

<template>
  <AppSelect
    :items="items"
    label="Standard"
    placeholder="Select Item"
  />
</template>
`},E={ts:`<script lang="ts" setup>
const items = ['foo', 'bar', 'fizz', 'buzz']
const selected = ref(['foo', 'bar', 'fizz', 'buzz'])
<\/script>

<template>
  <AppSelect
    v-model="selected"
    :items="items"
    placeholder="Select Item"
    label="Chips"
    chips
    multiple
    closable-chips
  />
</template>
`,js:`<script setup>
const items = [
  'foo',
  'bar',
  'fizz',
  'buzz',
]

const selected = ref([
  'foo',
  'bar',
  'fizz',
  'buzz',
])
<\/script>

<template>
  <AppSelect
    v-model="selected"
    :items="items"
    placeholder="Select Item"
    label="Chips"
    chips
    multiple
    closable-chips
  />
</template>
`},q={ts:`<script lang="ts" setup>
const selectedOption = ref({ state: 'Florida', abbr: 'FL' })

const items = [
  { state: 'Florida', abbr: 'FL' },
  { state: 'Georgia', abbr: 'GA' },
  { state: 'Nebraska', abbr: 'NE' },
  { state: 'California', abbr: 'CA' },
  { state: 'New York', abbr: 'NY' },
]
<\/script>

<template>
  <AppSelect
    v-model="selectedOption"
    :hint="\`\${selectedOption.state}, \${selectedOption.abbr}\`"
    :items="items"
    item-title="state"
    item-value="abbr"
    label="Select"
    persistent-hint
    return-object
    single-line
    placeholder="Select State"
  />
</template>
`,js:`<script setup>
const selectedOption = ref({
  state: 'Florida',
  abbr: 'FL',
})

const items = [
  {
    state: 'Florida',
    abbr: 'FL',
  },
  {
    state: 'Georgia',
    abbr: 'GA',
  },
  {
    state: 'Nebraska',
    abbr: 'NE',
  },
  {
    state: 'California',
    abbr: 'CA',
  },
  {
    state: 'New York',
    abbr: 'NY',
  },
]
<\/script>

<template>
  <AppSelect
    v-model="selectedOption"
    :hint="\`\${selectedOption.state}, \${selectedOption.abbr}\`"
    :items="items"
    item-title="state"
    item-value="abbr"
    label="Select"
    persistent-hint
    return-object
    single-line
    placeholder="Select State"
  />
</template>
`},J={ts:`<script lang="ts" setup>
const items = ['Foo', 'Bar', 'Fizz', 'Buzz']
<\/script>

<template>
  <AppSelect
    :items="items"
    label="Density"
    density="compact"
    placeholder="Select Item"
  />
</template>
`,js:`<script setup>
const items = [
  'Foo',
  'Bar',
  'Fizz',
  'Buzz',
]
<\/script>

<template>
  <AppSelect
    :items="items"
    label="Density"
    density="compact"
    placeholder="Select Item"
  />
</template>
`},K={ts:`<script lang="ts" setup>
const selectedOption1 = ref('Florida')
const selectedOption2 = ref('Texas')

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
]
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <AppSelect
        v-model="selectedOption1"
        :items="states"
        label="Select"
        prepend-icon="tabler-map"
        single-line
        variant="filled"
        placeholder="Select State"
      />
    </VCol>

    <VCol cols="12">
      <AppSelect
        v-model="selectedOption2"
        :items="states"
        append-icon="tabler-map"
        label="Select"
        single-line
        variant="filled"
        placeholder="Select State"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const selectedOption1 = ref('Florida')
const selectedOption2 = ref('Texas')

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
]
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <AppSelect
        v-model="selectedOption1"
        :items="states"
        label="Select"
        prepend-icon="tabler-map"
        single-line
        variant="filled"
        placeholder="Select State"
      />
    </VCol>

    <VCol cols="12">
      <AppSelect
        v-model="selectedOption2"
        :items="states"
        append-icon="tabler-map"
        label="Select"
        single-line
        variant="filled"
        placeholder="Select State"
      />
    </VCol>
  </VRow>
</template>
`},Q={ts:`<script lang="ts" setup>
const items = ['Foo', 'Bar', 'Fizz', 'Buzz']
<\/script>

<template>
  <AppSelect
    :items="items"
    :menu-props="{ transition: 'scroll-y-transition' }"
    label="Label"
    placeholder="Select Item"
  />
</template>
`,js:`<script setup>
const items = [
  'Foo',
  'Bar',
  'Fizz',
  'Buzz',
]
<\/script>

<template>
  <AppSelect
    :items="items"
    :menu-props="{ transition: 'scroll-y-transition' }"
    label="Label"
    placeholder="Select Item"
  />
</template>
`},W={ts:`<script lang="ts" setup>
const selectedOptions = ref(['Alabama'])

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
]
<\/script>

<template>
  <AppSelect
    v-model="selectedOptions"
    :items="states"
    :menu-props="{ maxHeight: '400' }"
    label="Select"
    multiple
    persistent-hint
    placeholder="Select State"
  />
</template>
`,js:`<script setup>
const selectedOptions = ref(['Alabama'])

const states = [
  'Alabama',
  'Alaska',
  'American Samoa',
  'Arizona',
  'Arkansas',
  'California',
  'Colorado',
  'Connecticut',
  'Delaware',
  'District of Columbia',
  'Federated States of Micronesia',
  'Florida',
  'Georgia',
  'Guam',
]
<\/script>

<template>
  <AppSelect
    v-model="selectedOptions"
    :items="states"
    :menu-props="{ maxHeight: '400' }"
    label="Select"
    multiple
    persistent-hint
    placeholder="Select State"
  />
</template>
`},X={ts:`<script lang="ts" setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar5 from '@images/avatars/avatar-5.png'

const items: { name: string; avatar: string }[] = [
  { name: 'Sandra Adams', avatar: avatar1 },
  { name: 'Ali Connors', avatar: avatar2 },
  { name: 'Trevor Hansen', avatar: avatar3 },
  { name: 'Tucker Smith', avatar: avatar4 },
  { name: 'Britta Holt', avatar: avatar5 },
]

const value = ref(['Sandra Adams'])
<\/script>

<template>
  <AppSelect
    v-model="value"
    :items="items"
    item-title="name"
    item-value="name"
    label="Select Item"
    placeholder="Select Item"
    multiple
    clearable
    clear-icon="tabler-x"
  >
    <template #selection="{ item }">
      <VChip>
        <template #prepend>
          <VAvatar
            start
            :image="item.raw.avatar"
          />
        </template>

        <span>{{ item.title }}</span>
      </VChip>
    </template>
  </AppSelect>
</template>
`,js:`<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'
import avatar5 from '@images/avatars/avatar-5.png'

const items = [
  {
    name: 'Sandra Adams',
    avatar: avatar1,
  },
  {
    name: 'Ali Connors',
    avatar: avatar2,
  },
  {
    name: 'Trevor Hansen',
    avatar: avatar3,
  },
  {
    name: 'Tucker Smith',
    avatar: avatar4,
  },
  {
    name: 'Britta Holt',
    avatar: avatar5,
  },
]

const value = ref(['Sandra Adams'])
<\/script>

<template>
  <AppSelect
    v-model="value"
    :items="items"
    item-title="name"
    item-value="name"
    label="Select Item"
    placeholder="Select Item"
    multiple
    clearable
    clear-icon="tabler-x"
  >
    <template #selection="{ item }">
      <VChip>
        <template #prepend>
          <VAvatar
            start
            :image="item.raw.avatar"
          />
        </template>

        <span>{{ item.title }}</span>
      </VChip>
    </template>
  </AppSelect>
</template>
`},Z={ts:`<script lang="ts" setup>
const items = ['Foo', 'Bar', 'Fizz', 'Buzz']
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Outlined"
        placeholder="Select Item"
      />
    </VCol>
    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Filled"
        placeholder="Select Item"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Solo"
        placeholder="Select Item"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Plain"
        placeholder="Select Item"
        variant="plain"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Underlined"
        variant="underlined"
        placeholder="Select Item"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const items = [
  'Foo',
  'Bar',
  'Fizz',
  'Buzz',
]
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Outlined"
        placeholder="Select Item"
      />
    </VCol>
    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Filled"
        placeholder="Select Item"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Solo"
        placeholder="Select Item"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Plain"
        placeholder="Select Item"
        variant="plain"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VSelect
        :items="items"
        label="Underlined"
        variant="underlined"
        placeholder="Select Item"
      />
    </VCol>
  </VRow>
</template>
`},Oe=d({__name:"select",setup(b){return(l,t)=>{const m=P,o=$,p=L,c=H,V=R,h=M,z=U,g=T,F=j,D=N;return S(),u(C,null,{default:a(()=>[e(n,{cols:"12",md:"6"},{default:a(()=>[e(o,{title:"Basic",code:r(Y)},{default:a(()=>[t[0]||(t[0]=s("p",null,"Select fields components are used for collecting user provided information from a list of options.",-1)),e(m)]),_:1,__:[0]},8,["code"])]),_:1}),e(n,{cols:"12",md:"6"},{default:a(()=>[e(o,{title:"Density",code:r(J)},{default:a(()=>[t[1]||(t[1]=s("p",null,[i("You can use "),s("code",null,"density"),i(" prop to reduce the field height and lower max height of list items.")],-1)),e(p)]),_:1,__:[1]},8,["code"])]),_:1}),e(n,{cols:"12"},{default:a(()=>[e(o,{title:"Variant",code:r(Z)},{default:a(()=>[t[2]||(t[2]=s("p",null,[i(" Use "),s("code",null,"filled"),i(", "),s("code",null,"outlined"),i(", "),s("code",null,"solo"),i(", "),s("code",null,"underlined"),i(" and "),s("code",null,"plain"),i(" options of "),s("code",null,"variant"),i(" prop to change appearance of select. ")],-1)),e(c)]),_:1,__:[2]},8,["code"])]),_:1}),e(n,{cols:"12",md:"6"},{default:a(()=>[e(o,{title:"Custom text and value",code:r(q)},{default:a(()=>[t[3]||(t[3]=s("p",null,"You can specify the specific properties within your items array that correspond to the title and value fields. In this example we also use the return-object prop which will return the entire object of the selected item on selection.",-1)),e(V)]),_:1,__:[3]},8,["code"])]),_:1}),e(n,{cols:"12",md:"6"},{default:a(()=>[e(o,{title:"Icons",code:r(K)},{default:a(()=>[t[4]||(t[4]=s("p",null,[i("Use a custom "),s("code",null,"prepend"),i(" or "),s("code",null,"appended"),i(" icon.")],-1)),e(h)]),_:1,__:[4]},8,["code"])]),_:1}),e(n,{cols:"12",md:"6"},{default:a(()=>[e(o,{title:"Chips",code:r(E)},{default:a(()=>[t[5]||(t[5]=s("p",null,[i("Use "),s("code",null,"chips"),i(" prop to make selected option as chip.")],-1)),e(z)]),_:1,__:[5]},8,["code"])]),_:1}),e(n,{cols:"12",md:"6"},{default:a(()=>[e(o,{title:"Menu Props",code:r(Q)},{default:a(()=>[t[6]||(t[6]=s("p",null,[i("Custom props can be passed directly to "),s("code",null,"v-menu"),i(" using "),s("code",null,"menuProps"),i(" prop.")],-1)),e(g)]),_:1,__:[6]},8,["code"])]),_:1}),e(n,{cols:"12",md:"6"},{default:a(()=>[e(o,{title:"Multiple",code:r(W)},{default:a(()=>[t[7]||(t[7]=s("p",null,[i("Use "),s("code",null,"multiple"),i(" prop to select multiple option.")],-1)),e(F)]),_:1,__:[7]},8,["code"])]),_:1}),e(n,{cols:"12",md:"6"},{default:a(()=>[e(o,{title:"Selection slot",code:r(X)},{default:a(()=>[t[8]||(t[8]=s("p",null,[i("The "),s("code",null,"selection"),i(" slot can be used to customize the way selected values are shown in the input.")],-1)),e(D)]),_:1,__:[8]},8,["code"])]),_:1})]),_:1})}}});export{Oe as default};
