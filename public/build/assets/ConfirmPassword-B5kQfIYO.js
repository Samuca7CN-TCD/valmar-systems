import{T as m,d,o as c,e as u,b as e,u as a,Z as p,w as r,a as o,n as f,f as _,i as w,F as g}from"./app-xjJ8na4r.js";import{A as h}from"./AuthenticationCard-DtF3xYPJ.js";import{A as b}from"./AuthenticationCardLogo-DQHwuK_9.js";import{_ as x,a as v}from"./TextInput-Dj1M68fx.js";import{_ as y}from"./InputLabel-DAvwukBO.js";import{_ as C}from"./PrimaryButton-DYKnFRqk.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const V=o("div",{class:"mb-4 text-sm text-gray-600"}," This is a secure area of the application. Please confirm your password before continuing. ",-1),A={class:"flex justify-end mt-4"},j={__name:"ConfirmPassword",setup(k){const s=m({password:""}),t=d(null),n=()=>{s.post(route("password.confirm"),{onFinish:()=>{s.reset(),t.value.focus()}})};return($,i)=>(c(),u(g,null,[e(a(p),{title:"Secure Area"}),e(h,null,{logo:r(()=>[e(b)]),default:r(()=>[V,o("form",{onSubmit:w(n,["prevent"])},[o("div",null,[e(y,{for:"password",value:"Senha"}),e(x,{id:"password",ref_key:"passwordInput",ref:t,modelValue:a(s).password,"onUpdate:modelValue":i[0]||(i[0]=l=>a(s).password=l),type:"password",class:"mt-1 block w-full",required:"",autocomplete:"current-password",autofocus:""},null,8,["modelValue"]),e(v,{class:"mt-2",message:a(s).errors.password},null,8,["message"])]),o("div",A,[e(C,{class:f(["ms-4",{"opacity-25":a(s).processing}]),disabled:a(s).processing},{default:r(()=>[_(" Confirm ")]),_:1},8,["class","disabled"])])],32)]),_:1})],64))}};export{j as default};