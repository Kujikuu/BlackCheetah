import{_ as h}from"./AppTextarea.vue_vue_type_script_setup_true_lang-CIe2COFg.js";import{d as _,r as w,g as d,o as i,m as p,au as V,f as o,b as e,e as a,t}from"./main-BFHJN9nb.js";import{_ as T}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{a as r,V as b}from"./VRow-CkpL0RVO.js";import{V as x}from"./VTextarea-Bsvjwuux.js";import{_ as k}from"./AppCardCode.vue_vue_type_style_index_0_lang-Cr7Um3Yy.js";import"./form-CRnOu3kV.js";/* empty css              *//* empty css                   */import"./VCounter-Cd8lIrDY.js";import"./VImg-CUvr5mlE.js";import"./VField-5Oc1eDZT.js";import"./easing-Bybner-F.js";import"./VInput-C8j_k2yQ.js";import"./forwardRefs-C-GTDzx5.js";import"./vue3-perfect-scrollbar-acdO2PlS.js";import"./VCard-DX1TBmaJ.js";import"./VAvatar-0YR6k8GC.js";import"./VCardText-DaLxJsvL.js";import"./VDivider-BkXgjbRp.js";const $=_({__name:"DemoTextareaValidation",setup(u){const n=w("Hello!"),l=[c=>c.length<=25||"Max 25 characters"];return(c,s)=>{const m=h;return i(),d(m,{modelValue:p(n),"onUpdate:modelValue":s[0]||(s[0]=f=>V(n)?n.value=f:null),label:"Validation",rules:l,rows:"2",placeholder:"Placeholder Text"},null,8,["modelValue"])}}}),j=_({__name:"DemoTextareaNoResize",setup(u){const n=w("Marshmallow tiramisu pie dessert gingerbread tart caramels marzipan oat cake. Muffin sesame snaps cupcake bonbon cookie tiramisu. Pudding biscuit gingerbread halvah lollipop jelly-o cookie.");return(l,c)=>{const s=h;return i(),d(s,{modelValue:p(n),"onUpdate:modelValue":c[0]||(c[0]=m=>V(n)?n.value=m:null),label:"Text","no-resize":"",rows:"2",placeholder:"Placeholder Text"},null,8,["modelValue"])}}}),z={};function H(u,n){const l=h;return i(),d(b,null,{default:o(()=>[e(r,{cols:"12",sm:"6"},{default:o(()=>[e(l,{label:"One row","auto-grow":"",rows:"1","row-height":"15",placeholder:"Placeholder Text"})]),_:1}),e(r,{cols:"12",sm:"6"},{default:o(()=>[e(l,{"auto-grow":"",label:"Two rows",rows:"2",placeholder:"Placeholder Text","row-height":"20"})]),_:1}),e(r,{cols:"12",sm:"6"},{default:o(()=>[e(l,{label:"Three rows","auto-grow":"",rows:"3",placeholder:"Placeholder Text","row-height":"25"})]),_:1}),e(r,{cols:"12",sm:"6"},{default:o(()=>[e(l,{"auto-grow":"",label:"Four rows",placeholder:"Placeholder Text",rows:"4","row-height":"30"})]),_:1})]),_:1})}const U=T(z,[["render",H]]),M={};function B(u,n){const l=h;return i(),d(b,null,{default:o(()=>[e(r,{cols:"12"},{default:o(()=>[e(l,{label:"prepend-icon",rows:"1",placeholder:"Placeholder Text","prepend-icon":"tabler-message-2"})]),_:1}),e(r,{cols:"12"},{default:o(()=>[e(l,{"append-icon":"tabler-message-2",placeholder:"Placeholder Text",label:"append-icon",rows:"1"})]),_:1}),e(r,{cols:"12"},{default:o(()=>[e(l,{"prepend-inner-icon":"tabler-message-2",label:"prepend-inner-icon",placeholder:"Placeholder Text",rows:"1"})]),_:1}),e(r,{cols:"12"},{default:o(()=>[e(l,{"append-inner-icon":"tabler-message-2",label:"append-inner-icon",placeholder:"Placeholder Text",rows:"1"})]),_:1})]),_:1})}const G=T(M,[["render",B]]),F=_({__name:"DemoTextareaCounter",setup(u){const n=w("Hello!");return(l,c)=>{const s=h;return i(),d(s,{modelValue:p(n),"onUpdate:modelValue":c[0]||(c[0]=m=>V(n)?n.value=m:null),counter:"",label:"Text",placeholder:"Placeholder Text"},null,8,["modelValue"])}}}),N=_({__name:"DemoTextareaClearable",setup(u){const n=w("This is clearable text.");return(l,c)=>{const s=h;return i(),d(s,{modelValue:p(n),"onUpdate:modelValue":c[0]||(c[0]=m=>V(n)?n.value=m:null),clearable:"","clear-icon":"tabler-circle-x",label:"Text",placeholder:"Placeholder Text"},null,8,["modelValue"])}}}),O={};function S(u,n){const l=h;return i(),d(l,{autocomplete:"email",label:"Email",placeholder:"johndoe@email.com"})}const W=T(O,[["render",S]]),E={};function I(u,n){const l=h;return i(),d(b,null,{default:o(()=>[e(r,{cols:"12"},{default:o(()=>[e(l,{disabled:"",label:"Disabled",hint:"Hint text",placeholder:"Placeholder Text",rows:"2"})]),_:1}),e(r,{cols:"12"},{default:o(()=>[e(l,{readonly:"",rows:"2",label:"Readonly",placeholder:"Placeholder Text",hint:"Hint text"})]),_:1})]),_:1})}const Y=T(E,[["render",I]]),q={};function J(u,n){return i(),d(b,null,{default:o(()=>[e(r,{cols:"12",sm:"6"},{default:o(()=>[e(x,{label:"Default",rows:"2",placeholder:"Placeholder Text"})]),_:1}),e(r,{cols:"12",sm:"6"},{default:o(()=>[e(x,{label:"Solo",placeholder:"Placeholder Text",rows:"2",variant:"solo"})]),_:1}),e(r,{cols:"12",sm:"6"},{default:o(()=>[e(x,{label:"Filled",rows:"2",placeholder:"Placeholder Text",variant:"filled"})]),_:1}),e(r,{cols:"12",sm:"6"},{default:o(()=>[e(x,{label:"Outlined",rows:"2",placeholder:"Placeholder Text",variant:"outlined"})]),_:1}),e(r,{cols:"12",sm:"6"},{default:o(()=>[e(x,{label:"Underlined",rows:"2",placeholder:"Placeholder Text",variant:"underlined"})]),_:1}),e(r,{cols:"12",sm:"6"},{default:o(()=>[e(x,{label:"Plain",rows:"2",placeholder:"Placeholder Text",variant:"plain"})]),_:1})]),_:1})}const K=T(q,[["render",J]]),L=_({__name:"DemoTextareaAutoGrow",setup(u){const n=w("The Woodman set to work at once, and so sharp was his axe that the tree was soon chopped nearly through.");return(l,c)=>{const s=h;return i(),d(s,{modelValue:p(n),"onUpdate:modelValue":c[0]||(c[0]=m=>V(n)?n.value=m:null),label:"Auto Grow",placeholder:"Placeholder Text","auto-grow":""},null,8,["modelValue"])}}}),Q={};function X(u,n){const l=h;return i(),d(l,{label:"Default",placeholder:"Placeholder Text"})}const Z=T(Q,[["render",X]]),ee={ts:`<script setup lang="ts">
const textareaValue = ref('The Woodman set to work at once, and so sharp was his axe that the tree was soon chopped nearly through.')
<\/script>

<template>
  <AppTextarea
    v-model="textareaValue"
    label="Auto Grow"
    placeholder="Placeholder Text"
    auto-grow
  />
</template>
`,js:`<script setup>
const textareaValue = ref('The Woodman set to work at once, and so sharp was his axe that the tree was soon chopped nearly through.')
<\/script>

<template>
  <AppTextarea
    v-model="textareaValue"
    label="Auto Grow"
    placeholder="Placeholder Text"
    auto-grow
  />
</template>
`},le={ts:`<template>
  <AppTextarea
    label="Default"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<template>
  <AppTextarea
    label="Default"
    placeholder="Placeholder Text"
  />
</template>
`},oe={ts:`<template>
  <AppTextarea
    autocomplete="email"
    label="Email"
    placeholder="johndoe@email.com"
  />
</template>
`,js:`<template>
  <AppTextarea
    autocomplete="email"
    label="Email"
    placeholder="johndoe@email.com"
  />
</template>
`},ae={ts:`<script setup lang="ts">
const textareaValue = ref('This is clearable text.')
<\/script>

<template>
  <AppTextarea
    v-model="textareaValue"
    clearable
    clear-icon="tabler-circle-x"
    label="Text"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<script setup>
const textareaValue = ref('This is clearable text.')
<\/script>

<template>
  <AppTextarea
    v-model="textareaValue"
    clearable
    clear-icon="tabler-circle-x"
    label="Text"
    placeholder="Placeholder Text"
  />
</template>
`},te={ts:`<script lang="ts" setup>
const textareaValue = ref('Hello!')
<\/script>

<template>
  <AppTextarea
    v-model="textareaValue"
    counter
    label="Text"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<script setup>
const textareaValue = ref('Hello!')
<\/script>

<template>
  <AppTextarea
    v-model="textareaValue"
    counter
    label="Text"
    placeholder="Placeholder Text"
  />
</template>
`},re={ts:`<template>
  <VRow>
    <VCol cols="12">
      <AppTextarea
        label="prepend-icon"
        rows="1"
        placeholder="Placeholder Text"
        prepend-icon="tabler-message-2"
      />
    </VCol>

    <VCol cols="12">
      <AppTextarea
        append-icon="tabler-message-2"
        placeholder="Placeholder Text"
        label="append-icon"
        rows="1"
      />
    </VCol>

    <VCol cols="12">
      <AppTextarea
        prepend-inner-icon="tabler-message-2"
        label="prepend-inner-icon"
        placeholder="Placeholder Text"
        rows="1"
      />
    </VCol>

    <VCol cols="12">
      <AppTextarea
        append-inner-icon="tabler-message-2"
        label="append-inner-icon"
        placeholder="Placeholder Text"
        rows="1"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol cols="12">
      <AppTextarea
        label="prepend-icon"
        rows="1"
        placeholder="Placeholder Text"
        prepend-icon="tabler-message-2"
      />
    </VCol>

    <VCol cols="12">
      <AppTextarea
        append-icon="tabler-message-2"
        placeholder="Placeholder Text"
        label="append-icon"
        rows="1"
      />
    </VCol>

    <VCol cols="12">
      <AppTextarea
        prepend-inner-icon="tabler-message-2"
        label="prepend-inner-icon"
        placeholder="Placeholder Text"
        rows="1"
      />
    </VCol>

    <VCol cols="12">
      <AppTextarea
        append-inner-icon="tabler-message-2"
        label="append-inner-icon"
        placeholder="Placeholder Text"
        rows="1"
      />
    </VCol>
  </VRow>
</template>
`},ne={ts:`<script lang="ts" setup>
const value = ref('Marshmallow tiramisu pie dessert gingerbread tart caramels marzipan oat cake. Muffin sesame snaps cupcake bonbon cookie tiramisu. Pudding biscuit gingerbread halvah lollipop jelly-o cookie.')
<\/script>

<template>
  <AppTextarea
    v-model="value"
    label="Text"
    no-resize
    rows="2"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<script setup>
const value = ref('Marshmallow tiramisu pie dessert gingerbread tart caramels marzipan oat cake. Muffin sesame snaps cupcake bonbon cookie tiramisu. Pudding biscuit gingerbread halvah lollipop jelly-o cookie.')
<\/script>

<template>
  <AppTextarea
    v-model="value"
    label="Text"
    no-resize
    rows="2"
    placeholder="Placeholder Text"
  />
</template>
`},se={ts:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <AppTextarea
        label="One row"
        auto-grow
        rows="1"
        row-height="15"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <AppTextarea
        auto-grow
        label="Two rows"
        rows="2"
        placeholder="Placeholder Text"
        row-height="20"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <AppTextarea
        label="Three rows"
        auto-grow
        rows="3"
        placeholder="Placeholder Text"
        row-height="25"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <AppTextarea
        auto-grow
        label="Four rows"
        placeholder="Placeholder Text"
        rows="4"
        row-height="30"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <AppTextarea
        label="One row"
        auto-grow
        rows="1"
        row-height="15"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <AppTextarea
        auto-grow
        label="Two rows"
        rows="2"
        placeholder="Placeholder Text"
        row-height="20"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <AppTextarea
        label="Three rows"
        auto-grow
        rows="3"
        placeholder="Placeholder Text"
        row-height="25"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <AppTextarea
        auto-grow
        label="Four rows"
        placeholder="Placeholder Text"
        rows="4"
        row-height="30"
      />
    </VCol>
  </VRow>
</template>
`},ce={ts:`<template>
  <VRow>
    <VCol cols="12">
      <AppTextarea
        disabled
        label="Disabled"
        hint="Hint text"
        placeholder="Placeholder Text"
        rows="2"
      />
    </VCol>

    <VCol cols="12">
      <AppTextarea
        readonly
        rows="2"
        label="Readonly"
        placeholder="Placeholder Text"
        hint="Hint text"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol cols="12">
      <AppTextarea
        disabled
        label="Disabled"
        hint="Hint text"
        placeholder="Placeholder Text"
        rows="2"
      />
    </VCol>

    <VCol cols="12">
      <AppTextarea
        readonly
        rows="2"
        label="Readonly"
        placeholder="Placeholder Text"
        hint="Hint text"
      />
    </VCol>
  </VRow>
</template>
`},pe={ts:`<script lang="ts" setup>
const textareaValue = ref('Hello!')
const rules = [(v: string) => v.length <= 25 || 'Max 25 characters']
<\/script>

<template>
  <AppTextarea
    v-model="textareaValue"
    label="Validation"
    :rules="rules"
    rows="2"
    placeholder="Placeholder Text"
  />
</template>
`,js:`<script setup>
const textareaValue = ref('Hello!')
const rules = [v => v.length <= 25 || 'Max 25 characters']
<\/script>

<template>
  <AppTextarea
    v-model="textareaValue"
    label="Validation"
    :rules="rules"
    rows="2"
    placeholder="Placeholder Text"
  />
</template>
`},de={ts:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Default"
        rows="2"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Solo"
        placeholder="Placeholder Text"
        rows="2"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Filled"
        rows="2"
        placeholder="Placeholder Text"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Outlined"
        rows="2"
        placeholder="Placeholder Text"
        variant="outlined"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Underlined"
        rows="2"
        placeholder="Placeholder Text"
        variant="underlined"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Plain"
        rows="2"
        placeholder="Placeholder Text"
        variant="plain"
      />
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Default"
        rows="2"
        placeholder="Placeholder Text"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Solo"
        placeholder="Placeholder Text"
        rows="2"
        variant="solo"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Filled"
        rows="2"
        placeholder="Placeholder Text"
        variant="filled"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Outlined"
        rows="2"
        placeholder="Placeholder Text"
        variant="outlined"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Underlined"
        rows="2"
        placeholder="Placeholder Text"
        variant="underlined"
      />
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VTextarea
        label="Plain"
        rows="2"
        placeholder="Placeholder Text"
        variant="plain"
      />
    </VCol>
  </VRow>
</template>
`},$e=_({__name:"textarea",setup(u){return(n,l)=>{const c=Z,s=k,m=L,f=K,g=Y,C=W,P=N,v=F,A=G,R=U,D=j,y=$;return i(),d(b,{class:"match-height"},{default:o(()=>[e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"Basic",code:p(le)},{default:o(()=>[l[0]||(l[0]=a("p",null," v-textarea in its simplest form is a multi-line text-field, useful for larger amounts of text. ",-1)),e(c)]),_:1,__:[0]},8,["code"])]),_:1}),e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"Auto Grow",code:p(ee)},{default:o(()=>[l[1]||(l[1]=a("p",null,[t("When using the "),a("code",null,"auto-grow"),t(" prop, textarea's will automatically increase in size when the contained text exceeds its size.")],-1)),e(m)]),_:1,__:[1]},8,["code"])]),_:1}),e(r,{cols:"12"},{default:o(()=>[e(s,{title:"Variant",code:p(de)},{default:o(()=>[l[2]||(l[2]=a("p",null,[t("Use "),a("code",null,"filled"),t(", "),a("code",null,"plain"),t(", "),a("code",null,"outlined"),t(", "),a("code",null,"solo"),t(" and "),a("code",null,"underlined"),t(" option of "),a("code",null,"variant"),t(" prop to change the look of file input.")],-1)),e(f)]),_:1,__:[2]},8,["code"])]),_:1}),e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"States",code:p(ce)},{default:o(()=>[l[3]||(l[3]=a("p",null,[t("Use "),a("code",null,"disabled"),t(" and "),a("code",null,"readonly"),t(" prop to change the state of textarea.")],-1)),e(g)]),_:1,__:[3]},8,["code"])]),_:1}),e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"Browser autocomplete",code:p(oe)},{default:o(()=>[l[4]||(l[4]=a("p",null,[t(" The "),a("code",null,"autocomplete"),t(" prop gives you the option to enable the browser to predict user input. ")],-1)),e(C)]),_:1,__:[4]},8,["code"])]),_:1}),e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"Clearable",code:p(ae)},{default:o(()=>[l[5]||(l[5]=a("p",null,[t("You can clear the text from a "),a("code",null,"v-textarea"),t(" by using the "),a("code",null,"clearable"),t(" prop, and customize the icon used with the "),a("code",null,"clearable-icon"),t(" prop.")],-1)),e(P)]),_:1,__:[5]},8,["code"])]),_:1}),e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"Counter",code:p(te)},{default:o(()=>[l[6]||(l[6]=a("p",null,[t(" The "),a("code",null,"counter"),t(" prop informs the user of a character limit for the "),a("code",null,"v-textarea"),t(". ")],-1)),e(v)]),_:1,__:[6]},8,["code"])]),_:1}),e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"Icons",code:p(re)},{default:o(()=>[l[7]||(l[7]=a("p",null,[t("The "),a("code",null,"append-icon"),t(", "),a("code",null,"prepend-icon"),t(", "),a("code",null,"append-inner-icon"),t(" and "),a("code",null,"prepend-inner-icon"),t(" props help add context to v-textarea.")],-1)),e(A)]),_:1,__:[7]},8,["code"])]),_:1}),e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"Rows",code:p(se)},{default:o(()=>[l[8]||(l[8]=a("p",null,[t("The "),a("code",null,"rows"),t(" prop allows you to define how many rows the textarea has, when combined with the "),a("code",null,"row-height"),t(" prop you can further customize your rows by defining their height.")],-1)),e(R)]),_:1,__:[8]},8,["code"])]),_:1}),e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"No resize",code:p(ne)},{default:o(()=>[l[9]||(l[9]=a("p",null,[a("code",null,"v-textarea"),t("'s have the option to remain the same size regardless of their content's size, using the "),a("code",null,"no-resize"),t(" prop.")],-1)),e(D)]),_:1,__:[9]},8,["code"])]),_:1}),e(r,{cols:"12",md:"6"},{default:o(()=>[e(s,{title:"Validation",code:p(pe)},{default:o(()=>[l[10]||(l[10]=a("p",null,[t("Use "),a("code",null,"rules"),t(" prop to validate the textarea.")],-1)),e(y)]),_:1,__:[10]},8,["code"])]),_:1})]),_:1})}}});export{$e as default};
