import{o as l,e as n,a as e,c,w as d,r as o}from"./app-wIlip3pR.js";import{_ as m}from"./Modal-DEJQdgPt.js";function v(t,a){return l(),n("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor","aria-hidden":"true","data-slot":"icon"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"})])}function b(t,a){return l(),n("svg",{xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor","aria-hidden":"true","data-slot":"icon"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M6 18 18 6M6 6l12 12"})])}const h={class:"bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4"},w={class:"sm:flex sm:items-start"},u={class:"mt-3 text-center w-full sm:mt-0 sm:ml-4 sm:text-left"},p={class:"text-lg"},f={class:"mt-2"},x={class:"flex flex-row justify-end px-6 py-4 bg-gray-100 text-right space-x-5"},B={__name:"CreateUpdateModal",props:{show:{type:Boolean,default:!1},maxWidth:{type:String,default:"2xl"},closeable:{type:Boolean,default:!0}},emits:["close"],setup(t,{emit:a}){const r=a,i=()=>{r("close")};return(s,_)=>(l(),c(m,{show:t.show,"max-width":t.maxWidth,closeable:t.closeable,onClose:i},{default:d(()=>[e("div",h,[e("div",w,[o(s.$slots,"icon"),e("div",u,[e("h3",p,[o(s.$slots,"title")]),e("div",f,[o(s.$slots,"content")])])])]),e("div",x,[o(s.$slots,"footer")])]),_:3},8,["show","max-width","closeable"]))}};export{B as _,b as a,v as r};