import{_ as g}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{c as p,o as n,b as r,a$ as o,d as V,r as _,G as y,bm as h,f as l,t as s,v as d,m as t,g as z,e as a}from"./main-DWvNZBrn.js";import{_ as w}from"./AppCardCode.vue_vue_type_style_index_0_lang-BzCo_uqe.js";import{V as x,a as m}from"./VRow-DHZpC2G_.js";import"./vue3-perfect-scrollbar-BZRsDK3L.js";import"./VCard-BRrHEmo0.js";import"./VAvatar-BF957B0g.js";import"./VImg-g4h63Uey.js";import"./VCardText-CYct27oJ.js";import"./VDivider-zd98jbKy.js";/* empty css              */const $={},I={class:"demo-space-x"};function B(u,i){return n(),p("div",I,[r(o,{size:30,width:"3",color:"primary",indeterminate:""}),r(o,{size:40,color:"primary",indeterminate:""}),r(o,{size:50,color:"primary",indeterminate:""}),r(o,{size:60,color:"primary",indeterminate:""})])}const D=g($,[["render",B]]),b={class:"demo-space-x"},j=V({__name:"DemoProgressCircularRotate",setup(u){const i=_(),e=_(0);return y(()=>{i.value=setInterval(()=>{if(e.value===100)return e.value=0;e.value+=10},1e3)}),h(()=>{clearInterval(i.value)}),(v,c)=>(n(),p("div",b,[r(o,{rotate:360,size:70,width:6,"model-value":t(e),color:"primary"},{default:l(()=>[s(d(t(e)),1)]),_:1},8,["model-value"]),r(o,{rotate:90,size:70,width:6,"model-value":t(e),color:"primary"},{default:l(()=>[s(d(t(e)),1)]),_:1},8,["model-value"]),r(o,{rotate:170,size:70,width:6,"model-value":t(e),color:"primary"},{default:l(()=>[s(d(t(e)),1)]),_:1},8,["model-value"]),r(o,{rotate:-90,size:70,width:6,"model-value":t(e),color:"primary"},{default:l(()=>[s(d(t(e)),1)]),_:1},8,["model-value"])]))}}),R={},U={class:"demo-space-x"};function k(u,i){return n(),p("div",U,[r(o,{indeterminate:"",color:"primary"}),r(o,{indeterminate:"",color:"secondary"}),r(o,{indeterminate:"",color:"success"}),r(o,{indeterminate:"",color:"info"}),r(o,{indeterminate:"",color:"warning"}),r(o,{indeterminate:"",color:"error"})])}const M=g(R,[["render",k]]),N={},S={class:"demo-space-x"};function T(u,i){return n(),p("div",S,[r(o,{"model-value":"50",color:"primary"}),r(o,{"model-value":"50",color:"secondary"}),r(o,{"model-value":"50",color:"success"}),r(o,{"model-value":"50",color:"info"}),r(o,{"model-value":"50",color:"warning"}),r(o,{"model-value":"50",color:"error"})])}const A=g(N,[["render",T]]),E={ts:`<template>
  <div class="demo-space-x">
    <VProgressCircular
      model-value="50"
      color="primary"
    />

    <VProgressCircular
      model-value="50"
      color="secondary"
    />

    <VProgressCircular
      model-value="50"
      color="success"
    />

    <VProgressCircular
      model-value="50"
      color="info"
    />

    <VProgressCircular
      model-value="50"
      color="warning"
    />

    <VProgressCircular
      model-value="50"
      color="error"
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VProgressCircular
      model-value="50"
      color="primary"
    />

    <VProgressCircular
      model-value="50"
      color="secondary"
    />

    <VProgressCircular
      model-value="50"
      color="success"
    />

    <VProgressCircular
      model-value="50"
      color="info"
    />

    <VProgressCircular
      model-value="50"
      color="warning"
    />

    <VProgressCircular
      model-value="50"
      color="error"
    />
  </div>
</template>
`},G={ts:`<template>
  <div class="demo-space-x">
    <VProgressCircular
      indeterminate
      color="primary"
    />

    <VProgressCircular
      indeterminate
      color="secondary"
    />

    <VProgressCircular
      indeterminate
      color="success"
    />

    <VProgressCircular
      indeterminate
      color="info"
    />

    <VProgressCircular
      indeterminate
      color="warning"
    />

    <VProgressCircular
      indeterminate
      color="error"
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VProgressCircular
      indeterminate
      color="primary"
    />

    <VProgressCircular
      indeterminate
      color="secondary"
    />

    <VProgressCircular
      indeterminate
      color="success"
    />

    <VProgressCircular
      indeterminate
      color="info"
    />

    <VProgressCircular
      indeterminate
      color="warning"
    />

    <VProgressCircular
      indeterminate
      color="error"
    />
  </div>
</template>
`},q={ts:`<script setup lang="ts">
const interval = ref()
const progressValue = ref(0)

onMounted(() => {
  interval.value = setInterval(() => {
    if (progressValue.value === 100)
      return progressValue.value = 0
    progressValue.value += 10
  }, 1000)
})

onBeforeUnmount(() => {
  clearInterval(interval.value)
})
<\/script>

<template>
  <div class="demo-space-x">
    <VProgressCircular
      :rotate="360"
      :size="70"
      :width="6"
      :model-value="progressValue"
      color="primary"
    >
      {{ progressValue }}
    </VProgressCircular>

    <VProgressCircular
      :rotate="90"
      :size="70"
      :width="6"
      :model-value="progressValue"
      color="primary"
    >
      {{ progressValue }}
    </VProgressCircular>

    <VProgressCircular
      :rotate="170"
      :size="70"
      :width="6"
      :model-value="progressValue"
      color="primary"
    >
      {{ progressValue }}
    </VProgressCircular>

    <VProgressCircular
      :rotate="-90"
      :size="70"
      :width="6"
      :model-value="progressValue"
      color="primary"
    >
      {{ progressValue }}
    </VProgressCircular>
  </div>
</template>
`,js:`<script setup>
const interval = ref()
const progressValue = ref(0)

onMounted(() => {
  interval.value = setInterval(() => {
    if (progressValue.value === 100)
      return progressValue.value = 0
    progressValue.value += 10
  }, 1000)
})
onBeforeUnmount(() => {
  clearInterval(interval.value)
})
<\/script>

<template>
  <div class="demo-space-x">
    <VProgressCircular
      :rotate="360"
      :size="70"
      :width="6"
      :model-value="progressValue"
      color="primary"
    >
      {{ progressValue }}
    </VProgressCircular>

    <VProgressCircular
      :rotate="90"
      :size="70"
      :width="6"
      :model-value="progressValue"
      color="primary"
    >
      {{ progressValue }}
    </VProgressCircular>

    <VProgressCircular
      :rotate="170"
      :size="70"
      :width="6"
      :model-value="progressValue"
      color="primary"
    >
      {{ progressValue }}
    </VProgressCircular>

    <VProgressCircular
      :rotate="-90"
      :size="70"
      :width="6"
      :model-value="progressValue"
      color="primary"
    >
      {{ progressValue }}
    </VProgressCircular>
  </div>
</template>
`},F={ts:`<template>
  <div class="demo-space-x">
    <VProgressCircular
      :size="30"
      width="3"
      color="primary"
      indeterminate
    />

    <VProgressCircular
      :size="40"
      color="primary"
      indeterminate
    />

    <VProgressCircular
      :size="50"
      color="primary"
      indeterminate
    />

    <VProgressCircular
      :size="60"
      color="primary"
      indeterminate
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VProgressCircular
      :size="30"
      width="3"
      color="primary"
      indeterminate
    />

    <VProgressCircular
      :size="40"
      color="primary"
      indeterminate
    />

    <VProgressCircular
      :size="50"
      color="primary"
      indeterminate
    />

    <VProgressCircular
      :size="60"
      color="primary"
      indeterminate
    />
  </div>
</template>
`},er=V({__name:"progress-circular",setup(u){return(i,e)=>{const v=A,c=w,C=M,P=j,f=D;return n(),z(x,{class:"match-height"},{default:l(()=>[r(m,{cols:"12",md:"6"},{default:l(()=>[r(c,{title:"color",code:t(E)},{default:l(()=>[e[0]||(e[0]=a("p",null,[s("Alternate colors can be applied to "),a("code",null,"v-progress-circular"),s(" using the "),a("code",null,"color"),s(" prop.")],-1)),r(v)]),_:1,__:[0]},8,["code"])]),_:1}),r(m,{cols:"12",md:"6"},{default:l(()=>[r(c,{title:"Indeterminate",code:t(G)},{default:l(()=>[e[1]||(e[1]=a("p",null,[s("Using the "),a("code",null,"indeterminate"),s(" prop, a "),a("code",null,"v-progress-circular"),s(" continues to animate indefinitely.")],-1)),r(C)]),_:1,__:[1]},8,["code"])]),_:1}),r(m,{cols:"12",md:"6"},{default:l(()=>[r(c,{title:"Rotate",code:t(q)},{default:l(()=>[e[2]||(e[2]=a("p",null,[s("The "),a("code",null,"rotate"),s(" prop gives you the ability to customize the "),a("code",null,"v-progress-circular"),s("'s origin.")],-1)),r(P)]),_:1,__:[2]},8,["code"])]),_:1}),r(m,{cols:"12",md:"6"},{default:l(()=>[r(c,{title:"Size",code:t(F)},{default:l(()=>[e[3]||(e[3]=a("p",null,[s("The "),a("code",null,"size"),s(" and "),a("code",null,"width"),s(" props allow you to easily alter the size and width of the "),a("code",null,"v-progress-circular"),s(" component.")],-1)),r(f)]),_:1,__:[3]},8,["code"])]),_:1})]),_:1})}}});export{er as default};
