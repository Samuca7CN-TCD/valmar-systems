import{d as i,T as f,o as w,c as y,w as e,f as o,a as c,b as t,u as a,s as x,n as v}from"./app-xjJ8na4r.js";import{a as h,_ as k}from"./DialogModal-DmTc97lN.js";import{_ as m}from"./DangerButton-BZt0IaZo.js";import{_ as D,a as C}from"./TextInput-Dj1M68fx.js";import{_ as b}from"./SecondaryButton-C2X9K_i9.js";import"./SectionTitle-BiSef3UA.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./Modal-GnKcZS1t.js";const g=c("div",{class:"max-w-xl text-sm text-gray-600"}," Depois que sua conta for excluída, todos os seus recursos e dados serão excluídos permanentemente. Antes de excluir sua conta, baixe quaisquer dados ou informações que você deseja reter. ",-1),V={class:"mt-5"},$={class:"mt-4"},K={__name:"DeleteUserForm",setup(U){const r=i(!1),n=i(null),s=f({password:""}),p=()=>{r.value=!0,setTimeout(()=>n.value.focus(),250)},u=()=>{s.delete(route("current-user.destroy"),{preserveScroll:!0,onSuccess:()=>l(),onError:()=>n.value.focus(),onFinish:()=>s.reset()})},l=()=>{r.value=!1,s.reset()};return(q,d)=>(w(),y(k,null,{title:e(()=>[o(" Deletar conta ")]),description:e(()=>[o(" Exclua sua conta permanentemente. ")]),content:e(()=>[g,c("div",V,[t(m,{onClick:p},{default:e(()=>[o(" Deletar conta ")]),_:1})]),t(h,{show:r.value,onClose:l},{title:e(()=>[o(" Deletar conta ")]),content:e(()=>[o(" Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account. "),c("div",$,[t(D,{ref_key:"passwordInput",ref:n,modelValue:a(s).password,"onUpdate:modelValue":d[0]||(d[0]=_=>a(s).password=_),type:"password",class:"mt-1 block w-3/4",placeholder:"Senha",autocomplete:"current-password",onKeyup:x(u,["enter"])},null,8,["modelValue"]),t(C,{message:a(s).errors.password,class:"mt-2"},null,8,["message"])])]),footer:e(()=>[t(b,{onClick:l},{default:e(()=>[o(" Cancel ")]),_:1}),t(m,{class:v(["ms-3",{"opacity-25":a(s).processing}]),disabled:a(s).processing,onClick:u},{default:e(()=>[o(" Deletar conta ")]),_:1},8,["class","disabled"])]),_:1},8,["show"])]),_:1}))}};export{K as default};