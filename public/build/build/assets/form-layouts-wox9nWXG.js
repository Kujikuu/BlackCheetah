import{_ as me}from"./CustomRadiosWithIcon-BZ-DtI_6.js";import{_ as j}from"./AppSelect.vue_vue_type_script_setup_true_lang-DXz23Wgu.js";import{_ as se}from"./AppTextarea.vue_vue_type_script_setup_true_lang-CIe2COFg.js";import{_ as L}from"./AppTextField.vue_vue_type_script_setup_true_lang-BiY8T0bF.js";import{d as T,r as m,g as A,o as g,f as l,e as c,b as e,t as w,a3 as _,J as te,m as t,c as q,F as le,i as de,x as M,K as ae,aM as S,au as i,v as ie,aa as ue}from"./main-BFHJN9nb.js";import{b as pe,V as ne}from"./VCard-DX1TBmaJ.js";import{V as ce}from"./VSpacer-CCpGOsVL.js";import{V as ee}from"./VCardText-DaLxJsvL.js";import{V as x,a as o}from"./VRow-CkpL0RVO.js";import{V as k}from"./VForm-Bn7tXzHN.js";import{V as I}from"./VCheckbox-CfYRqiVX.js";import{V as D,a as N}from"./VRadioGroup-BZrZhxAb.js";import{V as U}from"./VDivider-BkXgjbRp.js";import{V as Ve,a as be}from"./VList-BQIhB7WP.js";import{_ as fe}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{V as O,a as K,b as Q,c as Ce}from"./VExpansionPanels-B_mnEGqi.js";import{V as ve}from"./form-CRnOu3kV.js";import{_ as ye}from"./AppDateTimePicker.vue_vue_type_style_index_0_lang-BB6-Yrdv.js";import{a as xe,V as X}from"./VTabs-BOALpnd-.js";import{a as we,V as Z}from"./VWindowItem-9dFuuoo1.js";import{r as E,e as he}from"./validators-BYiRrju8.js";import{_ as _e}from"./AppAutocomplete.vue_vue_type_script_setup_true_lang-DYl343U3.js";import{_ as ge}from"./AppCardCode.vue_vue_type_style_index_0_lang-Cr7Um3Yy.js";import"./VSelect-DRqAennZ.js";import"./VTextField-cuhL7eKu.js";/* empty css                   */import"./VCounter-Cd8lIrDY.js";import"./VImg-CUvr5mlE.js";import"./VField-5Oc1eDZT.js";import"./easing-Bybner-F.js";import"./VInput-C8j_k2yQ.js";import"./forwardRefs-C-GTDzx5.js";import"./dialog-transition-BI7rMvXb.js";import"./VMenu-S7-3kIyf.js";import"./VOverlay-CCjswVhh.js";import"./lazy-CMH8XMVs.js";import"./scopeId-u705bwh2.js";import"./VCheckboxBtn-B9nMFPWY.js";import"./VSelectionControl-CRxaOcuF.js";import"./VAvatar-0YR6k8GC.js";import"./VChip-YDiTWgu4.js";import"./VSlideGroup-Crru8FDw.js";import"./VTextarea-Bsvjwuux.js";/* empty css              */import"./ssrBoot-BnhyewLe.js";import"./filter-B0dGONQA.js";import"./vue3-perfect-scrollbar-acdO2PlS.js";const Fe={class:"w-100 sticky-header overflow-hidden rounded-t"},ke={class:"d-flex align-center gap-4 flex-wrap bg-custom-background pa-6"},Ae={class:"d-flex align-center gap-4"},Te={class:"d-flex align-center gap-2 my-4"},Re=T({__name:"DemoFormLayoutSticky",setup(R){const C=[{title:"Standard",desc:"Delivery in 3-5 days.",value:"standard",icon:{icon:"tabler-briefcase-2",size:"32"}},{title:"Express",desc:"Delivery within 2 days.",value:"express",icon:{icon:"tabler-rocket",size:"32"}},{title:"Overnight",desc:"Delivery within a days.",value:"overnight",icon:{icon:"tabler-crown",size:"32"}}],f=[{code:"TAKEITALL",desc:"Apply this code to get 15% discount on orders above 20$."},{code:"FESTIVE10",desc:"Apply this code to get 10% discount on all orders."},{code:"MYSTERYDEAL",desc:"Apply this code to get discount between 10% - 50%."}],r=m({fullName:"",email:"",contactNumber:null,altContactNumber:null,address:"",pincode:null,Landmark:"",city:"",state:null,defaultAddress:!1,addressType:"home",deliveryType:"overnight",promoCode:"",paymentMethod:"card",cardNumber:null,cardName:"",cardExDate:"",cardCvv:""});return(V,d)=>{const v=L,a=se,s=j,n=me;return g(),A(ne,{class:"overflow-visible"},{default:l(()=>[c("div",Fe,[c("div",ke,[e(pe,null,{default:l(()=>d[14]||(d[14]=[w("Sticky Action Bar")])),_:1,__:[14]}),e(ce),c("div",null,[e(_,{variant:"tonal",class:"me-4"},{default:l(()=>d[15]||(d[15]=[w(" Back ")])),_:1,__:[15]}),e(_,null,{default:l(()=>d[16]||(d[16]=[w("Place Order")])),_:1,__:[16]})])])]),e(ee,null,{default:l(()=>[e(x,null,{default:l(()=>[e(o,{md:"8",cols:"12",class:"mx-auto"},{default:l(()=>[e(k,null,{default:l(()=>[d[22]||(d[22]=c("h5",{class:"text-h5 mb-6"}," 1. Delivery Address ",-1)),e(x,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(v,{modelValue:t(r).fullName,"onUpdate:modelValue":d[0]||(d[0]=u=>t(r).fullName=u),label:"Full Name",placeholder:"John Doe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(v,{modelValue:t(r).email,"onUpdate:modelValue":d[1]||(d[1]=u=>t(r).email=u),label:"Email",placeholder:"john.doe",suffix:"@example.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(v,{modelValue:t(r).contactNumber,"onUpdate:modelValue":d[2]||(d[2]=u=>t(r).contactNumber=u),label:"Contact Number",placeholder:"658 123 4567",type:"number"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(v,{modelValue:t(r).altContactNumber,"onUpdate:modelValue":d[3]||(d[3]=u=>t(r).altContactNumber=u),label:"Alternate Number",placeholder:"658 123 4567",type:"number"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(a,{modelValue:t(r).address,"onUpdate:modelValue":d[4]||(d[4]=u=>t(r).address=u),label:"Address",placeholder:"1456, Mall Road",rows:"2"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(v,{modelValue:t(r).pincode,"onUpdate:modelValue":d[5]||(d[5]=u=>t(r).pincode=u),label:"Pincode",placeholder:"658468",type:"number"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(v,{modelValue:t(r).Landmark,"onUpdate:modelValue":d[6]||(d[6]=u=>t(r).Landmark=u),label:"Landmark",placeholder:"Nr. Wall Street"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(v,{modelValue:t(r).city,"onUpdate:modelValue":d[7]||(d[7]=u=>t(r).city=u),label:"City",placeholder:"Jackson"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(s,{modelValue:t(r).state,"onUpdate:modelValue":d[8]||(d[8]=u=>t(r).state=u),label:"State",placeholder:"Select State",items:["Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","Florida"]},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(I,{modelValue:t(r).defaultAddress,"onUpdate:modelValue":d[9]||(d[9]=u=>t(r).defaultAddress=u),label:"Use this as default delivery address"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[d[17]||(d[17]=c("p",{class:"text-high-emphasis text-base mb-1"}," Address Type ",-1)),e(D,{modelValue:t(r).addressType,"onUpdate:modelValue":d[10]||(d[10]=u=>t(r).addressType=u),inline:""},{default:l(()=>[e(N,{label:"Home (All day delivery)",value:"home",class:"me-3"}),e(N,{label:"Office (Delivery between 10 AM - 5 PM)",value:"work"})]),_:1},8,["modelValue"])]),_:1,__:[17]})]),_:1}),e(U,{class:"my-6"}),d[23]||(d[23]=c("h5",{class:"text-h5 mb-6"}," 2. Delivery Type ",-1)),e(n,{"selected-radio":t(r).deliveryType,"onUpdate:selectedRadio":d[11]||(d[11]=u=>t(r).deliveryType=u),"radio-content":C,"grid-column":{sm:"4",cols:"12"}},null,8,["selected-radio"]),e(U,{class:"my-6"}),d[24]||(d[24]=c("h5",{class:"text-h5 my-6"}," 3. Apply Promo code ",-1)),c("div",Ae,[e(v,{modelValue:t(r).promoCode,"onUpdate:modelValue":d[12]||(d[12]=u=>t(r).promoCode=u),placeholder:"TAKEITALL"},null,8,["modelValue"]),e(_,null,{default:l(()=>d[18]||(d[18]=[w("Apply")])),_:1,__:[18]})]),c("div",Te,[e(U,{style:{"border-style":"dashed"}}),d[19]||(d[19]=c("span",null,"OR",-1)),e(U,{style:{"border-style":"dashed"}})]),e(Ve,{class:"border rounded py-0",lines:"two"},{default:l(()=>[(g(),q(le,null,de(f,(u,b)=>e(be,{key:u.code,title:u.code,subtitle:u.desc,class:M(b!==0?"border-t":"")},{append:l(()=>[e(_,{variant:"tonal",class:"ms-4"},{default:l(()=>d[20]||(d[20]=[w(" Apply ")])),_:1,__:[20]})]),_:2},1032,["title","subtitle","class"])),64))]),_:1}),e(U,{class:"my-6"}),d[25]||(d[25]=c("h5",{class:"text-h5 mb-6"}," 4. Payment Method ",-1)),e(D,{modelValue:t(r).paymentMethod,"onUpdate:modelValue":d[13]||(d[13]=u=>t(r).paymentMethod=u),inline:"",class:"mb-4"},{default:l(()=>[e(N,{value:"card",label:"Credit/Debit/ATM Card",class:"me-3"}),e(N,{value:"cash-on-delivery",label:"Cash On Delivery"})]),_:1},8,["modelValue"]),te(e(x,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(v,{label:"Card Number",placeholder:"1356 3215 6548 7898"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(v,{label:"Name",placeholder:"John Doe"})]),_:1}),e(o,{cols:"6",md:"3"},{default:l(()=>[e(v,{label:"Exp. Date",placeholder:"MM/YY"})]),_:1}),e(o,{cols:"6",md:"3"},{default:l(()=>[e(v,{label:"CVV Code",placeholder:"654"})]),_:1})]),_:1},512),[[ae,t(r).paymentMethod==="card"]]),te(c("div",null,d[21]||(d[21]=[c("p",null," Cash on delivery is a mode of payment where you make the payment after the goods/services are received. ",-1),c("p",null,"You can pay cash or make the payment via debit/credit card directly to the delivery person.",-1)]),512),[[ae,t(r).paymentMethod==="cash-on-delivery"]])]),_:1,__:[22,23,24,25]})]),_:1})]),_:1})]),_:1})]),_:1})}}}),Ne=fe(Re,[["__scopeId","data-v-77939e33"]]),Le={class:"me-1"},Ie={class:"d-flex gap-4"},Ue=T({__name:"DemoFormLayoutCollapsible",setup(R){const C=["Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","Florida","Georgia","Hawaii"],f=m("home"),r=m("standard"),V=m("credit-debit-card"),d=m(0),v=[{radioValue:"credit-debit-card",radioLabel:"Credit/Debit/ATM Card",icon:"tabler-credit-card"},{radioValue:"cash-on-delivery",radioLabel:"Cash On Delivery",icon:"tabler-help"}];return(a,s)=>{const n=L,u=se,b=j;return g(),A(Ce,{modelValue:t(d),"onUpdate:modelValue":s[6]||(s[6]=h=>i(d)?d.value=h:null)},{default:l(()=>[e(O,null,{default:l(()=>[e(K,null,{default:l(()=>s[7]||(s[7]=[w("Delivery Address")])),_:1,__:[7]}),e(Q,null,{default:l(()=>[e(k,{class:"pt-4 pb-2",onSubmit:S(()=>{},["prevent"])},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(n,{label:"Full Name",placeholder:"John Doe"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(n,{label:"Phone No",type:"number",placeholder:"+966 50 123 4567"})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(u,{label:"Address",placeholder:"1234 Main St, New York, NY 10001, USA",rows:"3"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(n,{label:"Pincode",placeholder:"123456",type:"number"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(n,{label:"Landmark",placeholder:"Near City Mall"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(n,{label:"City",placeholder:"New York"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(b,{items:C,label:"State",placeholder:"Select State"})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(ve,{class:"mb-3"},{default:l(()=>s[8]||(s[8]=[w(" Address Type ")])),_:1,__:[8]}),e(D,{modelValue:t(f),"onUpdate:modelValue":s[0]||(s[0]=h=>i(f)?f.value=h:null),inline:""},{default:l(()=>[c("div",null,[e(N,{label:"Home (All day delivery)",value:"home",class:"me-3"}),e(N,{label:"Office (Delivery between 10 AM - 5 PM)",value:"office"})])]),_:1},8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1})]),_:1}),e(O,null,{default:l(()=>[e(K,null,{default:l(()=>s[9]||(s[9]=[w("Delivery Options")])),_:1,__:[9]}),e(Q,null,{default:l(()=>[e(D,{modelValue:t(r),"onUpdate:modelValue":s[4]||(s[4]=h=>i(r)?r.value=h:null),class:"delivery-options pt-4 pb-2"},{default:l(()=>[c("div",{class:M(["delivery-option d-flex rounded-t",t(r)==="standard"?"active":""]),onClick:s[1]||(s[1]=h=>r.value="standard")},[e(N,{inline:"",value:"standard",class:"mt-n4"}),s[10]||(s[10]=c("div",{class:"w-100"},[c("div",{class:"d-flex justify-space-between"},[c("h6",{class:"text-base font-weight-medium"}," Standard 3-5 Days "),c("h6",{class:"text-base font-weight-medium"}," Free ")]),c("span",{class:"text-sm"},"Friday, 15 Nov - Monday, 18 Nov")],-1))],2),c("div",{class:M(["delivery-option d-flex",t(r)==="express"?"active":""]),onClick:s[2]||(s[2]=h=>r.value="express")},[e(N,{inline:"",class:"mt-n4",value:"express"}),s[11]||(s[11]=c("div",{class:"w-100"},[c("div",{class:"d-flex justify-space-between"},[c("h5",{class:"text-base font-weight-medium"}," Express "),c("h6",{class:"text-base font-weight-medium"}," $5.00 ")]),c("span",{class:"text-sm"},"Friday, 15 Nov - Sunday, 17 Nov")],-1))],2),c("div",{class:M(["delivery-option d-flex rounded-b",t(r)==="overnight"?"active":""]),onClick:s[3]||(s[3]=h=>r.value="overnight")},[e(N,{inline:"",class:"mt-n4",value:"overnight"}),s[12]||(s[12]=c("div",{class:"w-100"},[c("div",{class:"d-flex justify-space-between"},[c("h6",{class:"text-base font-weight-medium"}," Overnight "),c("h6",{class:"text-base font-weight-medium"}," $10.00 ")]),c("span",{class:"text-sm"},"Friday, 15 Nov - Saturday, 16 Nov")],-1))],2)]),_:1},8,["modelValue"])]),_:1})]),_:1}),e(O,null,{default:l(()=>[e(K,null,{default:l(()=>s[13]||(s[13]=[w("Payment Method")])),_:1,__:[13]}),e(Q,null,{default:l(()=>[e(x,null,{default:l(()=>[e(o,{md:"6",cols:"12"},{default:l(()=>[e(k,{class:"pt-4 pb-2"},{default:l(()=>[c("div",null,[e(D,{modelValue:t(V),"onUpdate:modelValue":s[5]||(s[5]=h=>i(V)?V.value=h:null),inline:""},{default:l(()=>[c("div",null,[(g(),q(le,null,de(v,h=>e(N,{key:h.radioValue,value:h.radioValue,class:"me-3"},{label:l(()=>[c("span",Le,ie(h.radioLabel),1),e(ue,{size:"18",icon:h.icon},null,8,["icon"])]),_:2},1032,["value"])),64))])]),_:1},8,["modelValue"])]),t(V)==="credit-debit-card"?(g(),A(x,{key:0},{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(n,{label:"Card Number",type:"number",placeholder:"1234 5678 9012 3456"})]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(n,{label:"Name",placeholder:"john doe"})]),_:1}),e(o,{cols:"6",md:"3"},{default:l(()=>[e(n,{label:"Expiry Date",placeholder:"MM/YY"})]),_:1}),e(o,{cols:"6",md:"3"},{default:l(()=>[e(n,{label:"CVV Code",type:"number",max:"3",placeholder:"123"})]),_:1})]),_:1})):(g(),A(x,{key:1},{default:l(()=>[e(o,null,{default:l(()=>s[14]||(s[14]=[c("div",{class:"text-high-emphasis"}," Cash on Delivery is a type of payment method where the recipient make payment for the order at the time of delivery rather than in advance. ",-1)])),_:1,__:[14]})]),_:1}))]),_:1})]),_:1})]),_:1}),e(U,{class:"my-5"}),c("div",Ie,[e(_,null,{default:l(()=>s[15]||(s[15]=[w("Place Order")])),_:1,__:[15]}),e(_,{color:"secondary",variant:"tonal"},{default:l(()=>s[16]||(s[16]=[w(" Cancel ")])),_:1,__:[16]})])]),_:1})]),_:1})]),_:1},8,["modelValue"])}}}),Se=T({__name:"DemoFormLayoutFormWithTabs",setup(R){const C=m("personal-info"),f=m(""),r=m(""),V=m(),d=m(""),v=m(),a=["USA","Canada","UK","Denmark","Germany","Iceland","Israel","Mexico"],s=["English","German","French","Spanish","Portuguese","Russian","Korean"],n=m(""),u=m(""),b=m(""),h=m(""),P=m(""),H=m(""),J=m(""),$=m(""),W=m(""),Y=m(""),G=m([]),B=m(!1),z=m(!1);return(Ke,p)=>{const F=L,oe=j,re=ye;return g(),q(le,null,[e(xe,{modelValue:t(C),"onUpdate:modelValue":p[0]||(p[0]=y=>i(C)?C.value=y:null)},{default:l(()=>[e(X,{value:"personal-info"},{default:l(()=>p[20]||(p[20]=[w(" Personal Info ")])),_:1,__:[20]}),e(X,{value:"account-details"},{default:l(()=>p[21]||(p[21]=[w(" Account Details ")])),_:1,__:[21]}),e(X,{value:"social-links"},{default:l(()=>p[22]||(p[22]=[w(" Social Links ")])),_:1,__:[22]})]),_:1},8,["modelValue"]),e(ne,{flat:""},{default:l(()=>[e(ee,null,{default:l(()=>[e(we,{modelValue:t(C),"onUpdate:modelValue":p[19]||(p[19]=y=>i(C)?C.value=y:null),class:"disable-tab-transition"},{default:l(()=>[e(Z,{value:"personal-info"},{default:l(()=>[e(k,{class:"mt-2"},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{md:"6",cols:"12"},{default:l(()=>[e(F,{modelValue:t(f),"onUpdate:modelValue":p[1]||(p[1]=y=>i(f)?f.value=y:null),label:"First name",placeholder:"John"},null,8,["modelValue"])]),_:1}),e(o,{md:"6",cols:"12"},{default:l(()=>[e(F,{modelValue:t(r),"onUpdate:modelValue":p[2]||(p[2]=y=>i(r)?r.value=y:null),label:"Last name",placeholder:"Doe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(oe,{modelValue:t(V),"onUpdate:modelValue":p[3]||(p[3]=y=>i(V)?V.value=y:null),items:a,label:"Country",placeholder:"Select Country"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(oe,{modelValue:t(G),"onUpdate:modelValue":p[4]||(p[4]=y=>i(G)?G.value=y:null),items:s,multiple:"",chips:"",clearable:"",label:"Language",placeholder:"Select Language"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(re,{modelValue:t(d),"onUpdate:modelValue":p[5]||(p[5]=y=>i(d)?d.value=y:null),label:"Birth Date",placeholder:"Select Birth Date"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(v),"onUpdate:modelValue":p[6]||(p[6]=y=>i(v)?v.value=y:null),type:"number",label:"Phone No.",placeholder:"+966 50 123 4567"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1}),e(Z,{value:"account-details"},{default:l(()=>[e(k,{class:"mt-2"},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(n),"onUpdate:modelValue":p[7]||(p[7]=y=>i(n)?n.value=y:null),label:"Username",placeholder:"Johndoe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(u),"onUpdate:modelValue":p[8]||(p[8]=y=>i(u)?u.value=y:null),label:"Email",suffix:"@example.com",placeholder:"johndoe@email.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(b),"onUpdate:modelValue":p[9]||(p[9]=y=>i(b)?b.value=y:null),label:"Password",placeholder:"············",type:t(B)?"text":"password",autocomplete:"password","append-inner-icon":t(B)?"tabler-eye-off":"tabler-eye","onClick:appendInner":p[10]||(p[10]=y=>B.value=!t(B))},null,8,["modelValue","type","append-inner-icon"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(h),"onUpdate:modelValue":p[11]||(p[11]=y=>i(h)?h.value=y:null),label:"Confirm Password",autocomplete:"confirm-password",placeholder:"············",type:t(z)?"text":"password","append-inner-icon":t(z)?"tabler-eye-off":"tabler-eye","onClick:appendInner":p[12]||(p[12]=y=>z.value=!t(z))},null,8,["modelValue","type","append-inner-icon"])]),_:1})]),_:1})]),_:1})]),_:1}),e(Z,{value:"social-links"},{default:l(()=>[e(k,{class:"mt-2"},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(P),"onUpdate:modelValue":p[13]||(p[13]=y=>i(P)?P.value=y:null),label:"Twitter",placeholder:"https://twitter.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(H),"onUpdate:modelValue":p[14]||(p[14]=y=>i(H)?H.value=y:null),label:"Facebook",placeholder:"https://facebook.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(J),"onUpdate:modelValue":p[15]||(p[15]=y=>i(J)?J.value=y:null),label:"Google+",placeholder:"https://plus.google.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t($),"onUpdate:modelValue":p[16]||(p[16]=y=>i($)?$.value=y:null),label:"LinkedIn",placeholder:"https://linkedin.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(W),"onUpdate:modelValue":p[17]||(p[17]=y=>i(W)?W.value=y:null),label:"Instagram",placeholder:"https://instagram.com/username"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(F,{modelValue:t(Y),"onUpdate:modelValue":p[18]||(p[18]=y=>i(Y)?Y.value=y:null),label:"Quora",placeholder:"https://quora.com/username"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1})]),_:1})]),_:1},8,["modelValue"])]),_:1}),e(U),e(ee,{class:"d-flex gap-4"},{default:l(()=>[e(_,null,{default:l(()=>p[23]||(p[23]=[w("Submit")])),_:1,__:[23]}),e(_,{color:"secondary",variant:"tonal"},{default:l(()=>p[24]||(p[24]=[w(" Cancel ")])),_:1,__:[24]})]),_:1})]),_:1})],64)}}}),Pe=T({__name:"DemoFormLayoutFormValidation",setup(R){const C=m(""),f=m(""),r=["Item 1","Item 2","Item 3","Item 4"],V=m(),d=m(!1),v=m();return(a,s)=>{const n=L,u=j;return g(),A(k,{ref_key:"form",ref:v,"lazy-validation":""},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(n,{modelValue:t(C),"onUpdate:modelValue":s[0]||(s[0]=b=>i(C)?C.value=b:null),rules:["requiredValidator"in a?a.requiredValidator:t(E)],label:"Name",placeholder:"John Doe",required:""},null,8,["modelValue","rules"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(n,{modelValue:t(f),"onUpdate:modelValue":s[1]||(s[1]=b=>i(f)?f.value=b:null),rules:["emailValidator"in a?a.emailValidator:t(he),"requiredValidator"in a?a.requiredValidator:t(E)],label:"E-mail",placeholder:"johndoe@email.com",required:""},null,8,["modelValue","rules"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(u,{modelValue:t(V),"onUpdate:modelValue":s[2]||(s[2]=b=>i(V)?V.value=b:null),items:r,rules:["requiredValidator"in a?a.requiredValidator:t(E)],placeholder:"Select an Item",label:"Item",name:"select",require:""},null,8,["modelValue","rules"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(I,{modelValue:t(d),"onUpdate:modelValue":s[3]||(s[3]=b=>i(d)?d.value=b:null),rules:["requiredValidator"in a?a.requiredValidator:t(E)],label:"Do you agree?",required:""},null,8,["modelValue","rules"])]),_:1}),e(o,{cols:"12",class:"d-flex flex-wrap gap-4"},{default:l(()=>[e(_,{color:"success",onClick:s[4]||(s[4]=b=>{var h;return(h=t(v))==null?void 0:h.validate()})},{default:l(()=>s[7]||(s[7]=[w(" Validate ")])),_:1,__:[7]}),e(_,{color:"error",onClick:s[5]||(s[5]=b=>{var h;return(h=t(v))==null?void 0:h.reset()})},{default:l(()=>s[8]||(s[8]=[w(" Reset Form ")])),_:1,__:[8]}),e(_,{color:"warning",onClick:s[6]||(s[6]=b=>{var h;return(h=t(v))==null?void 0:h.resetValidation()})},{default:l(()=>s[9]||(s[9]=[w(" Reset Validation ")])),_:1,__:[9]})]),_:1})]),_:1})]),_:1},512)}}}),De=T({__name:"DemoFormLayoutFormHint",setup(R){const C=m(""),f=m(""),r=m(),V=m(!1),d=["foo","bar","fizz","buzz"],v=m([]);return(a,s)=>{const n=L,u=_e;return g(),A(k,{onSubmit:S(()=>{},["prevent"])},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(n,{modelValue:t(C),"onUpdate:modelValue":s[0]||(s[0]=b=>i(C)?C.value=b:null),label:"Username",placeholder:"Johndoe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(n,{modelValue:t(f),"onUpdate:modelValue":s[1]||(s[1]=b=>i(f)?f.value=b:null),label:"Email",type:"email",placeholder:"johndoe@email.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(n,{modelValue:t(r),"onUpdate:modelValue":s[2]||(s[2]=b=>i(r)?r.value=b:null),label:"Password",autocomplete:"on",type:"password","persistent-hint":"",placeholder:"············",hint:"Your password must be 8-20 characters long."},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(u,{modelValue:t(v),"onUpdate:modelValue":s[3]||(s[3]=b=>i(v)?v.value=b:null),items:d,chips:"",multiple:"",label:"Autocomplete",placeholder:"Select"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(I,{modelValue:t(V),"onUpdate:modelValue":s[4]||(s[4]=b=>i(V)?V.value=b:null),label:"Remember me"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",class:"d-flex gap-4"},{default:l(()=>[e(_,{type:"submit"},{default:l(()=>s[5]||(s[5]=[w(" Submit ")])),_:1,__:[5]}),e(_,{color:"secondary",type:"reset",variant:"tonal"},{default:l(()=>s[6]||(s[6]=[w(" Reset ")])),_:1,__:[6]})]),_:1})]),_:1})]),_:1})}}}),Be=T({__name:"DemoFormLayoutMultipleColumn",setup(R){const C=m(""),f=m(""),r=m(""),V=m(""),d=m(""),v=m(""),a=m(!1);return(s,n)=>{const u=L;return g(),A(k,{onSubmit:S(()=>{},["prevent"])},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(u,{modelValue:t(C),"onUpdate:modelValue":n[0]||(n[0]=b=>i(C)?C.value=b:null),label:"First Name",placeholder:"John"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(u,{modelValue:t(f),"onUpdate:modelValue":n[1]||(n[1]=b=>i(f)?f.value=b:null),label:"Last Name",placeholder:"Doe"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(u,{modelValue:t(v),"onUpdate:modelValue":n[2]||(n[2]=b=>i(v)?v.value=b:null),label:"Email",placeholder:"johndoe@email.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(u,{modelValue:t(r),"onUpdate:modelValue":n[3]||(n[3]=b=>i(r)?r.value=b:null),label:"City",placeholder:"New York"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(u,{modelValue:t(V),"onUpdate:modelValue":n[4]||(n[4]=b=>i(V)?V.value=b:null),label:"Country",placeholder:"United States"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(u,{modelValue:t(d),"onUpdate:modelValue":n[5]||(n[5]=b=>i(d)?d.value=b:null),label:"Company",placeholder:"Pixinvent"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(I,{modelValue:t(a),"onUpdate:modelValue":n[6]||(n[6]=b=>i(a)?a.value=b:null),label:"Remember me"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",class:"d-flex gap-4"},{default:l(()=>[e(_,{type:"submit"},{default:l(()=>n[7]||(n[7]=[w(" Submit ")])),_:1,__:[7]}),e(_,{type:"reset",color:"secondary",variant:"tonal"},{default:l(()=>n[8]||(n[8]=[w(" Reset ")])),_:1,__:[8]})]),_:1})]),_:1})]),_:1})}}}),ze=T({__name:"DemoFormLayoutVerticalFormWithIcons",setup(R){const C=m(""),f=m(""),r=m(),V=m(),d=m(!1);return(v,a)=>{const s=L;return g(),A(k,{onSubmit:S(n=>({}),["prevent"])},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(s,{modelValue:t(C),"onUpdate:modelValue":a[0]||(a[0]=n=>i(C)?C.value=n:null),"prepend-inner-icon":"tabler-user",label:"First Name",placeholder:"John"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(s,{modelValue:t(f),"onUpdate:modelValue":a[1]||(a[1]=n=>i(f)?f.value=n:null),"prepend-inner-icon":"tabler-mail",label:"Email",type:"email",placeholder:"johndoe@example.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(s,{modelValue:t(r),"onUpdate:modelValue":a[2]||(a[2]=n=>i(r)?r.value=n:null),"prepend-inner-icon":"tabler-device-mobile",label:"Mobile",placeholder:"+966 50 123 4567",type:"number"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(s,{modelValue:t(V),"onUpdate:modelValue":a[3]||(a[3]=n=>i(V)?V.value=n:null),"prepend-inner-icon":"tabler-lock",label:"Password",autocomplete:"on",type:"password",placeholder:"············"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(I,{modelValue:t(d),"onUpdate:modelValue":a[4]||(a[4]=n=>i(d)?d.value=n:null),label:"Remember me"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(_,{type:"submit",class:"me-2"},{default:l(()=>a[5]||(a[5]=[w(" Submit ")])),_:1,__:[5]}),e(_,{color:"secondary",type:"reset",variant:"tonal"},{default:l(()=>a[6]||(a[6]=[w(" Reset ")])),_:1,__:[6]})]),_:1})]),_:1})]),_:1})}}}),Ee=T({__name:"DemoFormLayoutVerticalForm",setup(R){const C=m(""),f=m(""),r=m(),V=m(),d=m(!1);return(v,a)=>{const s=L;return g(),A(k,{onSubmit:S(()=>{},["prevent"])},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(s,{modelValue:t(C),"onUpdate:modelValue":a[0]||(a[0]=n=>i(C)?C.value=n:null),label:"First Name",placeholder:"John"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(s,{modelValue:t(f),"onUpdate:modelValue":a[1]||(a[1]=n=>i(f)?f.value=n:null),label:"Email",type:"email",placeholder:"johndoe@example.com"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(s,{modelValue:t(r),"onUpdate:modelValue":a[2]||(a[2]=n=>i(r)?r.value=n:null),label:"Mobile",placeholder:"+966 50 123 4567",type:"number"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(s,{modelValue:t(V),"onUpdate:modelValue":a[3]||(a[3]=n=>i(V)?V.value=n:null),label:"Password",autocomplete:"on",type:"password",placeholder:"············"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(I,{modelValue:t(d),"onUpdate:modelValue":a[4]||(a[4]=n=>i(d)?d.value=n:null),label:"Remember me"},null,8,["modelValue"])]),_:1}),e(o,{cols:"12",class:"d-flex gap-4"},{default:l(()=>[e(_,{type:"submit"},{default:l(()=>a[5]||(a[5]=[w(" Submit ")])),_:1,__:[5]}),e(_,{type:"reset",color:"secondary",variant:"tonal"},{default:l(()=>a[6]||(a[6]=[w(" Reset ")])),_:1,__:[6]})]),_:1})]),_:1})]),_:1})}}}),Me=T({__name:"DemoFormLayoutHorizontalFormWithIcons",setup(R){const C=m(""),f=m(""),r=m(),V=m(),d=m(!1);return(v,a)=>{const s=L;return g(),A(k,{onSubmit:S(()=>{},["prevent"])},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3",class:"d-flex align-items-center"},{default:l(()=>a[5]||(a[5]=[c("label",{class:"v-label text-body-2 text-high-emphasis",for:"firstNameHorizontalIcons"},"First Name",-1)])),_:1,__:[5]}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(s,{id:"firstNameHorizontalIcons",modelValue:t(C),"onUpdate:modelValue":a[0]||(a[0]=n=>i(C)?C.value=n:null),"prepend-inner-icon":"tabler-user",placeholder:"John","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3",class:"d-flex align-items-center"},{default:l(()=>a[6]||(a[6]=[c("label",{class:"v-label text-body-2 text-high-emphasis",for:"emailHorizontalIcons"},"Email",-1)])),_:1,__:[6]}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(s,{id:"emailHorizontalIcons",modelValue:t(f),"onUpdate:modelValue":a[1]||(a[1]=n=>i(f)?f.value=n:null),"prepend-inner-icon":"tabler-mail",placeholder:"johndoe@email.com","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3",class:"d-flex align-items-center"},{default:l(()=>a[7]||(a[7]=[c("label",{class:"v-label text-body-2 text-high-emphasis",for:"mobileHorizontalIcons"},"Mobile",-1)])),_:1,__:[7]}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(s,{id:"mobileHorizontalIcons",modelValue:t(r),"onUpdate:modelValue":a[2]||(a[2]=n=>i(r)?r.value=n:null),type:"number","prepend-inner-icon":"tabler-device-mobile",placeholder:"+966 50 123 4567","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3",class:"d-flex align-items-center"},{default:l(()=>a[8]||(a[8]=[c("label",{class:"v-label text-body-2 text-high-emphasis",for:"passwordHorizontalIcons"},"Password",-1)])),_:1,__:[8]}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(s,{id:"passwordHorizontalIcons",modelValue:t(V),"onUpdate:modelValue":a[3]||(a[3]=n=>i(V)?V.value=n:null),"prepend-inner-icon":"tabler-lock",autocomplete:"on",type:"password",placeholder:"············","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(I,{modelValue:t(d),"onUpdate:modelValue":a[4]||(a[4]=n=>i(d)?d.value=n:null),label:"Remember me"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(_,{type:"submit",class:"me-4"},{default:l(()=>a[9]||(a[9]=[w(" Submit ")])),_:1,__:[9]}),e(_,{color:"secondary",variant:"tonal",type:"reset"},{default:l(()=>a[10]||(a[10]=[w(" Reset ")])),_:1,__:[10]})]),_:1})]),_:1})]),_:1})]),_:1})]),_:1})}}}),je=T({__name:"DemoFormLayoutHorizontalForm",setup(R){const C=m(""),f=m(""),r=m(),V=m(),d=m(!1);return(v,a)=>{const s=L;return g(),A(k,{onSubmit:S(()=>{},["prevent"])},{default:l(()=>[e(x,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3",class:"d-flex align-items-center"},{default:l(()=>a[5]||(a[5]=[c("label",{class:"v-label text-body-2 text-high-emphasis",for:"firstName"},"First Name",-1)])),_:1,__:[5]}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(s,{id:"firstName",modelValue:t(C),"onUpdate:modelValue":a[0]||(a[0]=n=>i(C)?C.value=n:null),placeholder:"John","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3",class:"d-flex align-items-center"},{default:l(()=>a[6]||(a[6]=[c("label",{class:"v-label text-body-2 text-high-emphasis",for:"email"},"Email",-1)])),_:1,__:[6]}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(s,{id:"email",modelValue:t(f),"onUpdate:modelValue":a[1]||(a[1]=n=>i(f)?f.value=n:null),placeholder:"johndoe@email.com","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3",class:"d-flex align-items-center"},{default:l(()=>a[7]||(a[7]=[c("label",{class:"v-label text-body-2 text-high-emphasis",for:"mobile"},"Mobile",-1)])),_:1,__:[7]}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(s,{id:"mobile",modelValue:t(r),"onUpdate:modelValue":a[2]||(a[2]=n=>i(r)?r.value=n:null),type:"number",placeholder:"+966 50 123 4567","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3",class:"d-flex align-items-center"},{default:l(()=>a[8]||(a[8]=[c("label",{class:"v-label text-body-2 text-high-emphasis",for:"password"},"Password",-1)])),_:1,__:[8]}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(s,{id:"password",modelValue:t(V),"onUpdate:modelValue":a[3]||(a[3]=n=>i(V)?V.value=n:null),autocomplete:"on",type:"password",placeholder:"············","persistent-placeholder":""},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(I,{modelValue:t(d),"onUpdate:modelValue":a[4]||(a[4]=n=>i(d)?d.value=n:null),label:"Remember me"},null,8,["modelValue"])]),_:1})]),_:1})]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(x,{"no-gutters":""},{default:l(()=>[e(o,{cols:"12",md:"3"}),e(o,{cols:"12",md:"9"},{default:l(()=>[e(_,{type:"submit",class:"me-4"},{default:l(()=>a[9]||(a[9]=[w(" Submit ")])),_:1,__:[9]}),e(_,{color:"secondary",variant:"tonal",type:"reset"},{default:l(()=>a[10]||(a[10]=[w(" Reset ")])),_:1,__:[10]})]),_:1})]),_:1})]),_:1})]),_:1})]),_:1})}}}),qe={ts:`<script lang="ts" setup>
const username = ref('')
const email = ref('')
const password = ref<string>()
const checkbox = ref(false)
const items = ['foo', 'bar', 'fizz', 'buzz'] as const
const values = ref<typeof items[number][]>([])
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <!-- 👉 Username -->
        <AppTextField
          v-model="username"
          label="Username"
          placeholder="Johndoe"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Email -->
        <AppTextField
          v-model="email"
          label="Email"
          type="email"
          placeholder="johndoe@email.com"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Password -->
        <AppTextField
          v-model="password"
          label="Password"
          autocomplete="on"
          type="password"
          persistent-hint
          placeholder="············"
          hint="Your password must be 8-20 characters long."
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Autocomplete -->
        <AppAutocomplete
          v-model="values"
          :items="items"
          chips
          multiple
          label="Autocomplete"
          placeholder="Select"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Checkbox -->
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <!-- 👉 submit and reset button -->
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const username = ref('')
const email = ref('')
const password = ref()
const checkbox = ref(false)

const items = [
  'foo',
  'bar',
  'fizz',
  'buzz',
]

const values = ref([])
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <!-- 👉 Username -->
        <AppTextField
          v-model="username"
          label="Username"
          placeholder="Johndoe"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Email -->
        <AppTextField
          v-model="email"
          label="Email"
          type="email"
          placeholder="johndoe@email.com"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Password -->
        <AppTextField
          v-model="password"
          label="Password"
          autocomplete="on"
          type="password"
          persistent-hint
          placeholder="············"
          hint="Your password must be 8-20 characters long."
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Autocomplete -->
        <AppAutocomplete
          v-model="values"
          :items="items"
          chips
          multiple
          label="Autocomplete"
          placeholder="Select"
        />
      </VCol>

      <VCol cols="12">
        <!-- 👉 Checkbox -->
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <!-- 👉 submit and reset button -->
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},He={ts:`<script lang="ts" setup>
import type { VForm } from 'vuetify/components/VForm'

const name = ref('')
const email = ref('')
const items = ['Item 1', 'Item 2', 'Item 3', 'Item 4'] as const
const select = ref<typeof items[number]>()
const checkbox = ref(false)
const form = ref<VForm>()
<\/script>

<template>
  <VForm
    ref="form"
    lazy-validation
  >
    <VRow>
      <VCol cols="12">
        <AppTextField
          v-model="name"
          :rules="[requiredValidator]"
          label="Name"
          placeholder="John Doe"
          required
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="email"
          :rules="[emailValidator, requiredValidator]"
          label="E-mail"
          placeholder="johndoe@email.com"
          required
        />
      </VCol>

      <VCol cols="12">
        <AppSelect
          v-model="select"
          :items="items"
          :rules="[requiredValidator]"
          placeholder="Select an Item"
          label="Item"
          name="select"
          require
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          :rules="[requiredValidator]"
          label="Do you agree?"
          required
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex flex-wrap gap-4"
      >
        <VBtn
          color="success"
          @click="form?.validate()"
        >
          Validate
        </VBtn>

        <VBtn
          color="error"
          @click="form?.reset()"
        >
          Reset Form
        </VBtn>

        <VBtn
          color="warning"
          @click="form?.resetValidation()"
        >
          Reset Validation
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const name = ref('')
const email = ref('')

const items = [
  'Item 1',
  'Item 2',
  'Item 3',
  'Item 4',
]

const select = ref()
const checkbox = ref(false)
const form = ref()
<\/script>

<template>
  <VForm
    ref="form"
    lazy-validation
  >
    <VRow>
      <VCol cols="12">
        <AppTextField
          v-model="name"
          :rules="[requiredValidator]"
          label="Name"
          placeholder="John Doe"
          required
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="email"
          :rules="[emailValidator, requiredValidator]"
          label="E-mail"
          placeholder="johndoe@email.com"
          required
        />
      </VCol>

      <VCol cols="12">
        <AppSelect
          v-model="select"
          :items="items"
          :rules="[requiredValidator]"
          placeholder="Select an Item"
          label="Item"
          name="select"
          require
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          :rules="[requiredValidator]"
          label="Do you agree?"
          required
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex flex-wrap gap-4"
      >
        <VBtn
          color="success"
          @click="form?.validate()"
        >
          Validate
        </VBtn>

        <VBtn
          color="error"
          @click="form?.reset()"
        >
          Reset Form
        </VBtn>

        <VBtn
          color="warning"
          @click="form?.resetValidation()"
        >
          Reset Validation
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},Je={ts:`<script lang="ts" setup>
const tab = ref('personal-info')
const firstName = ref('')
const lastName = ref('')
const country = ref()
const birthDate = ref('')
const phoneNo = ref<number>()
const countryList = ['USA', 'Canada', 'UK', 'Denmark', 'Germany', 'Iceland', 'Israel', 'Mexico']
const languageList = ['English', 'German', 'French', 'Spanish', 'Portuguese', 'Russian', 'Korean'] as const
const username = ref('')
const email = ref('')
const password = ref('')
const cPassword = ref('')
const twitterLink = ref('')
const facebookLink = ref('')
const googlePlusLink = ref('')
const linkedInLink = ref('')
const instagramLink = ref('')
const quoraLink = ref('')
const languages = ref<typeof languageList[number][]>([])
const isPasswordVisible = ref(false)
const isCPasswordVisible = ref(false)
<\/script>

<template>
  <VTabs v-model="tab">
    <VTab value="personal-info">
      Personal Info
    </VTab>
    <VTab value="account-details">
      Account Details
    </VTab>
    <VTab value="social-links">
      Social Links
    </VTab>
  </VTabs>

  <VCard flat>
    <VCardText>
      <VWindow
        v-model="tab"
        class="disable-tab-transition"
      >
        <VWindowItem value="personal-info">
          <VForm class="mt-2">
            <VRow>
              <VCol
                md="6"
                cols="12"
              >
                <AppTextField
                  v-model="firstName"
                  label="First name"
                  placeholder="John"
                />
              </VCol>

              <VCol
                md="6"
                cols="12"
              >
                <AppTextField
                  v-model="lastName"
                  label="Last name"
                  placeholder="Doe"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppSelect
                  v-model="country"
                  :items="countryList"
                  label="Country"
                  placeholder="Select Country"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppSelect
                  v-model="languages"
                  :items="languageList"
                  multiple
                  chips
                  clearable
                  label="Language"
                  placeholder="Select Language"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppDateTimePicker
                  v-model="birthDate"
                  label="Birth Date"
                  placeholder="Select Birth Date"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="phoneNo"
                  type="number"
                  label="Phone No."
                  placeholder="+1 123 456 7890"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>

        <VWindowItem value="account-details">
          <VForm class="mt-2">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="username"
                  label="Username"
                  placeholder="Johndoe"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="email"
                  label="Email"
                  suffix="@example.com"
                  placeholder="johndoe@email.com"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="cPassword"
                  label="Confirm Password"
                  autocomplete="confirm-password"
                  placeholder="············"
                  :type="isCPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isCPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isCPasswordVisible = !isCPasswordVisible"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>

        <VWindowItem value="social-links">
          <VForm class="mt-2">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="twitterLink"
                  label="Twitter"
                  placeholder="https://twitter.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="facebookLink"
                  label="Facebook"
                  placeholder="https://facebook.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="googlePlusLink"
                  label="Google+"
                  placeholder="https://plus.google.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="linkedInLink"
                  label="LinkedIn"
                  placeholder="https://linkedin.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="instagramLink"
                  label="Instagram"
                  placeholder="https://instagram.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="quoraLink"
                  label="Quora"
                  placeholder="https://quora.com/username"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>
      </VWindow>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex gap-4">
      <VBtn>Submit</VBtn>
      <VBtn
        color="secondary"
        variant="tonal"
      >
        Cancel
      </VBtn>
    </VCardText>
  </VCard>
</template>
`,js:`<script setup>
const tab = ref('personal-info')
const firstName = ref('')
const lastName = ref('')
const country = ref()
const birthDate = ref('')
const phoneNo = ref()

const countryList = [
  'USA',
  'Canada',
  'UK',
  'Denmark',
  'Germany',
  'Iceland',
  'Israel',
  'Mexico',
]

const languageList = [
  'English',
  'German',
  'French',
  'Spanish',
  'Portuguese',
  'Russian',
  'Korean',
]

const username = ref('')
const email = ref('')
const password = ref('')
const cPassword = ref('')
const twitterLink = ref('')
const facebookLink = ref('')
const googlePlusLink = ref('')
const linkedInLink = ref('')
const instagramLink = ref('')
const quoraLink = ref('')
const languages = ref([])
const isPasswordVisible = ref(false)
const isCPasswordVisible = ref(false)
<\/script>

<template>
  <VTabs v-model="tab">
    <VTab value="personal-info">
      Personal Info
    </VTab>
    <VTab value="account-details">
      Account Details
    </VTab>
    <VTab value="social-links">
      Social Links
    </VTab>
  </VTabs>

  <VCard flat>
    <VCardText>
      <VWindow
        v-model="tab"
        class="disable-tab-transition"
      >
        <VWindowItem value="personal-info">
          <VForm class="mt-2">
            <VRow>
              <VCol
                md="6"
                cols="12"
              >
                <AppTextField
                  v-model="firstName"
                  label="First name"
                  placeholder="John"
                />
              </VCol>

              <VCol
                md="6"
                cols="12"
              >
                <AppTextField
                  v-model="lastName"
                  label="Last name"
                  placeholder="Doe"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppSelect
                  v-model="country"
                  :items="countryList"
                  label="Country"
                  placeholder="Select Country"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppSelect
                  v-model="languages"
                  :items="languageList"
                  multiple
                  chips
                  clearable
                  label="Language"
                  placeholder="Select Language"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppDateTimePicker
                  v-model="birthDate"
                  label="Birth Date"
                  placeholder="Select Birth Date"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="phoneNo"
                  type="number"
                  label="Phone No."
                  placeholder="+1 123 456 7890"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>

        <VWindowItem value="account-details">
          <VForm class="mt-2">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="username"
                  label="Username"
                  placeholder="Johndoe"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="email"
                  label="Email"
                  suffix="@example.com"
                  placeholder="johndoe@email.com"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="cPassword"
                  label="Confirm Password"
                  autocomplete="confirm-password"
                  placeholder="············"
                  :type="isCPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isCPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isCPasswordVisible = !isCPasswordVisible"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>

        <VWindowItem value="social-links">
          <VForm class="mt-2">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="twitterLink"
                  label="Twitter"
                  placeholder="https://twitter.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="facebookLink"
                  label="Facebook"
                  placeholder="https://facebook.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="googlePlusLink"
                  label="Google+"
                  placeholder="https://plus.google.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="linkedInLink"
                  label="LinkedIn"
                  placeholder="https://linkedin.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="instagramLink"
                  label="Instagram"
                  placeholder="https://instagram.com/username"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="quoraLink"
                  label="Quora"
                  placeholder="https://quora.com/username"
                />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>
      </VWindow>
    </VCardText>

    <VDivider />

    <VCardText class="d-flex gap-4">
      <VBtn>Submit</VBtn>
      <VBtn
        color="secondary"
        variant="tonal"
      >
        Cancel
      </VBtn>
    </VCardText>
  </VCard>
</template>
`},$e={ts:`<script lang="ts" setup>
const firstName = ref('')
const email = ref('')
const mobile = ref<number>()
const password = ref<string>()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 First Name -->
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="firstName"
            >First Name</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="firstName"
              v-model="firstName"
              placeholder="John"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Email -->
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="email"
            >Email</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="email"
              v-model="email"
              placeholder="johndoe@email.com"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Mobile -->
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="mobile"
            >Mobile</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="mobile"
              v-model="mobile"
              type="number"
              placeholder="+1 123 456 7890"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Password -->
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="password"
            >Password</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="password"
              v-model="password"
              autocomplete="on"
              type="password"
              placeholder="············"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Remember me -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VCheckbox
              v-model="checkbox"
              label="Remember me"
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 submit and reset button -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VBtn
              type="submit"
              class="me-4"
            >
              Submit
            </VBtn>
            <VBtn
              color="secondary"
              variant="tonal"
              type="reset"
            >
              Reset
            </VBtn>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const email = ref('')
const mobile = ref()
const password = ref()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 First Name -->
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="firstName"
            >First Name</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="firstName"
              v-model="firstName"
              placeholder="John"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Email -->
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="email"
            >Email</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="email"
              v-model="email"
              placeholder="johndoe@email.com"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Mobile -->
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="mobile"
            >Mobile</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="mobile"
              v-model="mobile"
              type="number"
              placeholder="+1 123 456 7890"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <VCol cols="12">
        <VRow no-gutters>
          <!-- 👉 Password -->
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="password"
            >Password</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="password"
              v-model="password"
              autocomplete="on"
              type="password"
              placeholder="············"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Remember me -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VCheckbox
              v-model="checkbox"
              label="Remember me"
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 submit and reset button -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VBtn
              type="submit"
              class="me-4"
            >
              Submit
            </VBtn>
            <VBtn
              color="secondary"
              variant="tonal"
              type="reset"
            >
              Reset
            </VBtn>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </VForm>
</template>
`},We={ts:`<script lang="ts" setup>
const firstName = ref('')
const email = ref('')
const mobile = ref<number>()
const password = ref<string>()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <!-- 👉 First Name -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="firstNameHorizontalIcons"
            >First Name</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="firstNameHorizontalIcons"
              v-model="firstName"
              prepend-inner-icon="tabler-user"
              placeholder="John"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Email -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="emailHorizontalIcons"
            >Email</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="emailHorizontalIcons"
              v-model="email"
              prepend-inner-icon="tabler-mail"
              placeholder="johndoe@email.com"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Mobile -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="mobileHorizontalIcons"
            >Mobile</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="mobileHorizontalIcons"
              v-model="mobile"
              type="number"
              prepend-inner-icon="tabler-device-mobile"
              placeholder="+1 123 456 7890"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Password -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="passwordHorizontalIcons"
            >Password</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="passwordHorizontalIcons"
              v-model="password"
              prepend-inner-icon="tabler-lock"
              autocomplete="on"
              type="password"
              placeholder="············"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Checkbox -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VCheckbox
              v-model="checkbox"
              label="Remember me"
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 submit and reset button -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VBtn
              type="submit"
              class="me-4"
            >
              Submit
            </VBtn>
            <VBtn
              color="secondary"
              variant="tonal"
              type="reset"
            >
              Reset
            </VBtn>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const email = ref('')
const mobile = ref()
const password = ref()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <!-- 👉 First Name -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="firstNameHorizontalIcons"
            >First Name</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="firstNameHorizontalIcons"
              v-model="firstName"
              prepend-inner-icon="tabler-user"
              placeholder="John"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Email -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="emailHorizontalIcons"
            >Email</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="emailHorizontalIcons"
              v-model="email"
              prepend-inner-icon="tabler-mail"
              placeholder="johndoe@email.com"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Mobile -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="mobileHorizontalIcons"
            >Mobile</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="mobileHorizontalIcons"
              v-model="mobile"
              type="number"
              prepend-inner-icon="tabler-device-mobile"
              placeholder="+1 123 456 7890"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Password -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
            class="d-flex align-items-center"
          >
            <label
              class="v-label text-body-2 text-high-emphasis"
              for="passwordHorizontalIcons"
            >Password</label>
          </VCol>

          <VCol
            cols="12"
            md="9"
          >
            <AppTextField
              id="passwordHorizontalIcons"
              v-model="password"
              prepend-inner-icon="tabler-lock"
              autocomplete="on"
              type="password"
              placeholder="············"
              persistent-placeholder
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 Checkbox -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VCheckbox
              v-model="checkbox"
              label="Remember me"
            />
          </VCol>
        </VRow>
      </VCol>

      <!-- 👉 submit and reset button -->
      <VCol cols="12">
        <VRow no-gutters>
          <VCol
            cols="12"
            md="3"
          />
          <VCol
            cols="12"
            md="9"
          >
            <VBtn
              type="submit"
              class="me-4"
            >
              Submit
            </VBtn>
            <VBtn
              color="secondary"
              variant="tonal"
              type="reset"
            >
              Reset
            </VBtn>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </VForm>
</template>
`},Ye={ts:`<script lang="ts" setup>
const firstName = ref('')
const lastName = ref('')
const city = ref('')
const country = ref('')
const company = ref('')
const email = ref('')
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <!-- 👉 First Name -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <!-- 👉 Last Name -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="lastName"
          label="Last Name"
          placeholder="Doe"
        />
      </VCol>

      <!-- 👉 Email -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          label="Email"
          placeholder="johndoe@email.com"
        />
      </VCol>

      <!-- 👉 City -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="city"
          label="City"
          placeholder="New York"
        />
      </VCol>

      <!-- 👉 Country -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="country"
          label="Country"
          placeholder="United States"
        />
      </VCol>

      <!-- 👉 Company -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="company"
          label="Company"
          placeholder="Pixinvent"
        />
      </VCol>

      <!-- 👉 Remember me -->
      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const lastName = ref('')
const city = ref('')
const country = ref('')
const company = ref('')
const email = ref('')
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <!-- 👉 First Name -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <!-- 👉 Last Name -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="lastName"
          label="Last Name"
          placeholder="Doe"
        />
      </VCol>

      <!-- 👉 Email -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="email"
          label="Email"
          placeholder="johndoe@email.com"
        />
      </VCol>

      <!-- 👉 City -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="city"
          label="City"
          placeholder="New York"
        />
      </VCol>

      <!-- 👉 Country -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="country"
          label="Country"
          placeholder="United States"
        />
      </VCol>

      <!-- 👉 Company -->
      <VCol
        cols="12"
        md="6"
      >
        <AppTextField
          v-model="company"
          label="Company"
          placeholder="Pixinvent"
        />
      </VCol>

      <!-- 👉 Remember me -->
      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},Ge={ts:`<script lang="ts" setup>
const firstName = ref('')
const email = ref('')
const mobile = ref<number>()
const password = ref<string>()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <AppTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="email"
          label="Email"
          type="email"
          placeholder="johndoe@example.com"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="mobile"
          label="Mobile"
          placeholder="+1 123 456 7890"
          type="number"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="password"
          label="Password"
          autocomplete="on"
          type="password"
          placeholder="············"
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const email = ref('')
const mobile = ref()
const password = ref()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="() => {}">
    <VRow>
      <VCol cols="12">
        <AppTextField
          v-model="firstName"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="email"
          label="Email"
          type="email"
          placeholder="johndoe@example.com"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="mobile"
          label="Mobile"
          placeholder="+1 123 456 7890"
          type="number"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="password"
          label="Password"
          autocomplete="on"
          type="password"
          placeholder="············"
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit">
          Submit
        </VBtn>

        <VBtn
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},Oe={ts:`<script lang="ts" setup>
const firstName = ref('')
const email = ref('')
const mobile = ref<number>()
const password = ref<string>()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="{}">
    <VRow>
      <VCol cols="12">
        <AppTextField
          v-model="firstName"
          prepend-inner-icon="tabler-user"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="email"
          prepend-inner-icon="tabler-mail"
          label="Email"
          type="email"
          placeholder="johndoe@example.com"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="mobile"
          prepend-inner-icon="tabler-device-mobile"
          label="Mobile"
          placeholder="+1 123 456 7890"
          type="number"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="password"
          prepend-inner-icon="tabler-lock"
          label="Password"
          autocomplete="on"
          type="password"
          placeholder="············"
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          class="me-2"
        >
          Submit
        </VBtn>

        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`,js:`<script setup>
const firstName = ref('')
const email = ref('')
const mobile = ref()
const password = ref()
const checkbox = ref(false)
<\/script>

<template>
  <VForm @submit.prevent="{}">
    <VRow>
      <VCol cols="12">
        <AppTextField
          v-model="firstName"
          prepend-inner-icon="tabler-user"
          label="First Name"
          placeholder="John"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="email"
          prepend-inner-icon="tabler-mail"
          label="Email"
          type="email"
          placeholder="johndoe@example.com"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="mobile"
          prepend-inner-icon="tabler-device-mobile"
          label="Mobile"
          placeholder="+1 123 456 7890"
          type="number"
        />
      </VCol>

      <VCol cols="12">
        <AppTextField
          v-model="password"
          prepend-inner-icon="tabler-lock"
          label="Password"
          autocomplete="on"
          type="password"
          placeholder="············"
        />
      </VCol>

      <VCol cols="12">
        <VCheckbox
          v-model="checkbox"
          label="Remember me"
        />
      </VCol>

      <VCol cols="12">
        <VBtn
          type="submit"
          class="me-2"
        >
          Submit
        </VBtn>

        <VBtn
          color="secondary"
          type="reset"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>
`},Jl=T({__name:"form-layouts",setup(R){return(C,f)=>{const r=je,V=ge,d=Me,v=Ee,a=ze,s=Be,n=De,u=Pe,b=Se,h=Ue,P=Ne;return g(),q("div",null,[e(x,null,{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Horizontal Form",code:t($e)},{default:l(()=>[e(r)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Horizontal Form with Icons",code:t(We)},{default:l(()=>[e(d)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Vertical Form",code:t(Ge)},{default:l(()=>[e(v)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Vertical Form with Icons",code:t(Oe)},{default:l(()=>[e(a)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[e(V,{title:"Multiple Column",code:t(Ye)},{default:l(()=>[e(s)]),_:1},8,["code"])]),_:1})]),_:1}),e(x,{class:"match-height my-3"},{default:l(()=>[e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Form Hint",code:t(qe)},{default:l(()=>[e(n)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12",md:"6"},{default:l(()=>[e(V,{title:"Form Validation",code:t(He)},{default:l(()=>[e(u)]),_:1},8,["code"])]),_:1})]),_:1}),e(x,null,{default:l(()=>[e(o,{cols:"12"},{default:l(()=>[e(V,{title:"Form with Tabs","no-padding":"",code:t(Je)},{default:l(()=>[e(b)]),_:1},8,["code"])]),_:1}),e(o,{cols:"12"},{default:l(()=>[f[0]||(f[0]=c("h6",{class:"text-h6 mb-6"}," Collapsible Section ",-1)),e(h)]),_:1,__:[0]}),e(o,{cols:"12"},{default:l(()=>[f[1]||(f[1]=c("h6",{class:"text-h6 mb-6"}," Sticky Section ",-1)),e(P)]),_:1,__:[1]})]),_:1})])}}});export{Jl as default};
