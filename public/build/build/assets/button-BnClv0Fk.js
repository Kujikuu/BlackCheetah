import{d as f,r as b,g as v,o as c,b2 as D,m as i,au as z,f as n,b as o,a3 as l,c as B,t as e,aa as _,e as s}from"./main-Bx5MqBCc.js";import{_ as p}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{a as d,V as x}from"./VRow-BSfsb00B.js";import{_ as N}from"./AppCardCode.vue_vue_type_style_index_0_lang-XKaLCs07.js";import{V as g}from"./VAlert-BH88qGLz.js";/* empty css              */import"./vue3-perfect-scrollbar-Dx4fYpdh.js";import"./VCard-DXwsm8sH.js";import"./VAvatar-Dq0WBzTg.js";import"./VImg-CzTq61uy.js";import"./VCardText-881-qsDn.js";import"./VDivider-BD3st0Tt.js";const R=f({__name:"DemoButtonGroup",setup(u){const t=b(1);return(r,V)=>(c(),v(D,{modelValue:i(t),"onUpdate:modelValue":V[0]||(V[0]=a=>z(t)?t.value=a:null),density:"comfortable"},{default:n(()=>[o(l,{icon:"tabler-align-left"}),o(l,{icon:"tabler-align-center"}),o(l,{icon:"tabler-align-right"}),o(l,{icon:"tabler-align-justified"})]),_:1},8,["modelValue"]))}}),U={},O={class:"demo-space-x"};function A(u,t){return c(),B("div",O,[o(l,{href:"https://pixinvent.com/"},{default:n(()=>t[0]||(t[0]=[e(" String Literal ")])),_:1,__:[0]}),o(l,{href:"https://pixinvent.com/",target:"_blank",rel:"noopener noreferrer"},{default:n(()=>t[1]||(t[1]=[e(" Open New Tab ")])),_:1,__:[1]})])}const q=p(U,[["render",A]]),G={},Q={class:"demo-space-x"};function Y(u,t){return c(),B("div",Q,[o(l,{to:"alert"},{default:n(()=>t[0]||(t[0]=[e(" String Literal ")])),_:1,__:[0]}),o(l,{color:"warning",to:{path:"alert"}},{default:n(()=>t[1]||(t[1]=[e(" Object Path ")])),_:1,__:[1]}),o(l,{color:"success",to:{name:"components-alert"}},{default:n(()=>t[2]||(t[2]=[e(" Named Router ")])),_:1,__:[2]}),o(l,{color:"secondary",to:{path:"alert",query:{plan:"private"}}},{default:n(()=>t[3]||(t[3]=[e(" With Query ")])),_:1,__:[3]})])}const F=p(G,[["render",Y]]),H={class:"demo-space-x"},J={class:"custom-loader"},K=f({__name:"DemoButtonLoaders",setup(u){const t=b([]),r=V=>{t.value[V]=!0,setTimeout(()=>{t.value[V]=!1},3e3)};return(V,a)=>(c(),B("div",H,[o(l,{loading:i(t)[0],disabled:i(t)[0],color:"primary",onClick:a[0]||(a[0]=m=>r(0))},{default:n(()=>a[5]||(a[5]=[e(" Accept Terms ")])),_:1,__:[5]},8,["loading","disabled"]),o(l,{loading:i(t)[1],disabled:i(t)[1],color:"secondary",onClick:a[1]||(a[1]=m=>r(1))},{default:n(()=>[a[6]||(a[6]=e(" Upload ")),o(_,{end:"",icon:"tabler-cloud-upload"})]),_:1,__:[6]},8,["loading","disabled"]),o(l,{loading:i(t)[2],disabled:i(t)[2],color:"success",onClick:a[2]||(a[2]=m=>r(2))},{loader:n(()=>a[7]||(a[7]=[s("span",null,"Loading...",-1)])),default:n(()=>[a[8]||(a[8]=e(" Loader slot "))]),_:1,__:[8]},8,["loading","disabled"]),o(l,{loading:i(t)[3],disabled:i(t)[3],color:"info",onClick:a[3]||(a[3]=m=>r(3))},{loader:n(()=>[s("span",J,[o(_,{icon:"tabler-refresh"})])]),default:n(()=>[a[9]||(a[9]=e(" Icon Loader "))]),_:1,__:[9]},8,["loading","disabled"]),o(l,{loading:i(t)[4],disabled:i(t)[4],color:"warning",icon:"tabler-cloud-upload",onClick:a[4]||(a[4]=m=>r(4))},null,8,["loading","disabled"])]))}}),M=p(K,[["__scopeId","data-v-a71701c5"]]),X={};function Z(u,t){return c(),v(x,null,{default:n(()=>[o(d,{cols:"12",sm:"6"},{default:n(()=>[o(l,{block:""},{default:n(()=>t[0]||(t[0]=[e(" Block Button ")])),_:1,__:[0]})]),_:1}),o(d,{cols:"12",sm:"6"},{default:n(()=>[o(l,{variant:"outlined",block:""},{default:n(()=>t[1]||(t[1]=[e(" Block Button ")])),_:1,__:[1]})]),_:1})]),_:1})}const h=p(X,[["render",Z]]),tt={},ot={class:"demo-space-x"};function nt(u,t){return c(),B("div",ot,[o(l,{size:"x-large"},{default:n(()=>t[0]||(t[0]=[e(" Extra large Button ")])),_:1,__:[0]}),o(l,{color:"success",size:"large"},{default:n(()=>t[1]||(t[1]=[e(" Large Button ")])),_:1,__:[1]}),o(l,{color:"info"},{default:n(()=>t[2]||(t[2]=[e(" Normal Button ")])),_:1,__:[2]}),o(l,{size:"small",color:"warning"},{default:n(()=>t[3]||(t[3]=[e(" Small Button ")])),_:1,__:[3]}),o(l,{size:"x-small",color:"error"},{default:n(()=>t[4]||(t[4]=[e(" Extra small Button ")])),_:1,__:[4]})])}const et=p(tt,[["render",nt]]),lt={},rt={class:"demo-space-x"};function at(u,t){return c(),B("div",rt,[o(l,{icon:"tabler-briefcase",rounded:""}),o(l,{variant:"tonal",icon:"tabler-user-plus"}),o(l,{icon:"tabler-search",variant:"outlined",color:"success"}),o(l,{icon:"tabler-thumb-up",variant:"text",color:"info"}),o(l,{icon:"tabler-star",variant:"tonal",color:"success",rounded:""}),o(l,{icon:"tabler-heart",variant:"text",color:"error"})])}const st=p(lt,[["render",at]]),it={},dt={class:"demo-space-x"};function ct(u,t){return c(),B("div",dt,[o(l,null,{default:n(()=>[t[0]||(t[0]=e(" Accept ")),o(_,{end:"",icon:"tabler-checkbox"})]),_:1,__:[0]}),o(l,{color:"secondary"},{default:n(()=>[o(_,{start:"",icon:"tabler-circle-minus"}),t[1]||(t[1]=e("Cancel "))]),_:1,__:[1]}),o(l,{color:"success"},{default:n(()=>[t[2]||(t[2]=e(" Upload ")),o(_,{end:"",icon:"tabler-cloud-upload"})]),_:1,__:[2]}),o(l,{color:"info"},{default:n(()=>[o(_,{start:"",icon:"tabler-arrow-left"}),t[3]||(t[3]=e(" Back "))]),_:1,__:[3]}),o(l,{color:"warning"},{default:n(()=>[o(_,{icon:"tabler-settings"})]),_:1}),o(l,{color:"error"},{default:n(()=>[o(_,{icon:"tabler-circle-off"})]),_:1})])}const ut=p(it,[["render",ct]]),pt={},Bt={class:"demo-space-x"};function Vt(u,t){return c(),B("div",Bt,[o(l,{variant:"tonal"},{default:n(()=>t[0]||(t[0]=[e(" Primary ")])),_:1,__:[0]}),o(l,{color:"secondary",variant:"tonal"},{default:n(()=>t[1]||(t[1]=[e(" Secondary ")])),_:1,__:[1]}),o(l,{color:"success",variant:"tonal"},{default:n(()=>t[2]||(t[2]=[e(" Success ")])),_:1,__:[2]}),o(l,{color:"info",variant:"tonal"},{default:n(()=>t[3]||(t[3]=[e(" Info ")])),_:1,__:[3]}),o(l,{color:"warning",variant:"tonal"},{default:n(()=>t[4]||(t[4]=[e(" Warning ")])),_:1,__:[4]}),o(l,{color:"error",variant:"tonal"},{default:n(()=>t[5]||(t[5]=[e(" Error ")])),_:1,__:[5]})])}const _t=p(pt,[["render",Vt]]),mt={},ft={class:"demo-space-x"};function vt(u,t){return c(),B("div",ft,[o(l,{variant:"plain"},{default:n(()=>t[0]||(t[0]=[e(" Primary ")])),_:1,__:[0]}),o(l,{color:"secondary",variant:"plain"},{default:n(()=>t[1]||(t[1]=[e(" Secondary ")])),_:1,__:[1]}),o(l,{color:"success",variant:"plain"},{default:n(()=>t[2]||(t[2]=[e(" Success ")])),_:1,__:[2]}),o(l,{color:"info",variant:"plain"},{default:n(()=>t[3]||(t[3]=[e(" Info ")])),_:1,__:[3]}),o(l,{color:"warning",variant:"plain"},{default:n(()=>t[4]||(t[4]=[e(" Warning ")])),_:1,__:[4]}),o(l,{color:"error",variant:"plain"},{default:n(()=>t[5]||(t[5]=[e(" Error ")])),_:1,__:[5]})])}const gt=p(mt,[["render",vt]]),bt={},xt={class:"demo-space-x"};function yt(u,t){return c(),B("div",xt,[o(l,{variant:"text"},{default:n(()=>t[0]||(t[0]=[e(" Primary ")])),_:1,__:[0]}),o(l,{variant:"text",color:"secondary"},{default:n(()=>t[1]||(t[1]=[e(" Secondary ")])),_:1,__:[1]}),o(l,{variant:"text",color:"success"},{default:n(()=>t[2]||(t[2]=[e(" Success ")])),_:1,__:[2]}),o(l,{variant:"text",color:"info"},{default:n(()=>t[3]||(t[3]=[e(" Info ")])),_:1,__:[3]}),o(l,{variant:"text",color:"warning"},{default:n(()=>t[4]||(t[4]=[e(" Warning ")])),_:1,__:[4]}),o(l,{variant:"text",color:"error"},{default:n(()=>t[5]||(t[5]=[e(" Error ")])),_:1,__:[5]})])}const kt=p(bt,[["render",yt]]),wt={},St={class:"demo-space-x"};function It(u,t){return c(),B("div",St,[o(l,null,{default:n(()=>t[0]||(t[0]=[e(" Normal Button ")])),_:1,__:[0]}),o(l,{rounded:"lg",color:"secondary"},{default:n(()=>t[1]||(t[1]=[e(" Rounded Button ")])),_:1,__:[1]}),o(l,{rounded:0,color:"success"},{default:n(()=>t[2]||(t[2]=[e(" Tile Button ")])),_:1,__:[2]}),o(l,{rounded:"pill",color:"info"},{default:n(()=>t[3]||(t[3]=[e(" Pill Button ")])),_:1,__:[3]})])}const $t=p(wt,[["render",It]]),Et={},Tt={class:"demo-space-x"};function Pt(u,t){return c(),B("div",Tt,[o(l,{variant:"flat"},{default:n(()=>t[0]||(t[0]=[e(" Primary ")])),_:1,__:[0]}),o(l,{variant:"flat",color:"secondary"},{default:n(()=>t[1]||(t[1]=[e(" Secondary ")])),_:1,__:[1]}),o(l,{variant:"flat",color:"success"},{default:n(()=>t[2]||(t[2]=[e(" Success ")])),_:1,__:[2]}),o(l,{variant:"flat",color:"info"},{default:n(()=>t[3]||(t[3]=[e(" Info ")])),_:1,__:[3]}),o(l,{variant:"flat",color:"warning"},{default:n(()=>t[4]||(t[4]=[e(" Warning ")])),_:1,__:[4]}),o(l,{variant:"flat",color:"error"},{default:n(()=>t[5]||(t[5]=[e(" Error ")])),_:1,__:[5]})])}const Ct=p(Et,[["render",Pt]]),Lt={},Wt={class:"demo-space-x"};function jt(u,t){return c(),B("div",Wt,[o(l,{variant:"outlined"},{default:n(()=>t[0]||(t[0]=[e(" Primary ")])),_:1,__:[0]}),o(l,{variant:"outlined",color:"secondary"},{default:n(()=>t[1]||(t[1]=[e(" Secondary ")])),_:1,__:[1]}),o(l,{variant:"outlined",color:"success"},{default:n(()=>t[2]||(t[2]=[e(" Success ")])),_:1,__:[2]}),o(l,{variant:"outlined",color:"info"},{default:n(()=>t[3]||(t[3]=[e(" Info ")])),_:1,__:[3]}),o(l,{variant:"outlined",color:"warning"},{default:n(()=>t[4]||(t[4]=[e(" Warning ")])),_:1,__:[4]}),o(l,{variant:"outlined",color:"error"},{default:n(()=>t[5]||(t[5]=[e(" Error ")])),_:1,__:[5]})])}const Dt=p(Lt,[["render",jt]]),zt={},Nt={class:"demo-space-x"};function Rt(u,t){return c(),B("div",Nt,[o(l,{color:"primary"},{default:n(()=>t[0]||(t[0]=[e(" Primary ")])),_:1,__:[0]}),o(l,{color:"secondary"},{default:n(()=>t[1]||(t[1]=[e(" Secondary ")])),_:1,__:[1]}),o(l,{color:"success"},{default:n(()=>t[2]||(t[2]=[e(" Success ")])),_:1,__:[2]}),o(l,{color:"info"},{default:n(()=>t[3]||(t[3]=[e(" Info ")])),_:1,__:[3]}),o(l,{color:"warning"},{default:n(()=>t[4]||(t[4]=[e(" Warning ")])),_:1,__:[4]}),o(l,{color:"error"},{default:n(()=>t[5]||(t[5]=[e(" Error ")])),_:1,__:[5]})])}const Ut=p(zt,[["render",Rt]]),Ot={ts:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VBtn block>
        Block Button
      </VBtn>
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VBtn
        variant="outlined"
        block
      >
        Block Button
      </VBtn>
    </VCol>
  </VRow>
</template>
`,js:`<template>
  <VRow>
    <VCol
      cols="12"
      sm="6"
    >
      <VBtn block>
        Block Button
      </VBtn>
    </VCol>

    <VCol
      cols="12"
      sm="6"
    >
      <VBtn
        variant="outlined"
        block
      >
        Block Button
      </VBtn>
    </VCol>
  </VRow>
</template>
`},At={ts:`<template>
  <div class="demo-space-x">
    <VBtn color="primary">
      Primary
    </VBtn>
    <VBtn color="secondary">
      Secondary
    </VBtn>
    <VBtn color="success">
      Success
    </VBtn>
    <VBtn color="info">
      Info
    </VBtn>
    <VBtn color="warning">
      Warning
    </VBtn>
    <VBtn color="error">
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn color="primary">
      Primary
    </VBtn>
    <VBtn color="secondary">
      Secondary
    </VBtn>
    <VBtn color="success">
      Success
    </VBtn>
    <VBtn color="info">
      Info
    </VBtn>
    <VBtn color="warning">
      Warning
    </VBtn>
    <VBtn color="error">
      Error
    </VBtn>
  </div>
</template>
`},qt={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="flat">
      Primary
    </VBtn>

    <VBtn
      variant="flat"
      color="secondary"
    >
      Secondary
    </VBtn>

    <VBtn
      variant="flat"
      color="success"
    >
      Success
    </VBtn>

    <VBtn
      variant="flat"
      color="info"
    >
      Info
    </VBtn>

    <VBtn
      variant="flat"
      color="warning"
    >
      Warning
    </VBtn>

    <VBtn
      variant="flat"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="flat">
      Primary
    </VBtn>

    <VBtn
      variant="flat"
      color="secondary"
    >
      Secondary
    </VBtn>

    <VBtn
      variant="flat"
      color="success"
    >
      Success
    </VBtn>

    <VBtn
      variant="flat"
      color="info"
    >
      Info
    </VBtn>

    <VBtn
      variant="flat"
      color="warning"
    >
      Warning
    </VBtn>

    <VBtn
      variant="flat"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`},Gt={ts:`<script lang="ts" setup>
const toggleExclusive = ref(1)
<\/script>

<template>
  <VBtnToggle
    v-model="toggleExclusive"
    density="comfortable"
  >
    <VBtn icon="tabler-align-left" />
    <VBtn icon="tabler-align-center" />
    <VBtn icon="tabler-align-right" />
    <VBtn icon="tabler-align-justified" />
  </VBtnToggle>
</template>
`,js:`<script setup>
const toggleExclusive = ref(1)
<\/script>

<template>
  <VBtnToggle
    v-model="toggleExclusive"
    density="comfortable"
  >
    <VBtn icon="tabler-align-left" />
    <VBtn icon="tabler-align-center" />
    <VBtn icon="tabler-align-right" />
    <VBtn icon="tabler-align-justified" />
  </VBtnToggle>
</template>
`},Qt={ts:`<template>
  <div class="demo-space-x">
    <VBtn>
      Accept
      <VIcon
        end
        icon="tabler-checkbox"
      />
    </VBtn>

    <VBtn color="secondary">
      <VIcon
        start
        icon="tabler-circle-minus"
      />Cancel
    </VBtn>

    <VBtn color="success">
      Upload
      <VIcon
        end
        icon="tabler-cloud-upload"
      />
    </VBtn>

    <VBtn color="info">
      <VIcon
        start
        icon="tabler-arrow-left"
      />
      Back
    </VBtn>

    <VBtn color="warning">
      <VIcon icon="tabler-settings" />
    </VBtn>

    <VBtn color="error">
      <VIcon icon="tabler-circle-off" />
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn>
      Accept
      <VIcon
        end
        icon="tabler-checkbox"
      />
    </VBtn>

    <VBtn color="secondary">
      <VIcon
        start
        icon="tabler-circle-minus"
      />Cancel
    </VBtn>

    <VBtn color="success">
      Upload
      <VIcon
        end
        icon="tabler-cloud-upload"
      />
    </VBtn>

    <VBtn color="info">
      <VIcon
        start
        icon="tabler-arrow-left"
      />
      Back
    </VBtn>

    <VBtn color="warning">
      <VIcon icon="tabler-settings" />
    </VBtn>

    <VBtn color="error">
      <VIcon icon="tabler-circle-off" />
    </VBtn>
  </div>
</template>
`},Yt={ts:`<template>
  <div class="demo-space-x">
    <VBtn
      icon="tabler-briefcase"
      rounded
    />

    <VBtn
      variant="tonal"
      icon="tabler-user-plus"
    />

    <VBtn
      icon="tabler-search"
      variant="outlined"
      color="success"
    />

    <VBtn
      icon="tabler-thumb-up"
      variant="text"
      color="info"
    />

    <VBtn
      icon="tabler-star"
      variant="tonal"
      color="success"
      rounded
    />

    <VBtn
      icon="tabler-heart"
      variant="text"
      color="error"
    />
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn
      icon="tabler-briefcase"
      rounded
    />

    <VBtn
      variant="tonal"
      icon="tabler-user-plus"
    />

    <VBtn
      icon="tabler-search"
      variant="outlined"
      color="success"
    />

    <VBtn
      icon="tabler-thumb-up"
      variant="text"
      color="info"
    />

    <VBtn
      icon="tabler-star"
      variant="tonal"
      color="success"
      rounded
    />

    <VBtn
      icon="tabler-heart"
      variant="text"
      color="error"
    />
  </div>
</template>
`},Ft={ts:`<template>
  <div class="demo-space-x">
    <VBtn href="https://pixinvent.com/">
      String Literal
    </VBtn>

    <VBtn
      href="https://pixinvent.com/"
      target="_blank"
      rel="noopener noreferrer"
    >
      Open New Tab
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn href="https://pixinvent.com/">
      String Literal
    </VBtn>

    <VBtn
      href="https://pixinvent.com/"
      target="_blank"
      rel="noopener noreferrer"
    >
      Open New Tab
    </VBtn>
  </div>
</template>
`},Ht={ts:`<script lang="ts" setup>
const loadings = ref<boolean[]>([])

const load = (i: number) => {
  loadings.value[i] = true
  setTimeout(() => {
    loadings.value[i] = false
  }, 3000)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VBtn
      :loading="loadings[0]"
      :disabled="loadings[0]"
      color="primary"
      @click="load(0)"
    >
      Accept Terms
    </VBtn>

    <VBtn
      :loading="loadings[1]"
      :disabled="loadings[1]"
      color="secondary"
      @click="load(1)"
    >
      Upload
      <VIcon
        end
        icon="tabler-cloud-upload"
      />
    </VBtn>

    <VBtn
      :loading="loadings[2]"
      :disabled="loadings[2]"
      color="success"
      @click="load(2)"
    >
      Loader slot
      <template #loader>
        <span>Loading...</span>
      </template>
    </VBtn>

    <VBtn
      :loading="loadings[3]"
      :disabled="loadings[3]"
      color="info"
      @click="load(3)"
    >
      Icon Loader
      <template #loader>
        <span class="custom-loader">
          <VIcon icon="tabler-refresh" />
        </span>
      </template>
    </VBtn>

    <VBtn
      :loading="loadings[4]"
      :disabled="loadings[4]"
      color="warning"
      icon="tabler-cloud-upload"
      @click="load(4)"
    />
  </div>
</template>

  <style lang="scss" scoped>
  .custom-loader {
    display: flex;
    animation: loader 1s infinite;
  }

  @keyframes loader {
    from {
      transform: rotate(0);
    }

    to {
      transform: rotate(360deg);
    }
  }
  </style>
`,js:`<script setup>
const loadings = ref([])

const load = i => {
  loadings.value[i] = true
  setTimeout(() => {
    loadings.value[i] = false
  }, 3000)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VBtn
      :loading="loadings[0]"
      :disabled="loadings[0]"
      color="primary"
      @click="load(0)"
    >
      Accept Terms
    </VBtn>

    <VBtn
      :loading="loadings[1]"
      :disabled="loadings[1]"
      color="secondary"
      @click="load(1)"
    >
      Upload
      <VIcon
        end
        icon="tabler-cloud-upload"
      />
    </VBtn>

    <VBtn
      :loading="loadings[2]"
      :disabled="loadings[2]"
      color="success"
      @click="load(2)"
    >
      Loader slot
      <template #loader>
        <span>Loading...</span>
      </template>
    </VBtn>

    <VBtn
      :loading="loadings[3]"
      :disabled="loadings[3]"
      color="info"
      @click="load(3)"
    >
      Icon Loader
      <template #loader>
        <span class="custom-loader">
          <VIcon icon="tabler-refresh" />
        </span>
      </template>
    </VBtn>

    <VBtn
      :loading="loadings[4]"
      :disabled="loadings[4]"
      color="warning"
      icon="tabler-cloud-upload"
      @click="load(4)"
    />
  </div>
</template>

  <style lang="scss" scoped>
  .custom-loader {
    display: flex;
    animation: loader 1s infinite;
  }

  @keyframes loader {
    from {
      transform: rotate(0);
    }

    to {
      transform: rotate(360deg);
    }
  }
  </style>
`},Jt={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="outlined">
      Primary
    </VBtn>
    <VBtn
      variant="outlined"
      color="secondary"
    >
      Secondary
    </VBtn>
    <VBtn
      variant="outlined"
      color="success"
    >
      Success
    </VBtn>
    <VBtn
      variant="outlined"
      color="info"
    >
      Info
    </VBtn>
    <VBtn
      variant="outlined"
      color="warning"
    >
      Warning
    </VBtn>
    <VBtn
      variant="outlined"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="outlined">
      Primary
    </VBtn>
    <VBtn
      variant="outlined"
      color="secondary"
    >
      Secondary
    </VBtn>
    <VBtn
      variant="outlined"
      color="success"
    >
      Success
    </VBtn>
    <VBtn
      variant="outlined"
      color="info"
    >
      Info
    </VBtn>
    <VBtn
      variant="outlined"
      color="warning"
    >
      Warning
    </VBtn>
    <VBtn
      variant="outlined"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`},Kt={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="plain">
      Primary
    </VBtn>

    <VBtn
      color="secondary"
      variant="plain"
    >
      Secondary
    </VBtn>

    <VBtn
      color="success"
      variant="plain"
    >
      Success
    </VBtn>

    <VBtn
      color="info"
      variant="plain"
    >
      Info
    </VBtn>

    <VBtn
      color="warning"
      variant="plain"
    >
      Warning
    </VBtn>

    <VBtn
      color="error"
      variant="plain"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="plain">
      Primary
    </VBtn>

    <VBtn
      color="secondary"
      variant="plain"
    >
      Secondary
    </VBtn>

    <VBtn
      color="success"
      variant="plain"
    >
      Success
    </VBtn>

    <VBtn
      color="info"
      variant="plain"
    >
      Info
    </VBtn>

    <VBtn
      color="warning"
      variant="plain"
    >
      Warning
    </VBtn>

    <VBtn
      color="error"
      variant="plain"
    >
      Error
    </VBtn>
  </div>
</template>
`},Mt={ts:`<template>
  <div class="demo-space-x">
    <VBtn>
      Normal Button
    </VBtn>
    <VBtn
      rounded="lg"
      color="secondary"
    >
      Rounded Button
    </VBtn>
    <VBtn
      :rounded="0"
      color="success"
    >
      Tile Button
    </VBtn>
    <VBtn
      rounded="pill"
      color="info"
    >
      Pill Button
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn>
      Normal Button
    </VBtn>
    <VBtn
      rounded="lg"
      color="secondary"
    >
      Rounded Button
    </VBtn>
    <VBtn
      :rounded="0"
      color="success"
    >
      Tile Button
    </VBtn>
    <VBtn
      rounded="pill"
      color="info"
    >
      Pill Button
    </VBtn>
  </div>
</template>
`},Xt={ts:`<template>
  <div class="demo-space-x">
    <VBtn to="alert">
      String Literal
    </VBtn>

    <VBtn
      color="warning"
      :to="{ path: 'alert' }"
    >
      Object Path
    </VBtn>

    <VBtn
      color="success"
      :to="{ name: 'components-alert' }"
    >
      Named Router
    </VBtn>

    <VBtn
      color="secondary"
      :to="{ path: 'alert', query: { plan: 'private' } }"
    >
      With Query
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn to="alert">
      String Literal
    </VBtn>

    <VBtn
      color="warning"
      :to="{ path: 'alert' }"
    >
      Object Path
    </VBtn>

    <VBtn
      color="success"
      :to="{ name: 'components-alert' }"
    >
      Named Router
    </VBtn>

    <VBtn
      color="secondary"
      :to="{ path: 'alert', query: { plan: 'private' } }"
    >
      With Query
    </VBtn>
  </div>
</template>
`},Zt={ts:`<template>
  <div class="demo-space-x">
    <VBtn size="x-large">
      Extra large Button
    </VBtn>

    <VBtn
      color="success"
      size="large"
    >
      Large Button
    </VBtn>

    <VBtn color="info">
      Normal Button
    </VBtn>

    <VBtn
      size="small"
      color="warning"
    >
      Small Button
    </VBtn>

    <VBtn
      size="x-small"
      color="error"
    >
      Extra small Button
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn size="x-large">
      Extra large Button
    </VBtn>

    <VBtn
      color="success"
      size="large"
    >
      Large Button
    </VBtn>

    <VBtn color="info">
      Normal Button
    </VBtn>

    <VBtn
      size="small"
      color="warning"
    >
      Small Button
    </VBtn>

    <VBtn
      size="x-small"
      color="error"
    >
      Extra small Button
    </VBtn>
  </div>
</template>
`},ht={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="text">
      Primary
    </VBtn>

    <VBtn
      variant="text"
      color="secondary"
    >
      Secondary
    </VBtn>

    <VBtn
      variant="text"
      color="success"
    >
      Success
    </VBtn>

    <VBtn
      variant="text"
      color="info"
    >
      Info
    </VBtn>

    <VBtn
      variant="text"
      color="warning"
    >
      Warning
    </VBtn>

    <VBtn
      variant="text"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="text">
      Primary
    </VBtn>

    <VBtn
      variant="text"
      color="secondary"
    >
      Secondary
    </VBtn>

    <VBtn
      variant="text"
      color="success"
    >
      Success
    </VBtn>

    <VBtn
      variant="text"
      color="info"
    >
      Info
    </VBtn>

    <VBtn
      variant="text"
      color="warning"
    >
      Warning
    </VBtn>

    <VBtn
      variant="text"
      color="error"
    >
      Error
    </VBtn>
  </div>
</template>
`},to={ts:`<template>
  <div class="demo-space-x">
    <VBtn variant="tonal">
      Primary
    </VBtn>

    <VBtn
      color="secondary"
      variant="tonal"
    >
      Secondary
    </VBtn>

    <VBtn
      color="success"
      variant="tonal"
    >
      Success
    </VBtn>

    <VBtn
      color="info"
      variant="tonal"
    >
      Info
    </VBtn>

    <VBtn
      color="warning"
      variant="tonal"
    >
      Warning
    </VBtn>

    <VBtn
      color="error"
      variant="tonal"
    >
      Error
    </VBtn>
  </div>
</template>
`,js:`<template>
  <div class="demo-space-x">
    <VBtn variant="tonal">
      Primary
    </VBtn>

    <VBtn
      color="secondary"
      variant="tonal"
    >
      Secondary
    </VBtn>

    <VBtn
      color="success"
      variant="tonal"
    >
      Success
    </VBtn>

    <VBtn
      color="info"
      variant="tonal"
    >
      Info
    </VBtn>

    <VBtn
      color="warning"
      variant="tonal"
    >
      Warning
    </VBtn>

    <VBtn
      color="error"
      variant="tonal"
    >
      Error
    </VBtn>
  </div>
</template>
`},Vo=f({__name:"button",setup(u){return(t,r)=>{const V=Ut,a=N,m=Dt,y=Ct,k=$t,w=kt,S=gt,I=_t,$=ut,E=st,T=et,P=h,C=M,L=F,W=q,j=R;return c(),v(x,null,{default:n(()=>[o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Colors",code:i(At)},{default:n(()=>[r[0]||(r[0]=s("p",null,[e("The "),s("code",null,"color"),e(" prop is used to change the background color of the alert.")],-1)),o(V)]),_:1,__:[0]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Outlined",code:i(Jt)},{default:n(()=>[r[1]||(r[1]=s("p",null,[e("The "),s("code",null,"outlined"),e(" variant option is used to create outlined buttons.")],-1)),o(m)]),_:1,__:[1]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Flat",code:i(qt)},{default:n(()=>[r[2]||(r[2]=s("p",null,[e("The "),s("code",null,"flat"),e(" buttons still maintain their background color, but have no box shadow.")],-1)),o(y)]),_:1,__:[2]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Rounded",code:i(Mt)},{default:n(()=>[r[3]||(r[3]=s("p",null,[e("Use the "),s("code",null,"rounded"),e(" prop to control the border radius of buttons.")],-1)),o(k)]),_:1,__:[3]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Text",code:i(ht)},{default:n(()=>[r[4]||(r[4]=s("p",null,[e("Use "),s("code",null,"text"),e(" variant option to create text button. Text buttons have no box shadow and no background.")],-1)),o(w)]),_:1,__:[4]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Plain",code:i(Kt)},{default:n(()=>[r[5]||(r[5]=s("p",null,[e("Use "),s("code",null,"plain"),e(" variant option to a create a plain button. Plain buttons have a lower baseline opacity that reacts to hover and focus.")],-1)),o(S)]),_:1,__:[5]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Tonal",code:i(to)},{default:n(()=>[r[6]||(r[6]=s("p",null,[e("Use "),s("code",null,"tonal"),e(" variant option to a create a light background button.")],-1)),o(I)]),_:1,__:[6]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Icon",code:i(Qt)},{default:n(()=>[r[7]||(r[7]=s("p",null,"Icons can be used inside of buttons to add emphasis to the action.",-1)),o($)]),_:1,__:[7]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Icon Only",code:i(Yt)},{default:n(()=>[r[8]||(r[8]=s("p",null,[e("Use "),s("code",null,"VIcon"),e(" component inside button to create buttons that looks like rest of the theme.")],-1)),o(E)]),_:1,__:[8]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Sizing",code:i(Zt)},{default:n(()=>[r[9]||(r[9]=s("p",null,"Buttons can be given different sizing options to fit a multitude of scenarios.",-1)),o(T)]),_:1,__:[9]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Block",code:i(Ot)},{default:n(()=>[r[10]||(r[10]=s("p",null,[e("The "),s("code",null,"block"),e(" prop allows buttons to extend the full available width.")],-1)),o(P)]),_:1,__:[10]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Loaders",code:i(Ht)},{default:n(()=>[r[11]||(r[11]=s("p",null,[e("Using the "),s("code",null,"loading"),e(" prop, you can notify a user that there is processing taking place. The default behavior is to use a "),s("code",null,"v-progress-circular"),e(" component but this can be customized.")],-1)),o(C)]),_:1,__:[11]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Router",code:i(Xt)},{default:n(()=>[r[13]||(r[13]=s("p",null,[e("Use "),s("code",null,"to"),e(" prop to create button with router support.")],-1)),o(g,{color:"warning",variant:"tonal",class:"mb-4"},{default:n(()=>r[12]||(r[12]=[e(" Note: On click of the link button, You will get redirected to another page. ")])),_:1,__:[12]}),o(L)]),_:1,__:[13]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Link",code:i(Ft)},{default:n(()=>[r[15]||(r[15]=s("p",null,[e("Designates that the component is a link. This is automatic when using the "),s("code",null,"href"),e(" or "),s("code",null,"to"),e(" prop.")],-1)),o(g,{color:"warning",variant:"tonal",class:"mb-4"},{default:n(()=>r[14]||(r[14]=[e(" Note: On click of the link button, You will get redirected to another page. ")])),_:1,__:[14]}),o(W)]),_:1,__:[15]},8,["code"])]),_:1}),o(d,{cols:"12"},{default:n(()=>[o(a,{title:"Group",code:i(Gt)},{default:n(()=>[r[16]||(r[16]=s("p",null,[e(" Wrap buttons with the "),s("code",null,"v-btn-toggle"),e(" component to create a group button. You can add a visual divider between buttons with the "),s("code",null,"divided"),e(" prop. ")],-1)),o(j)]),_:1,__:[16]},8,["code"])]),_:1})]),_:1})}}});export{Vo as default};
