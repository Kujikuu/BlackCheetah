import{V as c}from"./VPagination-CjOrNUW4.js";import{d as P,r as d,c as V,o as g,b as n,au as m,m as s,g as v,f as r,e as o,t as a}from"./main-DDcdwm3V.js";import{_ as C}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{_ as z}from"./AppCardCode.vue_vue_type_style_index_0_lang-CsHpsem3.js";import{V as S,a as _}from"./VRow-Du7OY2aM.js";import"./vue3-perfect-scrollbar-BIiYTTQI.js";import"./VCard-Da4wWZa-.js";import"./VAvatar-C3D4PMay.js";import"./VImg-NWIUnknk.js";import"./VCardText-ATWVip1e.js";import"./VDivider-CRFyNRVK.js";/* empty css              */const j={class:"d-flex flex-column gap-6 px-4"},w=P({__name:"DemoPaginationSize",setup(f){const e=d(1),t=d(2),l=d(3);return(i,u)=>(g(),V("div",j,[n(c,{modelValue:s(e),"onUpdate:modelValue":u[0]||(u[0]=p=>m(e)?e.value=p:null),length:7,size:"small"},null,8,["modelValue"]),n(c,{modelValue:s(t),"onUpdate:modelValue":u[1]||(u[1]=p=>m(t)?t.value=p:null),length:7},null,8,["modelValue"]),n(c,{modelValue:s(l),"onUpdate:modelValue":u[2]||(u[2]=p=>m(l)?l.value=p:null),length:7,"total-visible":i.$vuetify.display.xs?1:7,size:"large"},null,8,["modelValue","total-visible"])]))}}),I={class:"d-flex flex-column gap-6"},T=P({__name:"DemoPaginationColor",setup(f){const e=d(1),t=d(2),l=d(3);return(i,u)=>(g(),V("div",I,[n(c,{modelValue:s(e),"onUpdate:modelValue":u[0]||(u[0]=p=>m(e)?e.value=p:null),length:7,"active-color":"success"},null,8,["modelValue"]),n(c,{modelValue:s(t),"onUpdate:modelValue":u[1]||(u[1]=p=>m(t)?t.value=p:null),length:7,"active-color":"error"},null,8,["modelValue"]),n(c,{modelValue:s(l),"onUpdate:modelValue":u[2]||(u[2]=p=>m(l)?l.value=p:null),length:7,"active-color":"info"},null,8,["modelValue"])]))}}),B=P({__name:"DemoPaginationTotalVisible",setup(f){const e=d(1);return(t,l)=>(g(),v(c,{modelValue:s(e),"onUpdate:modelValue":l[0]||(l[0]=i=>m(e)?e.value=i:null),length:15,"total-visible":t.$vuetify.display.mdAndUp?7:3},null,8,["modelValue","total-visible"]))}}),E=P({__name:"DemoPaginationLength",setup(f){const e=d(1);return(t,l)=>(g(),v(c,{modelValue:s(e),"onUpdate:modelValue":l[0]||(l[0]=i=>m(e)?e.value=i:null),length:15},null,8,["modelValue"]))}}),O=P({__name:"DemoPaginationIcons",setup(f){const e=d(1);return(t,l)=>(g(),v(c,{modelValue:s(e),"onUpdate:modelValue":l[0]||(l[0]=i=>m(e)?e.value=i:null),length:5,"prev-icon":"tabler-caret-left","next-icon":"tabler-caret-right"},null,8,["modelValue"]))}}),A={};function k(f,e){return g(),v(c,{length:5,disabled:""})}const L=C(A,[["render",k]]),N=P({__name:"DemoPaginationOutlineCircle",setup(f){const e=d(1);return(t,l)=>(g(),v(c,{modelValue:s(e),"onUpdate:modelValue":l[0]||(l[0]=i=>m(e)?e.value=i:null),variant:"outlined",length:5,rounded:"circle"},null,8,["modelValue"]))}}),R=P({__name:"DemoPaginationCircle",setup(f){const e=d(1);return(t,l)=>(g(),v(c,{modelValue:s(e),"onUpdate:modelValue":l[0]||(l[0]=i=>m(e)?e.value=i:null),length:5,rounded:"circle"},null,8,["modelValue"]))}}),Y=P({__name:"DemoPaginationOutline",setup(f){const e=d(1);return(t,l)=>(g(),v(c,{modelValue:s(e),"onUpdate:modelValue":l[0]||(l[0]=i=>m(e)?e.value=i:null),variant:"outlined",length:5},null,8,["modelValue"]))}}),q=P({__name:"DemoPaginationBasic",setup(f){const e=d(1);return(t,l)=>(g(),v(c,{modelValue:s(e),"onUpdate:modelValue":l[0]||(l[0]=i=>m(e)?e.value=i:null),length:5},null,8,["modelValue"]))}}),F={ts:`<script lang="ts" setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="5"
  />
</template>
`,js:`<script setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="5"
  />
</template>
`},G={ts:`<script lang="ts" setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="5"
    rounded="circle"
  />
</template>
`,js:`<script setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="5"
    rounded="circle"
  />
</template>
`},H={ts:`<script setup lang="ts">
const pageSuccess = ref(1)
const pageError = ref(2)
const pageInfo = ref(3)
<\/script>

<template>
  <div class="d-flex flex-column gap-6">
    <VPagination
      v-model="pageSuccess"
      :length="7"
      active-color="success"
    />
    <VPagination
      v-model="pageError"
      :length="7"
      active-color="error"
    />
    <VPagination
      v-model="pageInfo"
      :length="7"
      active-color="info"
    />
  </div>
</template>
`,js:`<script setup>
const pageSuccess = ref(1)
const pageError = ref(2)
const pageInfo = ref(3)
<\/script>

<template>
  <div class="d-flex flex-column gap-6">
    <VPagination
      v-model="pageSuccess"
      :length="7"
      active-color="success"
    />
    <VPagination
      v-model="pageError"
      :length="7"
      active-color="error"
    />
    <VPagination
      v-model="pageInfo"
      :length="7"
      active-color="info"
    />
  </div>
</template>
`},J={ts:`<template>
  <VPagination
    :length="5"
    disabled
  />
</template>
`,js:`<template>
  <VPagination
    :length="5"
    disabled
  />
</template>
`},K={ts:`<script lang="ts" setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="5"
    prev-icon="tabler-caret-left"
    next-icon="tabler-caret-right"
  />
</template>
`,js:`<script setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="5"
    prev-icon="tabler-caret-left"
    next-icon="tabler-caret-right"
  />
</template>
`},M={ts:`<script lang="ts" setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="15"
  />
</template>
`,js:`<script setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="15"
  />
</template>
`},Q={ts:`<script setup lang="ts">
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    variant="outlined"
    :length="5"
  />
</template>
`,js:`<script setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    variant="outlined"
    :length="5"
  />
</template>
`},W={ts:`<script setup lang="ts">
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    variant="outlined"
    :length="5"
    rounded="circle"
  />
</template>
`,js:`<script setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    variant="outlined"
    :length="5"
    rounded="circle"
  />
</template>
`},X={ts:`<script setup lang="ts">
const xSmallPagination = ref(1)
const smallPagination = ref(2)
const largePagination = ref(3)
<\/script>

<template>
  <div class="d-flex flex-column gap-6 px-4">
    <VPagination
      v-model="xSmallPagination"
      :length="7"
      size="small"
    />
    <VPagination
      v-model="smallPagination"
      :length="7"
    />
    <VPagination
      v-model="largePagination"
      :length="7"
      :total-visible="$vuetify.display.xs ? 1 : 7"
      size="large"
    />
  </div>
</template>
`,js:`<script setup>
const xSmallPagination = ref(1)
const smallPagination = ref(2)
const largePagination = ref(3)
<\/script>

<template>
  <div class="d-flex flex-column gap-6 px-4">
    <VPagination
      v-model="xSmallPagination"
      :length="7"
      size="small"
    />
    <VPagination
      v-model="smallPagination"
      :length="7"
    />
    <VPagination
      v-model="largePagination"
      :length="7"
      :total-visible="$vuetify.display.xs ? 1 : 7"
      size="large"
    />
  </div>
</template>
`},Z={ts:`<script lang="ts" setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="15"
    :total-visible="$vuetify.display.mdAndUp ? 7 : 3"
  />
</template>
`,js:`<script setup>
const currentPage = ref(1)
<\/script>

<template>
  <VPagination
    v-model="currentPage"
    :length="15"
    :total-visible="$vuetify.display.mdAndUp ? 7 : 3"
  />
</template>
`},de=P({__name:"pagination",setup(f){return(e,t)=>{const l=q,i=z,u=Y,p=R,x=N,h=L,b=O,D=E,U=B,$=T,y=w;return g(),v(S,{class:"match-height"},{default:r(()=>[n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Basic",code:s(F)},{default:r(()=>[t[0]||(t[0]=o("p",null,[a("The "),o("code",null,"v-pagination"),a(" component is used to separate long sets of data.")],-1)),n(l)]),_:1,__:[0]},8,["code"])]),_:1}),n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Outline",code:s(Q)},{default:r(()=>[t[1]||(t[1]=o("p",null,[a("The "),o("code",null,"variant='outline'"),a(" prop is used to give outline to pagination item.")],-1)),n(u)]),_:1,__:[1]},8,["code"])]),_:1}),n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Circle",code:s(G)},{default:r(()=>[t[2]||(t[2]=o("p",null,[a("The "),o("code",null,"rounded"),a(" prop allows you to render pagination buttons with alternative styles.")],-1)),n(p)]),_:1,__:[2]},8,["code"])]),_:1}),n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Outline Circle",code:s(W)},{default:r(()=>[t[3]||(t[3]=o("p",null,[a("The "),o("code",null,"variant='outline'"),a(" and "),o("code",null,"rounded"),a(" prop is used to give rounded outline to pagination item.")],-1)),n(x)]),_:1,__:[3]},8,["code"])]),_:1}),n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Disabled",code:s(J)},{default:r(()=>[t[4]||(t[4]=o("p",null,[a("Pagination items can be manually deactivated using the "),o("code",null,"disabled"),a(" prop.")],-1)),n(h)]),_:1,__:[4]},8,["code"])]),_:1}),n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Icons",code:s(K)},{default:r(()=>[t[5]||(t[5]=o("p",null,[a("Previous and next page icons can be customized with the "),o("code",null,"prev-icon"),a(" and "),o("code",null,"next-icon"),a(" props.")],-1)),n(b)]),_:1,__:[5]},8,["code"])]),_:1}),n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Length",code:s(M)},{default:r(()=>[t[6]||(t[6]=o("p",null,[a("Using the "),o("code",null,"length"),a(" prop you can set the length of "),o("code",null,"v-pagination"),a(", if the number of page buttons exceeds the parent container, it will truncate the list.")],-1)),n(D)]),_:1,__:[6]},8,["code"])]),_:1}),n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Total visible",code:s(Z)},{default:r(()=>[t[7]||(t[7]=o("p",null,[a("You can also manually set the maximum number of visible page buttons with the "),o("code",null,"total-visible"),a(" prop.")],-1)),n(U)]),_:1,__:[7]},8,["code"])]),_:1}),n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Color",code:s(H)},{default:r(()=>[t[8]||(t[8]=o("p",null,[a("Use "),o("code",null,"active-color"),a(" prop for create different color pagination.")],-1)),n($)]),_:1,__:[8]},8,["code"])]),_:1}),n(_,{cols:"12",md:"6"},{default:r(()=>[n(i,{title:"Size",code:s(X)},{default:r(()=>[t[9]||(t[9]=o("p",null,[a("Use "),o("code",null,"size"),a(" prop to sets the height and width of the component. Default unit is px. Can also use the following predefined sizes: "),o("strong",null,"x-small"),a(", "),o("strong",null,"small"),a(", "),o("strong",null,"default"),a(", "),o("strong",null,"large"),a(", and "),o("strong",null,"x-large"),a(".")],-1)),n(y)]),_:1,__:[9]},8,["code"])]),_:1})]),_:1})}}});export{de as default};
