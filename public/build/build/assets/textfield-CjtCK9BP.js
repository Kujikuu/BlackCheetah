import{_ as V}from"./AppTextField.vue_vue_type_script_setup_true_lang-DhKHFfLG.js";import{V as S}from"./VNodeRenderer-C2wnVx7D.js";import{d as v,r as _,g as f,o as u,m as r,au as h,f as t,b as e,c as U,l as q,aa as F,a3 as j,a$ as H,H as N,cE as L,t as a,q as z,e as o}from"./main-B7VC7nGg.js";import{V as B}from"./VTooltip-D1fd0pjA.js";import{_ as T}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{V as C,a as s}from"./VRow-MKCJGy0b.js";import{r as E,e as W}from"./validators-BYiRrju8.js";import{V as Y}from"./VForm-l3pGfDRl.js";import{V as w}from"./VTextField-9u7WM2bG.js";import{_ as O}from"./AppCardCode.vue_vue_type_style_index_0_lang-CD0z15Ga.js";import"./form-BaJ0G7mb.js";import"./VOverlay-B389Zf8M.js";import"./easing-Bybner-F.js";import"./lazy-CsDJnHKD.js";import"./scopeId-HadF4ZZN.js";import"./VImg-x37b4dxK.js";import"./forwardRefs-C-GTDzx5.js";/* empty css              *//* empty css                   */import"./VCounter-D47buQPS.js";import"./VField-CKsqcJ7I.js";import"./VInput-KWRFsiTr.js";import"./vue3-perfect-scrollbar-CLY7Pg1l.js";import"./VCard-K7PtDEgU.js";import"./VAvatar-CzzIEzv4.js";import"./VCardText-HxJsBLuU.js";import"./VDivider-B1_9QpCE.js";const G={key:0,class:"ms-3"},J=v({__name:"DemoTextfieldIconSlots",setup(x){const n=_("Hey!"),l=_(!1),m=()=>{l.value=!0,n.value="Wait for it...",setTimeout(()=>{l.value=!1,n.value="You've clicked me!"},2e3)};return(i,d)=>{const c=V;return u(),f(c,{modelValue:r(n),"onUpdate:modelValue":d[0]||(d[0]=p=>h(n)?n.value=p:null),clearable:"",label:"Message",placeholder:"Hey!!",type:"text",class:"textfield-demo-icon-slot"},{prepend:t(()=>[e(B,{location:"bottom"},{activator:t(({props:p})=>[e(F,z(p,{icon:"tabler-help"}),null,16)]),default:t(()=>[d[1]||(d[1]=a(" I'm a tooltip "))]),_:1,__:[1]})]),"append-inner":t(()=>[e(L,{"leave-absolute":""},{default:t(()=>[r(l)?(u(),f(H,{key:0,color:"primary",width:"3",size:"24",indeterminate:""})):(u(),f(r(S),{key:1,class:"text-2xl",nodes:r(N).app.logo},null,8,["nodes"]))]),_:1})]),append:t(()=>[e(j,{icon:i.$vuetify.display.smAndDown,onClick:m},{default:t(()=>[e(F,{icon:"tabler-viewfinder",color:"#fff",size:"22"}),i.$vuetify.display.mdAndUp?(u(),U("span",G,"Click me")):q("",!0)]),_:1},8,["icon"])]),_:1},8,["modelValue"])}}}),K=T(J,[["__scopeId","data-v-8f478c8f"]]),Q=v({__name:"DemoTextfieldPasswordInput",setup(x){const n=_(!1),l=_(!0),m=_("Password"),i=_("wqfasds"),d={required:c=>!!c||"Required.",min:c=>c.length>=8||"Min 8 characters"};return(c,p)=>{const b=V;return u(),f(C,null,{default:t(()=>[e(s,{cols:"12",sm:"6"},{default:t(()=>[e(b,{modelValue:r(m),"onUpdate:modelValue":p[0]||(p[0]=g=>h(m)?m.value=g:null),"append-inner-icon":r(n)?"tabler-eye-off":"tabler-eye",rules:[d.required,d.min],type:r(n)?"text":"password",name:"input-10-1",label:"Normal with hint text",hint:"At least 8 characters",placeholder:"············",counter:"","onClick:appendInner":p[1]||(p[1]=g=>n.value=!r(n))},null,8,["modelValue","append-inner-icon","rules","type"])]),_:1}),e(s,{cols:"12",sm:"6"},{default:t(()=>[e(b,{modelValue:r(i),"onUpdate:modelValue":p[2]||(p[2]=g=>h(i)?i.value=g:null),rules:[d.required,d.min],"append-inner-icon":r(l)?"tabler-eye-off":"tabler-eye",type:r(l)?"text":"password",name:"input-10-2",placeholder:"············",label:"Visible",hint:"At least 8 characters","onClick:appendInner":p[3]||(p[3]=g=>l.value=!r(l))},null,8,["modelValue","rules","append-inner-icon","type"])]),_:1})]),_:1})}}}),X={};function Z(x,n){const l=V;return u(),f(l,null,{label:t(()=>[n[0]||(n[0]=a(" What about  ")),n[1]||(n[1]=o("strong",null,"icon",-1)),n[2]||(n[2]=a(" here? ")),e(F,{icon:"tabler-file-search"})]),_:1})}const ee=T(X,[["render",Z]]),le=v({__name:"DemoTextfieldIconEvents",setup(x){const n=_("Hey!"),l=_(!0),m=_(0),i=()=>{l.value=!l.value},d=()=>{n.value=""},c=()=>{m.value=0},p=()=>{c(),d()};return(b,g)=>{const y=V;return u(),f(y,{modelValue:r(n),"onUpdate:modelValue":g[0]||(g[0]=A=>h(n)?n.value=A:null),clearable:"",type:"text",label:"Message",color:"primary",placeholder:"Hey!!","clear-icon":"tabler-circle-x","append-icon":r(n)?b.$vuetify.locale.isRtl?"tabler-arrow-big-left-lines":"tabler-arrow-big-right-lines":"tabler-microphone","append-inner-icon":r(l)?"tabler-map-pin":"tabler-map-pin-off","onClick:appendInner":i,"onClick:append":p,"onClick:clear":d},null,8,["modelValue","append-icon","append-inner-icon"])}}}),te=v({__name:"DemoTextfieldValidation",setup(x){const n=_("");return(l,m)=>{const i=V;return u(),f(Y,null,{default:t(()=>[e(i,{modelValue:r(n),"onUpdate:modelValue":m[0]||(m[0]=d=>h(n)?n.value=d:null),rules:["requiredValidator"in l?l.requiredValidator:r(E),"emailValidator"in l?l.emailValidator:r(W)],placeholder:"johnedoe@email.com",label:"E-mail"},null,8,["modelValue","rules"])]),_:1})}}}),oe={};function ne(x,n){const l=V;return u(),f(l,{label:"Regular",placeholder:"Placeholder Text","single-line":""})}const ae=T(oe,[["render",ne]]),re=v({__name:"DemoTextfieldPrefixesAndSuffixes",setup(x){const n=_(10.05),l=_(28.02),m=_("example"),i=_("04:56");return(d,c)=>{const p=V;return u(),f(C,null,{default:t(()=>[e(s,{cols:"12"},{default:t(()=>[e(p,{modelValue:r(n),"onUpdate:modelValue":c[0]||(c[0]=b=>h(n)?n.value=b:null),label:"Amount",prefix:"SAR",type:"number",placeholder:"10.05"},null,8,["modelValue"])]),_:1}),e(s,{cols:"12"},{default:t(()=>[e(p,{modelValue:r(l),"onUpdate:modelValue":c[1]||(c[1]=b=>h(l)?l.value=b:null),label:"Weight",suffix:"lbs",type:"number",placeholder:"28.02"},null,8,["modelValue"])]),_:1}),e(s,{cols:"12"},{default:t(()=>[e(p,{modelValue:r(m),"onUpdate:modelValue":c[2]||(c[2]=b=>h(m)?m.value=b:null),label:"Email address",suffix:"@gmail.com",placeholder:"example"},null,8,["modelValue"])]),_:1}),e(s,{cols:"12"},{default:t(()=>[e(p,{modelValue:r(i),"onUpdate:modelValue":c[3]||(c[3]=b=>h(i)?i.value=b:null),label:"Label Text",type:"time",suffix:"PST",placeholder:"04:56"},null,8,["modelValue"])]),_:1})]),_:1})}}}),se={};function ie(x,n){const l=V;return u(),f(C,null,{default:t(()=>[e(s,{cols:"12"},{default:t(()=>[e(l,{label:"Prepend","prepend-icon":"tabler-map-pin",placeholder:"Placeholder Text"})]),_:1}),e(s,{cols:"12"},{default:t(()=>[e(l,{label:"Prepend Inner","prepend-inner-icon":"tabler-map-pin",placeholder:"Placeholder Text"})]),_:1}),e(s,{cols:"12"},{default:t(()=>[e(l,{label:"Append","append-icon":"tabler-map-pin",placeholder:"Placeholder Text"})]),_:1}),e(s,{cols:"12"},{default:t(()=>[e(l,{label:"Append Inner","append-inner-icon":"tabler-map-pin",placeholder:"Placeholder Text"})]),_:1})]),_:1})}const pe=T(se,[["render",ie]]),de={};function ce(x,n){const l=V;return u(),f(l,{color:"success",label:"First name",placeholder:"Placeholder Text"})}const me=T(de,[["render",ce]]),ue={};function fe(x,n){const l=V;return u(),f(l,{placeholder:"Placeholder Text",label:"Regular",clearable:""})}const _e=T(ue,[["render",fe]]),xe=v({__name:"DemoTextfieldCounter",setup(x){const n=_("Preliminary report"),l=_("California is a state in the western United States"),m=[i=>i.length<=25||"Max 25 characters"];return(i,d)=>{const c=V;return u(),f(C,null,{default:t(()=>[e(s,{cols:"12"},{default:t(()=>[e(c,{modelValue:r(n),"onUpdate:modelValue":d[0]||(d[0]=p=>h(n)?n.value=p:null),rules:m,counter:"25",placeholder:"Placeholder Text",hint:"This field uses counter prop",label:"Regular"},null,8,["modelValue"])]),_:1}),e(s,{cols:"12"},{default:t(()=>[e(c,{modelValue:r(l),"onUpdate:modelValue":d[1]||(d[1]=p=>h(l)?l.value=p:null),rules:m,counter:"",maxlength:"25",placeholder:"Placeholder Text",hint:"This field uses maxlength attribute",label:"Limit exceeded"},null,8,["modelValue"])]),_:1})]),_:1})}}}),be={};function Ve(x,n){const l=V;return u(),f(C,null,{default:t(()=>[e(s,null,{default:t(()=>[e(l,{label:"Disabled",placeholder:"Placeholder Text",disabled:""})]),_:1}),e(s,{cols:"12"},{default:t(()=>[e(l,{placeholder:"Placeholder Text",label:"Readonly",readonly:""})]),_:1})]),_:1})}const he=T(be,[["render",Ve]]),ge={};function Te(x,n){return u(),f(C,null,{default:t(()=>[e(s,{cols:"12",md:"6"},{default:t(()=>[e(w,{label:"Outlined",variant:"outlined"})]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(w,{label:"Filled",variant:"filled"})]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(w,{label:"Solo",variant:"solo"})]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(w,{label:"Plain",variant:"plain"})]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(w,{label:"Underlined",variant:"underlined"})]),_:1})]),_:1})}const ve=T(ge,[["render",Te]]),Ce={};function we(x,n){const l=V;return u(),f(l,{label:"Compact",density:"compact",placeholder:"Placeholder Text"})}const ye=T(Ce,[["render",we]]),Ae={};function Fe(x,n){const l=V;return u(),f(l,{label:"Regular",placeholder:"Placeholder Text"})}const Pe=T(Ae,[["render",Fe]]),ke={ts:`<template>
  <AppTextField
    label="Regular"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<template>
  <AppTextField
    label="Regular"
    placeholder="Placeholder Text"
  />
</template>
`},Re={ts:`<template>
  <AppTextField
    placeholder="Placeholder Text"
    label="Regular"
    clearable
  />
</template>
`,js:`<template>
  <AppTextField
    placeholder="Placeholder Text"
    label="Regular"
    clearable
  />
</template>
`},Ie={ts:`<script lang="ts" setup>
const title = ref('Preliminary report')
const description = ref('California is a state in the western United States')
const rules = [(v: string) => v.length <= 25 || 'Max 25 characters']
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <AppTextField
        v-model="title"
        :rules="rules"
        counter="25"
        placeholder="Placeholder Text"
        hint="This field uses counter prop"
        label="Regular"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        v-model="description"
        :rules="rules"
        counter
        maxlength="25"
        placeholder="Placeholder Text"
        hint="This field uses maxlength attribute"
        label="Limit exceeded"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const title = ref('Preliminary report')
const description = ref('California is a state in the western United States')
const rules = [v => v.length <= 25 || 'Max 25 characters']
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <AppTextField
        v-model="title"
        :rules="rules"
        counter="25"
        placeholder="Placeholder Text"
        hint="This field uses counter prop"
        label="Regular"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        v-model="description"
        :rules="rules"
        counter
        maxlength="25"
        placeholder="Placeholder Text"
        hint="This field uses maxlength attribute"
        label="Limit exceeded"
      />
    </VCol>
  </VRow>
</template>
`},$e={ts:`<template>
  <AppTextField
    color="success"
    label="First name"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<template>
  <AppTextField
    color="success"
    label="First name"
    placeholder="Placeholder Text"
  />
</template>
`},Me={ts:`<template>
  <AppTextField
    label="Compact"
    density="compact"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<template>
  <AppTextField
    label="Compact"
    density="compact"
    placeholder="Placeholder Text"
  />
</template>
`},De={ts:`<script lang="ts" setup>
const message = ref('Hey!')
const marker = ref(true)
const iconIndex = ref(0)

const toggleMarker = () => {
  marker.value = !marker.value
}

const clearMessage = () => {
  message.value = ''
}

const resetIcon = () => {
  iconIndex.value = 0
}

const sendMessage = () => {
  resetIcon()
  clearMessage()
}
<\/script>

<template>
  <AppTextField
    v-model="message"
    clearable
    type="text"
    label="Message"
    color="primary"
    placeholder="Hey!!"
    clear-icon="tabler-circle-x"
    :append-icon="message ? $vuetify.locale.isRtl ? 'tabler-arrow-big-left-lines' : 'tabler-arrow-big-right-lines' : 'tabler-microphone'"
    :append-inner-icon="marker ? 'tabler-map-pin' : 'tabler-map-pin-off'"
    @click:append-inner="toggleMarker"
    @click:append="sendMessage"
    @click:clear="clearMessage"
  />
</template>
`,js:`<script setup>
const message = ref('Hey!')
const marker = ref(true)
const iconIndex = ref(0)

const toggleMarker = () => {
  marker.value = !marker.value
}

const clearMessage = () => {
  message.value = ''
}

const resetIcon = () => {
  iconIndex.value = 0
}

const sendMessage = () => {
  resetIcon()
  clearMessage()
}
<\/script>

<template>
  <AppTextField
    v-model="message"
    clearable
    type="text"
    label="Message"
    color="primary"
    placeholder="Hey!!"
    clear-icon="tabler-circle-x"
    :append-icon="message ? $vuetify.locale.isRtl ? 'tabler-arrow-big-left-lines' : 'tabler-arrow-big-right-lines' : 'tabler-microphone'"
    :append-inner-icon="marker ? 'tabler-map-pin' : 'tabler-map-pin-off'"
    @click:append-inner="toggleMarker"
    @click:append="sendMessage"
    @click:clear="clearMessage"
  />
</template>
`},Se={ts:`<script setup lang="ts">
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

const message = ref('Hey!')
const loading = ref(false)

const clickMe = () => {
  loading.value = true
  message.value = 'Wait for it...'

  setTimeout(() => {
    loading.value = false
    message.value = 'You've clicked me!'
  }, 2000)
}
<\/script>

<template>
  <AppTextField
    v-model="message"
    clearable
    label="Message"
    placeholder="Hey!!"
    type="text"
    class="textfield-demo-icon-slot"
  >
    <!-- Prepend -->
    <template #prepend>
      <VTooltip location="bottom">
        <template #activator="{ props }">
          <VIcon
            v-bind="props"
            icon="tabler-help"
          />
        </template>
        I'm a tooltip
      </VTooltip>
    </template>

    <!-- AppendInner -->
    <template #append-inner>
      <VFadeTransition leave-absolute>
        <VProgressCircular
          v-if="loading"
          color="primary"
          width="3"
          size="24"
          indeterminate
        />

        <VNodeRenderer
          v-else
          class="text-2xl"
          :nodes="themeConfig.app.logo"
        />
      </VFadeTransition>
    </template>

    <!-- Append -->
    <template #append>
      <VBtn
        :icon="$vuetify.display.smAndDown"
        @click="clickMe"
      >
        <VIcon
          icon="tabler-viewfinder"
          color="#fff"
          size="22"
        />
        <span
          v-if="$vuetify.display.mdAndUp"
          class="ms-3"
        >Click me</span>
      </VBtn>
    </template>
  </AppTextField>
</template>

<style lang="scss" scoped>
// .textfield-demo-icon-slot {
//   :deep(.v-input) {
//     align-content: center;

//     .v-input__prepend,
//     .v-input__append {
//       padding-block-start: 0 !important;
//     }

//     .v-input__prepend {
//       align-items: center;
//     }

//     .v-field__append-inner .v-progress-circular svg {
//       block-size: 1.3em;
//       inline-size: 1.3em;
//     }

//     .v-field__append-inner svg {
//       margin-block-start: 0.1rem;
//     }
//   }
// }
</style>
`,js:`<script setup>
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

const message = ref('Hey!')
const loading = ref(false)

const clickMe = () => {
  loading.value = true
  message.value = 'Wait for it...'
  setTimeout(() => {
    loading.value = false
    message.value = 'You've clicked me!'
  }, 2000)
}
<\/script>

<template>
  <AppTextField
    v-model="message"
    clearable
    label="Message"
    placeholder="Hey!!"
    type="text"
    class="textfield-demo-icon-slot"
  >
    <!-- Prepend -->
    <template #prepend>
      <VTooltip location="bottom">
        <template #activator="{ props }">
          <VIcon
            v-bind="props"
            icon="tabler-help"
          />
        </template>
        I'm a tooltip
      </VTooltip>
    </template>

    <!-- AppendInner -->
    <template #append-inner>
      <VFadeTransition leave-absolute>
        <VProgressCircular
          v-if="loading"
          color="primary"
          width="3"
          size="24"
          indeterminate
        />

        <VNodeRenderer
          v-else
          class="text-2xl"
          :nodes="themeConfig.app.logo"
        />
      </VFadeTransition>
    </template>

    <!-- Append -->
    <template #append>
      <VBtn
        :icon="$vuetify.display.smAndDown"
        @click="clickMe"
      >
        <VIcon
          icon="tabler-viewfinder"
          color="#fff"
          size="22"
        />
        <span
          v-if="$vuetify.display.mdAndUp"
          class="ms-3"
        >Click me</span>
      </VBtn>
    </template>
  </AppTextField>
</template>

<style lang="scss" scoped>
// .textfield-demo-icon-slot {
//   :deep(.v-input) {
//     align-content: center;

//     .v-input__prepend,
//     .v-input__append {
//       padding-block-start: 0 !important;
//     }

//     .v-input__prepend {
//       align-items: center;
//     }

//     .v-field__append-inner .v-progress-circular svg {
//       block-size: 1.3em;
//       inline-size: 1.3em;
//     }

//     .v-field__append-inner svg {
//       margin-block-start: 0.1rem;
//     }
//   }
// }
</style>
`},Ue={ts:`<template>
  <VRow>
    <VCol cols="12">
      <AppTextField
        label="Prepend"
        prepend-icon="tabler-map-pin"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        label="Prepend Inner"
        prepend-inner-icon="tabler-map-pin"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        label="Append"
        append-icon="tabler-map-pin"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        label="Append Inner"
        append-inner-icon="tabler-map-pin"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol cols="12">
      <AppTextField
        label="Prepend"
        prepend-icon="tabler-map-pin"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        label="Prepend Inner"
        prepend-inner-icon="tabler-map-pin"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        label="Append"
        append-icon="tabler-map-pin"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        label="Append Inner"
        append-inner-icon="tabler-map-pin"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`},qe={ts:`<template>
  <AppTextField>
    <template #label>
      What about &nbsp;<strong>icon</strong>&nbsp;here?
      <VIcon icon="tabler-file-search" />
    </template>
  </AppTextField>
</template>
`,js:`<template>
  <AppTextField>
    <template #label>
      What about &nbsp;<strong>icon</strong>&nbsp;here?
      <VIcon icon="tabler-file-search" />
    </template>
  </AppTextField>
</template>
`},je={ts:`<script lang="ts" setup>
const show1 = ref(false)
const show2 = ref(true)
const password = ref('Password')
const confirmPassword = ref('wqfasds')

const rules = {
  required: (value: string) => !!value || 'Required.',
  min: (v: string) => v.length >= 8 || 'Min 8 characters',
}
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <AppTextField
        v-model="password"
        :append-inner-icon="show1 ? 'tabler-eye-off' : 'tabler-eye' "
        :rules="[rules.required, rules.min]"
        :type="show1 ? 'text' : 'password'"
        name="input-10-1"
        label="Normal with hint text"
        hint="At least 8 characters"
        placeholder="············"
        counter
        @click:append-inner="show1 = !show1"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <AppTextField
        v-model="confirmPassword"
        :rules="[rules.required, rules.min]"
        :append-inner-icon="show2 ? 'tabler-eye-off' : 'tabler-eye' "
        :type="show2 ? 'text' : 'password'"
        name="input-10-2"
        placeholder="············"
        label="Visible"
        hint="At least 8 characters"
        @click:append-inner="show2 = !show2"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const show1 = ref(false)
const show2 = ref(true)
const password = ref('Password')
const confirmPassword = ref('wqfasds')

const rules = {
  required: value => !!value || 'Required.',
  min: v => v.length >= 8 || 'Min 8 characters',
}
<\/script>

<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <AppTextField
        v-model="password"
        :append-inner-icon="show1 ? 'tabler-eye-off' : 'tabler-eye' "
        :rules="[rules.required, rules.min]"
        :type="show1 ? 'text' : 'password'"
        name="input-10-1"
        label="Normal with hint text"
        hint="At least 8 characters"
        placeholder="············"
        counter
        @click:append-inner="show1 = !show1"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <AppTextField
        v-model="confirmPassword"
        :rules="[rules.required, rules.min]"
        :append-inner-icon="show2 ? 'tabler-eye-off' : 'tabler-eye' "
        :type="show2 ? 'text' : 'password'"
        name="input-10-2"
        placeholder="············"
        label="Visible"
        hint="At least 8 characters"
        @click:append-inner="show2 = !show2"
      />
    </VCol>
  </VRow>
</template>
`},He={ts:`<script setup lang="ts">
const amount = ref(10.05)
const weight = ref(28.02)
const email = ref('example')
const time = ref('04:56')
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <AppTextField
        v-model="amount"
        label="Amount"
        prefix="SAR"
        type="number"
        placeholder="10.05"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        v-model="weight"
        label="Weight"
        suffix="lbs"
        type="number"
        placeholder="28.02"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        v-model="email"
        label="Email address"
        suffix="@gmail.com"
        placeholder="example"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        v-model="time"
        label="Label Text"
        type="time"
        suffix="PST"
        placeholder="04:56"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const amount = ref(10.05)
const weight = ref(28.02)
const email = ref('example')
const time = ref('04:56')
<\/script>

<template>
  <VRow>
    <VCol cols="12">
      <AppTextField
        v-model="amount"
        label="Amount"
        prefix="SAR"
        type="number"
        placeholder="10.05"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        v-model="weight"
        label="Weight"
        suffix="lbs"
        type="number"
        placeholder="28.02"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        v-model="email"
        label="Email address"
        suffix="@gmail.com"
        placeholder="example"
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        v-model="time"
        label="Label Text"
        type="time"
        suffix="PST"
        placeholder="04:56"
      />
    </VCol>
  </VRow>
</template>
`},Ne={ts:`<template>
  <AppTextField
    label="Regular"
    placeholder="Placeholder Text"
    single-line
  />
</template>
`,js:`<template>
  <AppTextField
    label="Regular"
    placeholder="Placeholder Text"
    single-line
  />
</template>
`},Le={ts:`<template>
  <VRow>
    <VCol>
      <AppTextField
        label="Disabled"
        placeholder="Placeholder Text"
        disabled
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        placeholder="Placeholder Text"
        label="Readonly"
        readonly
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol>
      <AppTextField
        label="Disabled"
        placeholder="Placeholder Text"
        disabled
      />
    </VCol>

    <VCol cols="12">
      <AppTextField
        placeholder="Placeholder Text"
        label="Readonly"
        readonly
      />
    </VCol>
  </VRow>
</template>
`},ze={ts:`<script lang="ts" setup>
const email = ref('')
<\/script>

<template>
  <VForm>
    <AppTextField
      v-model="email"
      :rules="[requiredValidator, emailValidator]"
      placeholder="johnedoe@email.com"
      label="E-mail"
    />
  </VForm>
</template>
`,js:`<script setup>
const email = ref('')
<\/script>

<template>
  <VForm>
    <AppTextField
      v-model="email"
      :rules="[requiredValidator, emailValidator]"
      placeholder="johnedoe@email.com"
      label="E-mail"
    />
  </VForm>
</template>
`},Be={ts:`<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Outlined"
        variant="outlined"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Filled"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Solo"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Plain"
        variant="plain"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Underlined"
        variant="underlined"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Outlined"
        variant="outlined"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Filled"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Solo"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Plain"
        variant="plain"
      />
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VTextField
        label="Underlined"
        variant="underlined"
      />
    </VCol>
  </VRow>
</template>
`},bl=v({__name:"textfield",setup(x){return(n,l)=>{const m=Pe,i=O,d=ye,c=ve,p=he,b=xe,g=_e,y=me,A=pe,P=re,k=ae,R=te,I=le,$=ee,M=Q,D=K;return u(),f(C,{class:"match-height"},{default:t(()=>[e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Basic",code:r(ke)},{default:t(()=>[l[0]||(l[0]=o("p",null,"Text fields components are used for collecting user provided information.",-1)),e(m)]),_:1,__:[0]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Density",code:r(Me)},{default:t(()=>[l[1]||(l[1]=o("p",null,[a("The "),o("code",null,"density"),a(" prop decreases the height of the text field based upon levels of density; "),o("code",null,"default"),a(","),o("code",null,"comfortable"),a(", and "),o("code",null,"compact"),a(".")],-1)),e(d)]),_:1,__:[1]},8,["code"])]),_:1}),e(s,{cols:"12"},{default:t(()=>[e(i,{title:"Variant",code:r(Be)},{default:t(()=>[l[2]||(l[2]=o("p",null,[a("Use "),o("code",null,"solo"),a(", "),o("code",null,"filled"),a(", "),o("code",null,"outlined"),a(", "),o("code",null,"plain"),a(" and "),o("code",null,"underlined"),a(" option of "),o("code",null,"variant"),a(" prop to change the look of the textfield. ")],-1)),e(c)]),_:1,__:[2]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"State",code:r(Le)},{default:t(()=>[l[3]||(l[3]=o("p",null,"Text fields can be disabled or readonly.",-1)),e(p)]),_:1,__:[3]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Counter",code:r(Ie)},{default:t(()=>[l[4]||(l[4]=o("p",null,[a("Use a "),o("code",null,"counter"),a(" prop to inform a user of the character limit.")],-1)),e(b)]),_:1,__:[4]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Clearable",code:r(Re)},{default:t(()=>[l[5]||(l[5]=o("p",null,"When clearable, you can customize the clear icon with clear-icon.",-1)),e(g)]),_:1,__:[5]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Custom Colors",code:r($e)},{default:t(()=>[l[6]||(l[6]=o("p",null,[a("Use "),o("code",null,"color"),a(" prop to change the input border color.")],-1)),e(y)]),_:1,__:[6]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Icons",code:r(Ue)},{default:t(()=>[l[7]||(l[7]=o("p",null,[a("You can add icons to the text field with "),o("code",null,"prepend-icon"),a(", "),o("code",null,"append-icon"),a(" and "),o("code",null,"append-inner-icon"),a(" and "),o("code",null,"prepend-inner-icon"),a(" props.")],-1)),e(A)]),_:1,__:[7]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Prefixes and suffixes",code:r(He)},{default:t(()=>[l[8]||(l[8]=o("p",null,[a("The "),o("code",null,"prefix"),a(" and "),o("code",null,"suffix"),a(" properties allows you to prepend and append inline non-modifiable text next to the text field.")],-1)),e(P)]),_:1,__:[8]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Single line",code:r(Ne)},{default:t(()=>[l[9]||(l[9]=o("p",null,[o("code",null,"single-line"),a(" text fields do not float their label on focus or with data.")],-1)),e(k)]),_:1,__:[9]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Validation",code:r(ze)},{default:t(()=>[l[10]||(l[10]=o("p",null,[a("Vuetify includes simple validation through the "),o("code",null,"rules"),a(" prop.")],-1)),e(R)]),_:1,__:[10]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Icon events",code:r(De)},{default:t(()=>[l[11]||(l[11]=o("p",null,[o("code",null,"click:prepend"),a(", "),o("code",null,"click:append"),a(", "),o("code",null,"click:append-inner"),a(", and "),o("code",null,"click:clear"),a(" will be emitted when you click on the respective icon")],-1)),e(I)]),_:1,__:[11]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Label Slot",code:r(qe)},{default:t(()=>[l[12]||(l[12]=o("p",null,[a("Text field label can be defined in "),o("code",null,"label"),a(" slot - that will allow to use HTML content.")],-1)),e($)]),_:1,__:[12]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Password input",code:r(je)},{default:t(()=>[l[13]||(l[13]=o("p",null,[a("Using the HTML input "),o("code",null,"type"),a(" password can be used with an appended icon and callback to control the visibility.")],-1)),e(M)]),_:1,__:[13]},8,["code"])]),_:1}),e(s,{cols:"12",md:"6"},{default:t(()=>[e(i,{title:"Icon slots",code:r(Se)},{default:t(()=>[l[14]||(l[14]=o("p",null,[a("Instead of using "),o("code",null,"prepend"),a("/"),o("code",null,"append"),a("/"),o("code",null,"append-inner"),a(" icons you can use slots to extend input's functionality.")],-1)),e(D)]),_:1,__:[14]},8,["code"])]),_:1})]),_:1})}}});export{bl as default};
