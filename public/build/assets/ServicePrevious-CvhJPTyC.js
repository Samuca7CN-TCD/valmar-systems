import{T as f,d as y,l as B,o as i,e as c,b as n,u as d,w as b,F as k,Z as D,a as e,t as p,p as N,v as $,h as M,g as F,f as O}from"./app-B9AaS3di.js";import{_ as P}from"./AppLayout-BpgBCMDE.js";import{_ as j}from"./CreateUpdatePayModal-dNGOSuzY.js";import{_ as A}from"./ExtraOptionsButton-CdyZ58LY.js";import{f as E,t as m,a as q}from"./general-CT66CFmJ.js";import{r as R}from"./EyeIcon-EVOI874d.js";import"./PrimaryButton-C6t27UzX.js";import"./SecondaryButton-DopaOyGA.js";import"./PrinterIcon-CisfNOAW.js";import"./BanknotesIcon-D31jRm2d.js";import"./Modal-tn0Up1zP.js";const U={class:"font-semibold text-xl text-gray-800 leading-tight"},Z={class:"pt-12 print:hidden print:pt-0"},z={class:"max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full"},G={class:"bg-white overflow-hidden shadow-xl sm:rounded-lg p-5"},H={class:"flex flex-row items-center space-x-3"},I={class:"py-12 print:py-6"},J={class:"max-w-7xl mx-auto print:max-w-full"},K={class:"px-0 print:px-0"},Q={class:"bg-white overflow-hidden shadow-xl sm:rounded-lg print:shadow-none"},W={class:"flex flex-col"},X={class:"overflow-x-auto sm:-mx-6 lg:-mx-8"},Y={class:"inline-block min-w-full py-2 sm:px-6 lg:px-8"},ee={class:"overflow-hidden"},te={class:"min-w-full text-left text-sm font-light"},se=e("thead",{class:"border-b font-medium dark:border-neutral-500"},[e("tr",null,[e("th",{scope:"col",class:"px-6 py-4 text-center"},"#"),e("th",{scope:"col",class:"px-6 py-4 text-center"},"Título"),e("th",{scope:"col",class:"px-6 py-4 text-center"},"Cliente"),e("th",{scope:"col",class:"px-6 py-4 text-center"},"Valor total"),e("th",{scope:"col",class:"px-6 py-4 text-center print:hidden"},"Ações")])],-1),oe={class:"border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600"},ae={class:"whitespace-nowrap py-4 text-center font-medium"},ne={class:"whitespace-normal px-6 py-4 text-center trim"},re=["title"],le={class:"whitespace-normal px-6 py-4 text-center"},ie={class:"whitespace-nowrap px-2 py-4 text-center"},ce=["title","onClick"],de={key:1,class:"transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600"},pe=e("td",{class:"whitespace-nowrap px-6 py-4 text-center",colspan:"9"},"Não há serviços cadastrados no momento!",-1),ue=[pe],Ce={__name:"ServicePrevious",props:{page:Object,page_options:{type:Array,default:null},services_list:Object},setup(r){const h=r,o=f({id:null,title:"Teste",client:"Samuel",total_value:5e3,partial_value:5e3,observations:"Testando 123...",deadline:E(new Date(Date.now()+15*24*60*60*1e3),"new_date"),records_list:{enable_records:!1,data:[]}}),_=y(""),x=B(()=>{const a=_.value.toLowerCase();return h.services_list.filter(s=>s.motive.toLowerCase().includes(a)||s.entity_name.toLowerCase().includes(a)||s.observations.toLowerCase().includes(a)||m(s.accounting.total_value).toString().toLowerCase().includes(a)||m(s.accounting.partial_value).toString().toLowerCase().includes(a)||s.deadline.includes(a))}),l=y({mode:"create",show:!1,title:"Ver Serviço",primary_button_txt:"Fechar"}),C=(a,s=null)=>{const t=h.services_list.find(u=>u.id===s);if(t){const{id:u,motive:L,entity_name:S,observations:V,records:w,accounting:g}=t;o.id=u,o.title=L,o.client=S,o.total_value=g.total_value,o.partial_value=g.partial_value,o.observations=V,o.records_list.enable_records=!!w.length,o.records_list.data=w.map(T=>f(T))}l.value.mode="see",l.value.show=!0},v=()=>{o.reset(),l.value.show=!1};return(a,s)=>(i(),c(k,null,[n(d(D),{title:r.page.name},null,8,["title"]),n(P,{page:r.page,page_options:r.page_options},{header:b(()=>[e("h2",U,p(r.page.name)+" Prévio ",1)]),default:b(()=>[e("div",Z,[e("div",z,[e("div",G,[e("div",H,[n(d(q),{class:"w-5 y-5"}),N(e("input",{type:"text",name:"search_term",id:"search_term",autocomplete:"on",class:"simple-input",autofocus:"true",placeholder:"Pesquisar termo...","onUpdate:modelValue":s[0]||(s[0]=t=>_.value=t)},null,512),[[$,_.value]])])])])]),e("div",I,[e("div",J,[e("div",K,[e("div",Q,[e("div",W,[e("div",X,[e("div",Y,[e("div",ee,[e("table",te,[se,e("tbody",null,[x.value.length?(i(!0),c(k,{key:0},M(x.value,t=>(i(),c("tr",oe,[e("td",ae,p(t.id),1),e("td",ne,[F(p(t.motive)+" ",1),t.observations?(i(),c("span",{key:0,title:t.observations,class:"inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help"},"OBS",8,re)):O("",!0)]),e("td",le,p(t.entity_name),1),e("td",ie,p(d(m)(t.accounting.total_value)),1),e("td",{class:"whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-green-700 active:text-green-900 select-none print:hidden",title:"VER: "+t.motive+"("+t.entity_name+")",onClick:u=>C("see",t.id)},[n(d(R),{class:"w-4 h-4 m-auto"})],8,ce)]))),256)):(i(),c("tr",de,ue))])])])])])])])])])]),n(A,{mode:["rollup","print_page"]}),n(j,{show:l.value.show,modal:l.value,service:d(o),onSubmit:v,onClose:v},null,8,["show","modal","service"])]),_:1},8,["page","page_options"])],64))}};export{Ce as default};