import{_ as S}from"./AppTextField.vue_vue_type_script_setup_true_lang-cll0KdiE.js";import{a as x,V as C}from"./VRow-CWZpCBwA.js";import{V as b}from"./VCheckbox-Dff0MBOJ.js";import{d as k,r as p,c as v,o as h,b as l,f as i,m as a,au as u,F as V,g,e as r,t as d,q as O,aM as I,v as U,i as z}from"./main-C-XcYpJA.js";import{V as $}from"./VTooltip-DiV9RwYR.js";import{_ as A}from"./AppCardCode.vue_vue_type_style_index_0_lang-C1Qo0vHr.js";import"./form-DuADsac0.js";import"./VTextField-CSI95_ey.js";/* empty css                   */import"./VCounter-C_LbNd2E.js";import"./VImg-D1uZhlk0.js";import"./VField-DIWiONsX.js";import"./easing-Bybner-F.js";import"./VInput-BTftOa-l.js";import"./forwardRefs-C-GTDzx5.js";/* empty css              */import"./VCheckboxBtn-BSH2AfDu.js";import"./VSelectionControl-CAJyj1-L.js";import"./VOverlay-EFD0dTaV.js";import"./lazy-CYX4z4FS.js";import"./scopeId-Cz5VrSPP.js";import"./vue3-perfect-scrollbar-BgABJ6kh.js";import"./VCard-DISeXwr4.js";import"./VAvatar-D2epmcpy.js";import"./VCardText-CEySelaG.js";import"./VDivider-Bk49LTaU.js";const D=k({__name:"DemoCheckboxInlineTextField",setup(f){const t=p(!0),e=p(!1);return(c,o)=>{const n=S;return h(),v(V,null,[l(C,null,{default:i(()=>[l(x,{sm:"1",cols:"2",class:"d-flex align-end"},{default:i(()=>[l(b,{modelValue:a(t),"onUpdate:modelValue":o[0]||(o[0]=s=>u(t)?t.value=s:null)},null,8,["modelValue"])]),_:1}),l(x,{sm:"11",cols:"10"},{default:i(()=>[l(n,{label:"Include files",placeholder:"Placeholder Text"})]),_:1})]),_:1}),l(C,null,{default:i(()=>[l(x,{cols:"2",sm:"1",class:"d-flex align-end"},{default:i(()=>[l(b,{modelValue:a(e),"onUpdate:modelValue":o[1]||(o[1]=s=>u(e)?e.value=s:null)},null,8,["modelValue"])]),_:1}),l(x,{cols:"10",sm:"11"},{default:i(()=>[l(n,{disabled:!a(e),label:"I only work if you check the box",placeholder:"Placeholder Text"},null,8,["disabled"])]),_:1})]),_:1})],64)}}}),J=k({__name:"DemoCheckboxLabelSlot",setup(f){const t=p(!1);return(e,c)=>(h(),g(b,{modelValue:a(t),"onUpdate:modelValue":c[1]||(c[1]=o=>u(t)?t.value=o:null)},{label:i(()=>[r("div",null,[c[3]||(c[3]=d(" I agree that ")),l($,{location:"bottom"},{activator:i(({props:o})=>[r("a",O({href:"https://vuetifyjs.com/",target:"_blank",rel:"noopener noreferrer"},o,{onClick:c[0]||(c[0]=I(()=>{},["stop"]))})," Vuetify ",16)]),default:i(()=>[c[2]||(c[2]=d(" Opens in new window "))]),_:1,__:[2]}),c[4]||(c[4]=d(" is awesome "))])]),_:1},8,["modelValue"]))}}),F={class:"demo-space-x"},E=k({__name:"DemoCheckboxStates",setup(f){const t=p(!0),e=p(!0),c=p(!0),o=p(!1);return(n,s)=>(h(),v("div",F,[l(b,{modelValue:a(t),"onUpdate:modelValue":s[0]||(s[0]=m=>u(t)?t.value=m:null),label:"On"},null,8,["modelValue"]),l(b,{modelValue:a(o),"onUpdate:modelValue":s[1]||(s[1]=m=>u(o)?o.value=m:null),label:"Off"},null,8,["modelValue"]),l(b,{indeterminate:a(e),"onUpdate:indeterminate":s[2]||(s[2]=m=>u(e)?e.value=m:null),modelValue:a(e),"onUpdate:modelValue":s[3]||(s[3]=m=>u(e)?e.value=m:null),label:"Indeterminate"},null,8,["indeterminate","modelValue"]),l(b,{"model-value":a(c),disabled:"",label:"On disabled"},null,8,["model-value"]),l(b,{disabled:"",label:"Off disabled"})]))}}),P={class:"demo-space-x"},j=k({__name:"DemoCheckboxCheckboxValue",setup(f){const t=p(1),e=p("Show");return(c,o)=>(h(),v("div",P,[l(b,{modelValue:a(t),"onUpdate:modelValue":o[0]||(o[0]=n=>u(t)?t.value=n:null),"true-value":1,"false-value":0,label:`${a(t).toString()}`},null,8,["modelValue","label"]),l(b,{modelValue:a(e),"onUpdate:modelValue":o[1]||(o[1]=n=>u(e)?e.value=n:null),"true-value":"Show","false-value":"Hide",color:"success",label:`${a(e).toString()}`},null,8,["modelValue","label"])]))}}),R={class:"demo-space-x"},B=k({__name:"DemoCheckboxIcon",setup(f){const t=p(!0),e=p(!0),c=p(!0),o=n=>{const s=n.toString();return s.charAt(0).toUpperCase()+s.slice(1)};return(n,s)=>(h(),v("div",R,[l(b,{modelValue:a(t),"onUpdate:modelValue":s[0]||(s[0]=m=>u(t)?t.value=m:null),label:o(a(t)),"true-icon":"tabler-check","false-icon":"tabler-x"},null,8,["modelValue","label"]),l(b,{modelValue:a(e),"onUpdate:modelValue":s[1]||(s[1]=m=>u(e)?e.value=m:null),label:o(a(e)),"true-icon":"tabler-alarm","false-icon":"tabler-alarm",color:"success"},null,8,["modelValue","label"]),l(b,{modelValue:a(c),"onUpdate:modelValue":s[2]||(s[2]=m=>u(c)?c.value=m:null),label:o(a(c)),"true-icon":"tabler-check","false-icon":"tabler-circle-x",color:"error"},null,8,["modelValue","label"])]))}}),M={class:"demo-space-x"},W={class:"mt-1"},H=k({__name:"DemoCheckboxModelAsArray",setup(f){const t=p(["John"]);return(e,c)=>(h(),v(V,null,[r("div",M,[l(b,{modelValue:a(t),"onUpdate:modelValue":c[0]||(c[0]=o=>u(t)?t.value=o:null),label:"John",value:"John"},null,8,["modelValue"]),l(b,{modelValue:a(t),"onUpdate:modelValue":c[1]||(c[1]=o=>u(t)?t.value=o:null),label:"Jacob",color:"success",value:"Jacob"},null,8,["modelValue"]),l(b,{modelValue:a(t),"onUpdate:modelValue":c[2]||(c[2]=o=>u(t)?t.value=o:null),label:"Johnson",color:"info",value:"Johnson"},null,8,["modelValue"])]),r("p",W,U(a(t)),1)],64))}}),N={class:"demo-space-x"},q=k({__name:"DemoCheckboxColors",setup(f){const t=p(["Primary","Secondary","Success","Info","Warning","Error"]),e=p(["Primary","Secondary","Success","Info","Warning","Error"]);return(c,o)=>(h(),v("div",N,[(h(!0),v(V,null,z(a(t),n=>(h(),g(b,{key:n,modelValue:a(e),"onUpdate:modelValue":o[0]||(o[0]=s=>u(e)?e.value=s:null),label:n,color:n.toLowerCase(),value:n},null,8,["modelValue","label","color","value"]))),128))]))}}),Y={class:"demo-space-x"},G=k({__name:"DemoCheckboxDensity",setup(f){const t=p(!0),e=p(!1),c=o=>{const n=o.toString();return n.charAt(0).toUpperCase()+n.slice(1)};return(o,n)=>(h(),v("div",Y,[l(b,{modelValue:a(t),"onUpdate:modelValue":n[0]||(n[0]=s=>u(t)?t.value=s:null),density:"compact",label:c(a(t))},null,8,["modelValue","label"]),l(b,{modelValue:a(e),"onUpdate:modelValue":n[1]||(n[1]=s=>u(e)?e.value=s:null),density:"compact",label:c(a(e))},null,8,["modelValue","label"])]))}}),K={class:"demo-space-x"},Q=k({__name:"DemoCheckboxBasic",setup(f){const t=p(!0),e=p(!1),c=o=>{const n=o.toString();return n.charAt(0).toUpperCase()+n.slice(1)};return(o,n)=>(h(),v("div",K,[l(b,{modelValue:a(t),"onUpdate:modelValue":n[0]||(n[0]=s=>u(t)?t.value=s:null),label:c(a(t))},null,8,["modelValue","label"]),l(b,{modelValue:a(e),"onUpdate:modelValue":n[1]||(n[1]=s=>u(e)?e.value=s:null),label:c(a(e))},null,8,["modelValue","label"])]))}}),X={ts:`<script lang="ts" setup>
const checkboxOne = ref(true)
const checkboxTwo = ref(false)

const capitalizedLabel = (label: boolean) => {
  const convertLabelText = label.toString()

  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkboxOne"
      :label="capitalizedLabel(checkboxOne)"
    />

    <VCheckbox
      v-model="checkboxTwo"
      :label="capitalizedLabel(checkboxTwo)"
    />
  </div>
</template>
`,js:`<script setup>
const checkboxOne = ref(true)
const checkboxTwo = ref(false)

const capitalizedLabel = label => {
  const convertLabelText = label.toString()
  
  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkboxOne"
      :label="capitalizedLabel(checkboxOne)"
    />

    <VCheckbox
      v-model="checkboxTwo"
      :label="capitalizedLabel(checkboxTwo)"
    />
  </div>
</template>
`},Z={ts:`<script lang="ts" setup>
const checkbox = ref(1)
const checkboxString = ref('Show')
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkbox"
      :true-value="1"
      :false-value="0"
      :label="\`\${checkbox.toString()}\`"
    />

    <VCheckbox
      v-model="checkboxString"
      true-value="Show"
      false-value="Hide"
      color="success"
      :label="\`\${checkboxString.toString()}\`"
    />
  </div>
</template>
`,js:`<script setup>
const checkbox = ref(1)
const checkboxString = ref('Show')
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkbox"
      :true-value="1"
      :false-value="0"
      :label="\`\${checkbox.toString()}\`"
    />

    <VCheckbox
      v-model="checkboxString"
      true-value="Show"
      false-value="Hide"
      color="success"
      :label="\`\${checkboxString.toString()}\`"
    />
  </div>
</template>
`},ee={ts:`<script lang="ts" setup>
const colorCheckbox = ref(['Primary', 'Secondary', 'Success', 'Info', 'Warning', 'Error'])
const selectedCheckbox = ref(['Primary', 'Secondary', 'Success', 'Info', 'Warning', 'Error'])
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-for="color in colorCheckbox"
      :key="color"
      v-model="selectedCheckbox"
      :label="color"
      :color="color.toLowerCase()"
      :value="color"
    />
  </div>
</template>
`,js:`<script setup>
const colorCheckbox = ref([
  'Primary',
  'Secondary',
  'Success',
  'Info',
  'Warning',
  'Error',
])

const selectedCheckbox = ref([
  'Primary',
  'Secondary',
  'Success',
  'Info',
  'Warning',
  'Error',
])
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-for="color in colorCheckbox"
      :key="color"
      v-model="selectedCheckbox"
      :label="color"
      :color="color.toLowerCase()"
      :value="color"
    />
  </div>
</template>
`},le={ts:`<script lang="ts" setup>
const checkboxOne = ref(true)
const checkboxTwo = ref(false)

const capitalizedLabel = (label: boolean) => {
  const convertLabelText = label.toString()

  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkboxOne"
      density="compact"
      :label="capitalizedLabel(checkboxOne)"
    />

    <VCheckbox
      v-model="checkboxTwo"
      density="compact"
      :label="capitalizedLabel(checkboxTwo)"
    />
  </div>
</template>
`,js:`<script setup>
const checkboxOne = ref(true)
const checkboxTwo = ref(false)

const capitalizedLabel = label => {
  const convertLabelText = label.toString()
  
  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="checkboxOne"
      density="compact"
      :label="capitalizedLabel(checkboxOne)"
    />

    <VCheckbox
      v-model="checkboxTwo"
      density="compact"
      :label="capitalizedLabel(checkboxTwo)"
    />
  </div>
</template>
`},oe={ts:`<script lang="ts" setup>
const toggleCheckboxOne = ref(true)
const toggleCheckboxTwo = ref(true)
const toggleCheckboxThree = ref(true)

const capitalizedLabel = (label: boolean) => {
  const convertLabelText = label.toString()

  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="toggleCheckboxOne"
      :label="capitalizedLabel(toggleCheckboxOne)"
      true-icon="tabler-check"
      false-icon="tabler-x"
    />

    <VCheckbox
      v-model="toggleCheckboxTwo"
      :label="capitalizedLabel(toggleCheckboxTwo)"
      true-icon="tabler-alarm"
      false-icon="tabler-alarm"
      color="success"
    />

    <VCheckbox
      v-model="toggleCheckboxThree"
      :label="capitalizedLabel(toggleCheckboxThree)"
      true-icon="tabler-check"
      false-icon="tabler-circle-x"
      color="error"
    />
  </div>
</template>
`,js:`<script setup>
const toggleCheckboxOne = ref(true)
const toggleCheckboxTwo = ref(true)
const toggleCheckboxThree = ref(true)

const capitalizedLabel = label => {
  const convertLabelText = label.toString()
  
  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="toggleCheckboxOne"
      :label="capitalizedLabel(toggleCheckboxOne)"
      true-icon="tabler-check"
      false-icon="tabler-x"
    />

    <VCheckbox
      v-model="toggleCheckboxTwo"
      :label="capitalizedLabel(toggleCheckboxTwo)"
      true-icon="tabler-alarm"
      false-icon="tabler-alarm"
      color="success"
    />

    <VCheckbox
      v-model="toggleCheckboxThree"
      :label="capitalizedLabel(toggleCheckboxThree)"
      true-icon="tabler-check"
      false-icon="tabler-circle-x"
      color="error"
    />
  </div>
</template>
`},te={ts:`<script lang="ts" setup>
const includeFiles = ref(true)
const isInputEnabled = ref(false)
<\/script>

<template>
  <VRow>
    <VCol
      sm="1"
      cols="2"
      class="d-flex align-end"
    >
      <VCheckbox v-model="includeFiles" />
    </VCol>

    <VCol
      sm="11"
      cols="10"
    >
      <AppTextField
        label="Include files"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>

  <VRow>
    <VCol
      cols="2"
      sm="1"
      class="d-flex align-end"
    >
      <VCheckbox v-model="isInputEnabled" />
    </VCol>

    <VCol
      cols="10"
      sm="11"
    >
      <AppTextField
        :disabled="!isInputEnabled"
        label="I only work if you check the box"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`,js:`<script setup>
const includeFiles = ref(true)
const isInputEnabled = ref(false)
<\/script>

<template>
  <VRow>
    <VCol
      sm="1"
      cols="2"
      class="d-flex align-end"
    >
      <VCheckbox v-model="includeFiles" />
    </VCol>

    <VCol
      sm="11"
      cols="10"
    >
      <AppTextField
        label="Include files"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>

  <VRow>
    <VCol
      cols="2"
      sm="1"
      class="d-flex align-end"
    >
      <VCheckbox v-model="isInputEnabled" />
    </VCol>

    <VCol
      cols="10"
      sm="11"
    >
      <AppTextField
        :disabled="!isInputEnabled"
        label="I only work if you check the box"
        placeholder="Placeholder Text"
      />
    </VCol>
  </VRow>
</template>
`},ae={ts:`<script lang="ts" setup>
const checkbox = ref(false)
<\/script>

<template>
  <VCheckbox v-model="checkbox">
    <template #label>
      <div>
        I agree that
        <VTooltip location="bottom">
          <template #activator="{ props }">
            <a
              href="https://vuetifyjs.com/"
              target="_blank"
              rel="noopener noreferrer"
              v-bind="props"
              @click.stop
            >
              Vuetify
            </a>
          </template>
          Opens in new window
        </VTooltip>
        is awesome
      </div>
    </template>
  </VCheckbox>
</template>
`,js:`<script setup>
const checkbox = ref(false)
<\/script>

<template>
  <VCheckbox v-model="checkbox">
    <template #label>
      <div>
        I agree that
        <VTooltip location="bottom">
          <template #activator="{ props }">
            <a
              href="https://vuetifyjs.com/"
              target="_blank"
              rel="noopener noreferrer"
              v-bind="props"
              @click.stop
            >
              Vuetify
            </a>
          </template>
          Opens in new window
        </VTooltip>
        is awesome
      </div>
    </template>
  </VCheckbox>
</template>
`},ce={ts:`<script lang="ts" setup>
const selected = ref(['John'])
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="selected"
      label="John"
      value="John"
    />

    <VCheckbox
      v-model="selected"
      label="Jacob"
      color="success"
      value="Jacob"
    />

    <VCheckbox
      v-model="selected"
      label="Johnson"
      color="info"
      value="Johnson"
    />
  </div>

  <p class="mt-1">
    {{ selected }}
  </p>
</template>
`,js:`<script setup>
const selected = ref(['John'])
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="selected"
      label="John"
      value="John"
    />

    <VCheckbox
      v-model="selected"
      label="Jacob"
      color="success"
      value="Jacob"
    />

    <VCheckbox
      v-model="selected"
      label="Johnson"
      color="info"
      value="Johnson"
    />
  </div>

  <p class="mt-1">
    {{ selected }}
  </p>
</template>
`},se={ts:`<script setup lang="ts">
const toggleCheckbox = ref(true)
const toggleIndeterminateCheckbox = ref(true)
const disabledCheckbox = ref(true)
const toggleOffCheckbox = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="toggleCheckbox"
      label="On"
    />

    <VCheckbox
      v-model="toggleOffCheckbox"
      label="Off"
    />

    <VCheckbox
      v-model:indeterminate="toggleIndeterminateCheckbox"
      v-model="toggleIndeterminateCheckbox"
      label="Indeterminate"
    />

    <VCheckbox
      :model-value="disabledCheckbox"
      disabled
      label="On disabled"
    />

    <VCheckbox
      disabled
      label="Off disabled"
    />
  </div>
</template>
`,js:`<script setup>
const toggleCheckbox = ref(true)
const toggleIndeterminateCheckbox = ref(true)
const disabledCheckbox = ref(true)
const toggleOffCheckbox = ref(false)
<\/script>

<template>
  <div class="demo-space-x">
    <VCheckbox
      v-model="toggleCheckbox"
      label="On"
    />

    <VCheckbox
      v-model="toggleOffCheckbox"
      label="Off"
    />

    <VCheckbox
      v-model:indeterminate="toggleIndeterminateCheckbox"
      v-model="toggleIndeterminateCheckbox"
      label="Indeterminate"
    />

    <VCheckbox
      :model-value="disabledCheckbox"
      disabled
      label="On disabled"
    />

    <VCheckbox
      disabled
      label="Off disabled"
    />
  </div>
</template>
`},$e=k({__name:"checkbox",setup(f){return(t,e)=>{const c=Q,o=A,n=G,s=q,m=H,_=B,T=j,w=E,L=J,y=D;return h(),g(C,{class:"match-height"},{default:i(()=>[l(x,{cols:"12",md:"6"},{default:i(()=>[l(o,{title:"Basic",code:a(X)},{default:i(()=>[e[0]||(e[0]=r("p",null,[r("code",null,"v-checkbox"),d(" in its simplest form provides a toggle between 2 values.")],-1)),l(c)]),_:1,__:[0]},8,["code"])]),_:1}),l(x,{cols:"12",md:"6"},{default:i(()=>[l(o,{title:"Density",code:a(le)},{default:i(()=>[e[1]||(e[1]=r("p",null,[d("Use "),r("code",null,"density"),d(" prop to reduces the input height. Available options are: "),r("code",null,"default"),d(", "),r("code",null,"comfortable"),d(", and "),r("code",null,"compact"),d(".")],-1)),l(n)]),_:1,__:[1]},8,["code"])]),_:1}),l(x,{cols:"12",md:"6"},{default:i(()=>[l(o,{title:"Colors",code:a(ee)},{default:i(()=>[e[2]||(e[2]=r("p",null,[d("Checkboxes can be colored by using any of the builtin colors and contextual names using the "),r("code",null,"color"),d(" prop.")],-1)),l(s)]),_:1,__:[2]},8,["code"])]),_:1}),l(x,{cols:"12",md:"6"},{default:i(()=>[l(o,{title:"Model as array",code:a(ce)},{default:i(()=>[e[3]||(e[3]=r("p",null,[d("Multiple "),r("code",null,"v-checkbox"),d("'s can share the same "),r("code",null,"v-model"),d(" by using an array.")],-1)),l(m)]),_:1,__:[3]},8,["code"])]),_:1}),l(x,{cols:"12",md:"6"},{default:i(()=>[l(o,{title:"Icon",code:a(oe)},{default:i(()=>[e[4]||(e[4]=r("p",null,[d("Use "),r("code",null,"false-icon"),d(" and "),r("code",null,"true-icon"),d(" prop to change the icon on the checkbox.")],-1)),l(_)]),_:1,__:[4]},8,["code"])]),_:1}),l(x,{cols:"12",md:"6"},{default:i(()=>[l(o,{title:"Checkbox Value",code:a(Z)},{default:i(()=>[e[5]||(e[5]=r("p",null,[d("Use "),r("code",null,"false-value"),d(" and "),r("code",null,"true-value"),d(" prop to sets value for truthy and falsy state")],-1)),l(T)]),_:1,__:[5]},8,["code"])]),_:1}),l(x,{cols:"12",md:"6"},{default:i(()=>[l(o,{title:"States",code:a(se)},{default:i(()=>[e[6]||(e[6]=r("p",null,[r("code",null,"v-checkbox"),d(" can have different states such as "),r("code",null,"default"),d(", "),r("code",null,"disabled"),d(", and "),r("code",null,"indeterminate"),d(".")],-1)),l(w)]),_:1,__:[6]},8,["code"])]),_:1}),l(x,{cols:"12",md:"6"},{default:i(()=>[l(o,{title:"Label Slot",code:a(ae)},{default:i(()=>[e[7]||(e[7]=r("p",null,[d("Checkbox labels can be defined in "),r("code",null,"label"),d(" slot - that will allow to use HTML content.")],-1)),l(L)]),_:1,__:[7]},8,["code"])]),_:1}),l(x,{cols:"12",md:"6"},{default:i(()=>[l(o,{title:"Inline text-field",code:a(te)},{default:i(()=>[e[8]||(e[8]=r("p",null,[d("You can place "),r("code",null,"v-checkbox"),d(" in line with other components such as "),r("code",null,"v-text-field"),d(".")],-1)),l(y)]),_:1,__:[8]},8,["code"])]),_:1})]),_:1})}}});export{$e as default};
