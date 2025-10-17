import{aA as n}from"./main-CpsWdFYr.js";/**
 * @license lucide-vue-next v0.546.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const u=t=>t.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase(),p=t=>t.replace(/^([A-Z])|[\s-_]+(\w)/g,(e,o,r)=>r?r.toUpperCase():o.toLowerCase()),k=t=>{const e=p(t);return e.charAt(0).toUpperCase()+e.slice(1)},g=(...t)=>t.filter((e,o,r)=>!!e&&e.trim()!==""&&r.indexOf(e)===o).join(" ").trim(),h=t=>t==="";/**
 * @license lucide-vue-next v0.546.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var s={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":2,"stroke-linecap":"round","stroke-linejoin":"round"};/**
 * @license lucide-vue-next v0.546.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const C=({name:t,iconNode:e,absoluteStrokeWidth:o,"absolute-stroke-width":r,strokeWidth:a,"stroke-width":c,size:i=s.width,color:w=s.stroke,...d},{slots:l})=>n("svg",{...s,...d,width:i,height:i,stroke:w,"stroke-width":h(o)||h(r)||o===!0||r===!0?Number(a||c||s["stroke-width"])*24/Number(i):a||c||s["stroke-width"],class:g("lucide",d.class,...t?[`lucide-${u(k(t))}-icon`,`lucide-${u(t)}`]:["lucide-icon"])},[...e.map(m=>n(...m)),...l.default?[l.default()]:[]]);/**
 * @license lucide-vue-next v0.546.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const f=(t,e)=>(o,{slots:r,attrs:a})=>n(C,{...a,...o,iconNode:e,name:t},r);/**
 * @license lucide-vue-next v0.546.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const A=f("saudi-riyal",[["path",{d:"m20 19.5-5.5 1.2",key:"1aenhr"}],["path",{d:"M14.5 4v11.22a1 1 0 0 0 1.242.97L20 15.2",key:"2rtezt"}],["path",{d:"m2.978 19.351 5.549-1.363A2 2 0 0 0 10 16V2",key:"1kbm92"}],["path",{d:"M20 10 4 13.5",key:"8nums9"}]]);export{A as S};
