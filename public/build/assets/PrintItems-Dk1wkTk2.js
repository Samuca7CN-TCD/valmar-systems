import{_ as q}from"./AppLayout-BOBs5ZEO.js";import{_ as I}from"./FloatButton-DOifkNz7.js";import{_ as $}from"./Checkbox-iaDYC64Y.js";import{d as _,l as N,o as n,e as o,b as c,u as A,Z as B,w as g,a as e,F as m,g as x,t as l,h as y,n as L}from"./app-xjJ8na4r.js";import{_ as V}from"./ExtraOptionsButton-irMfiu4z.js";import"./PlusIcon-Qtp_qUi0.js";import"./PrinterIcon-EuA9oIlG.js";const j=e("h2",{class:"font-semibold text-xl text-gray-800 leading-tight"}," Listagem de Items ",-1),D={class:"py-12 print:py-0"},F={class:"max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:mx-0 print:sm:px-0 print:lg:px-0"},O={class:"bg-white overflow-hidden shadow-xl sm:rounded-lg min-h-[300px] print:shadow-none"},Q={class:"print:hidden flex-col-config p-5 w-full"},z={class:"w-full grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-1"},E={class:"text-xs sm:text-sm md:text-md whitespace-nowrap truncate"},M=["checked","onChange"],P={class:"flex flex-col w-full print:w-fit"},S={class:"overflow-x-auto print:overflow-hidden sm:-mx-6 lg:-mx-8"},Z={class:"inline-block min-w-full sm:px-6 lg:px-8"},G={class:"overflow-hidden"},H={class:"min-w-full text-center text-sm font-light print:break-inside-avoid"},J={class:"border-b bg-neutral-800 font-medium text-white"},K=e("th",{scope:"col",class:"px-6 py-2 print:hidden"},"Imprimir",-1),R=e("th",{scope:"col",class:"px-6 py-2"},"Nome",-1),T=e("th",{scope:"col",class:"px-6 py-2"},"Qtd. em estoque",-1),U={key:0,scope:"col",class:"px-6 py-2"},W=e("th",{scope:"col",class:"px-6 py-2"},"Categoria",-1),X={key:0,class:"print:break-inside-avoid"},Y={class:"whitespace-nowrap px-6 py-2 print:hidden"},ee={class:"px-6 py-2 font-medium"},te={class:"whitespace-nowrap px-6 py-2"},se={key:0,class:"whitespace-nowrap px-6 py-2"},ne={class:"whitespace-nowrap px-6 py-2"},oe={key:1},ie=e("tr",{class:"border-b transition duration-300 ease-in-out hover:bg-neutral-200"},[e("td",{colspan:"5",class:"whitespace-nowrap px-6 py-2 font-medium"},"Não há items nas categorias selecionadas")],-1),ae=[ie],me={__name:"PrintItems",props:{page:Object,items_list:{type:Array,default:()=>[]},categories_list:{type:Array,default:()=>[]},buy_option:{type:Boolean,default:!1}},setup(r){const h=r,p=_(h.categories_list.map(s=>({id:s.id,category:s.name_plural}))),i=_([...p.value]),v=s=>{const a=i.value.findIndex(t=>t.id===s.id);a!==-1?i.value.splice(a,1):i.value.push(s)},b=()=>{i.value=[...p.value]},w=()=>{i.value=[]},f=N(()=>i.value.length?h.items_list.filter(s=>i.value.some(a=>a.id==s.category_id)):[]),d=_([]),k=s=>{const a=d.value.indexOf(s);a!==-1?d.value.splice(a,1):d.value.push(s)},C=()=>{window.print()};return(s,a)=>(n(),o(m,null,[c(A(B),{title:r.page.name},null,8,["title"]),c(q,{page:r.page},{header:g(()=>[j,c(I,{icon:"printer",onClick:C,title:"Imprimir Lista"})]),default:g(()=>[e("div",D,[e("div",F,[e("div",O,[e("div",Q,[e("ul",z,[(n(!0),o(m,null,x(p.value,t=>(n(),o("li",{key:t.id,class:"py-1 px-2 rounded-full flex flex-row items-center space-x bg-neutral-100 justify-between"},[e("span",E,l(t.category),1),e("span",null,[e("input",{type:"checkbox",checked:i.value.some(u=>u.id===t.id),onChange:u=>v(t),class:"appearance-none transition-colors cursor-pointer w-14 h-7 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-black text-green-500 focus:ring-green-500 focus:bg-green-200 checked:bg-green-500 bg-green-200"},null,40,M)])]))),128))]),e("div",{class:"flex flex-row space-x-5 items-center text-center mt-3"},[e("span",{onClick:b,class:"text-blue-500 font-bold hover:bg-blue-100 active:bg-blue-200 p-3 rounded-full cursor-pointer select-none"},"Marcar todos"),e("span",{onClick:w,class:"text-red-500 font-bold hover:bg-red-100 active:bg-red-200 p-3 rounded-full cursor-pointer select-none"},"Desmarcar todos")])]),e("div",P,[e("div",S,[e("div",Z,[e("div",G,[e("table",H,[e("thead",J,[e("tr",null,[K,R,T,r.buy_option?(n(),o("th",U,"Qtd. para compra ")):y("",!0),W])]),f.value.length?(n(),o("tbody",X,[(n(!0),o(m,null,x(f.value,t=>(n(),o("tr",{key:t.id,class:L([{"print:hidden":d.value.includes(t.id)},"border-b transition duration-150 ease-in-out hover:bg-neutral-100 print:break-inside-avoid"])},[e("td",Y,[c($,{onInput:u=>k(t.id),checked:""},null,8,["onInput"])]),e("td",ee,l(t.name),1),e("td",te,l(t.quantity)+" "+l(t.quantity==1?t.measurement_unit.name:t.measurement_unit.name_plural),1),r.buy_option?(n(),o("td",se,l(t.min_quantity-t.quantity)+" "+l(t.min_quantity-t.quantity==1?t.measurement_unit.name:t.measurement_unit.name_plural),1)):y("",!0),e("td",ne,l(t.category.name),1)],2))),128))])):(n(),o("tbody",oe,ae))])])])])])])])]),c(V,{mode:"rollup"})]),_:1},8,["page"])],64))}};export{me as default};