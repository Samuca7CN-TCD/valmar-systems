import{T as g,o as c,e as f,b as e,u as o,Z as _,w as l,a as r,f as d,h as w,j as h,n as v,i as y,F as V}from"./app-xjJ8na4r.js";import{A as k}from"./AuthenticationCard-DtF3xYPJ.js";import{A as x}from"./AuthenticationCardLogo-DQHwuK_9.js";import{_ as b}from"./Checkbox-iaDYC64Y.js";import{_ as u,a as n}from"./TextInput-Dj1M68fx.js";import{_ as i}from"./InputLabel-DAvwukBO.js";import{_ as A}from"./PrimaryButton-DYKnFRqk.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const C={class:"mt-4"},$={class:"mt-4"},q={class:"mt-4"},N={key:0,class:"mt-4"},U={class:"flex items-center"},F={class:"ms-2"},P=["href"],T=["href"],j={class:"flex items-center justify-end mt-4"},D={__name:"Register",setup(B){const s=g({name:"",email:"",password:"",password_confirmation:"",terms:!1}),p=()=>{s.post(route("register"),{onFinish:()=>s.reset("password","password_confirmation")})};return(m,a)=>(c(),f(V,null,[e(o(_),{title:"Register"}),e(k,null,{logo:l(()=>[e(x)]),default:l(()=>[r("form",{onSubmit:y(p,["prevent"])},[r("div",null,[e(i,{for:"name",value:"Nome"}),e(u,{id:"name",modelValue:o(s).name,"onUpdate:modelValue":a[0]||(a[0]=t=>o(s).name=t),type:"text",class:"mt-1 block w-full",required:"",autofocus:"",autocomplete:"name"},null,8,["modelValue"]),e(n,{class:"mt-2",message:o(s).errors.name},null,8,["message"])]),r("div",C,[e(i,{for:"email",value:"Email"}),e(u,{id:"email",modelValue:o(s).email,"onUpdate:modelValue":a[1]||(a[1]=t=>o(s).email=t),type:"email",class:"mt-1 block w-full",required:"",autocomplete:"username"},null,8,["modelValue"]),e(n,{class:"mt-2",message:o(s).errors.email},null,8,["message"])]),r("div",$,[e(i,{for:"password",value:"Senha"}),e(u,{id:"password",modelValue:o(s).password,"onUpdate:modelValue":a[2]||(a[2]=t=>o(s).password=t),type:"password",class:"mt-1 block w-full",required:"",autocomplete:"new-password"},null,8,["modelValue"]),e(n,{class:"mt-2",message:o(s).errors.password},null,8,["message"])]),r("div",q,[e(i,{for:"password_confirmation",value:"Confirme sua senha"}),e(u,{id:"password_confirmation",modelValue:o(s).password_confirmation,"onUpdate:modelValue":a[3]||(a[3]=t=>o(s).password_confirmation=t),type:"password",class:"mt-1 block w-full",required:"",autocomplete:"new-password"},null,8,["modelValue"]),e(n,{class:"mt-2",message:o(s).errors.password_confirmation},null,8,["message"])]),m.$page.props.jetstream.hasTermsAndPrivacyPolicyFeature?(c(),f("div",N,[e(i,{for:"terms"},{default:l(()=>[r("div",U,[e(b,{id:"terms",checked:o(s).terms,"onUpdate:checked":a[4]||(a[4]=t=>o(s).terms=t),name:"terms",required:""},null,8,["checked"]),r("div",F,[d(" I agree to the "),r("a",{target:"_blank",href:m.route("terms.show"),class:"underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"},"Terms of Service",8,P),d(" and "),r("a",{target:"_blank",href:m.route("policy.show"),class:"underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"},"Privacy Policy",8,T)])]),e(n,{class:"mt-2",message:o(s).errors.terms},null,8,["message"])]),_:1})])):w("",!0),r("div",j,[e(o(h),{href:m.route("login"),class:"underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"},{default:l(()=>[d(" Already registered? ")]),_:1},8,["href"]),e(A,{class:v(["ms-4",{"opacity-25":o(s).processing}]),disabled:o(s).processing},{default:l(()=>[d(" Register ")]),_:1},8,["class","disabled"])])],32)]),_:1})],64))}};export{D as default};