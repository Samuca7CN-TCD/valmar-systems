import{l as I,d as D,o as n,c as U,w as f,a as e,n as M,u as i,h as p,f as T,t as c,i as J,p as h,v as g,e as d,F,g as j,q as P,b as _,y as K,T as W,Z as X,G as Y}from"./app-xjJ8na4r.js";import{r as ee,a as te,b as se,_ as ae}from"./AppLayout-BOBs5ZEO.js";import{_ as oe}from"./CreateUpdateModal-U7ZkrYrZ.js";import{_ as ne}from"./PrimaryButton-DYKnFRqk.js";import{_ as ie}from"./SecondaryButton-C2X9K_i9.js";import{f as Q,t as G}from"./general-DyNoCMvo.js";import{r as re}from"./PlusIcon-Qtp_qUi0.js";import{r as le,a as de}from"./XMarkIcon-ClR3zrEp.js";import{r as Z}from"./EyeIcon-Dl0r3gu1.js";import{_ as ce}from"./FloatButton-DOifkNz7.js";/* empty css                    */import{r as ue}from"./MagnifyingGlassIcon-BlKVGOg-.js";import{r as me}from"./InformationCircleIcon-CFTkfDvi.js";import"./Modal-GnKcZS1t.js";import"./PrinterIcon-EuA9oIlG.js";const pe={class:"pb-12 space-y-12 divide-y-2"},ye={class:"py-4"},_e=e("h2",{class:"text-base font-semibold leading-7 text-gray-900"},"Informações Básicas",-1),he=e("p",{class:"mt-1 text-sm leading-6 text-gray-600"},"Informações sobre a entrada como nome do funcionário, data da entrada e observações",-1),ve={class:"mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6"},xe={class:"sm:col-span-6"},be=e("label",{for:"entry-date",class:"block text-sm font-medium leading-6 text-gray-900 required-input-label"},"Data do Entrada",-1),fe={class:"mt-2"},ge=["max","disabled"],we={key:0,class:"text-red-500 text-sm"},$e={class:"sm:col-span-6"},ke=e("label",{for:"entry-motive",class:"block text-sm font-medium leading-6 text-gray-900 required-input-label"},"Motivo",-1),qe={class:"mt-2"},Ce=["disabled"],Ve={key:0,class:"text-red-500 text-sm"},Ee={class:"sm:col-span-6"},Se=e("label",{for:"entry-observations",class:"block text-sm font-medium leading-6 text-gray-900"},"Observações",-1),Ue={class:"mt-2"},Me=["disabled"],Ie={key:0,class:"text-red-500 text-sm"},De={key:0,class:"py-4"},Fe=e("h2",{class:"text-base font-semibold leading-7 text-gray-900"},"Lista de Itens",-1),Le=e("p",{class:"mt-1 text-sm leading-6 text-gray-600"},"Adicione os itens do entrada.",-1),Oe={class:"mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-4"},Te={class:"sm:col-span-4"},je={class:"relative"},Ae=e("label",{for:"item-selecter",class:"block text-sm font-medium leading-6 text-gray-900"},"Selecione os itens de entrada",-1),Be=["disabled"],Ne={key:0,class:"absolute z-10 w-full mx-auto sm:w-96 bg-white border rounded mt-2 h-96 overflow-y-scroll"},ze=["onMousedown"],Re={class:"sm:col-span-4"},Qe={key:0,class:"min-w-full text-left text-sm font-medium leading-6 text-gray-900"},We={class:"border-b font-medium"},Ge=e("th",{scope:"col",class:"px-6 py-4 text-center"},"#",-1),Pe=e("th",{scope:"col",class:"px-6 py-4 text-center"},"Item",-1),Ze=e("th",{scope:"col",class:"px-6 py-4 text-center"},"Qtd. Estoque",-1),He=e("th",{scope:"col",class:"px-6 py-4 text-center"},"Qtd. Entrada",-1),Je=e("th",{scope:"col",class:"px-6 py-4 text-center"},"Und. Medida",-1),Ke=e("th",{scope:"col",class:"px-6 py-4 text-center"},"Funcionário",-1),Xe={key:0,scope:"col",class:"px-6 py-4 text-center"},Ye={class:"whitespace-nowrap py-4 text-center font-medium"},et={class:"whitespace-nowrap py-4 text-center font-medium"},tt={class:"whitespace-nowrap py-4 text-center font-medium flex justify-center"},st=["value","onInput","disabled"],at={class:"whitespace-nowrap py-4 text-center font-medium"},ot={class:"whitespace-nowrap py-4 text-center font-medium"},nt=["disabled","onUpdate:modelValue"],it=["value"],rt={key:0,class:"whitespace-nowrap py-4 text-center font-mono text-2xl"},lt=["onClick","title"],dt={key:1,class:"p-5 bg-gray-200 rounded-md block text-sm font-medium leading-6 text-gray-900"},ct={__name:"CreateUpdateEntriesModal",props:{modal:Object,entry:{type:Object,default:null},items:Array,employees_list:Array},emits:["close","submit"],setup(a,{emit:x}){var l;const L=x,r=a,w=/^\d{4}-\d{2}-\d{2}$/,m=I(()=>{const{motive:s,date:o}=r.entry;return!s||!s.length?!1:o&&w.test(o)}),V=I(()=>{const{employee:s,motive:o,date:t,items_list:u}=r.entry;return!o||!o.length||!t||!w.test(t)||u.length<1?!1:u.every(S=>S.movement_quantity>0&&S.employee_id!==null)}),b=I(()=>r.modal.mode==="see"),E=D(""),v=D(!1),A=I(()=>r.items.filter(s=>s.name.toLowerCase().includes(E.value.toLowerCase()))),$=s=>{s==="in"&&(v.value=!0),s==="out"&&(v.value=!1)},O=()=>{r.entry.estimated_value=r.entry.items_list.reduce((s,o)=>s+o.price*o.movement_quantity,0),r.entry.total_value=r.entry.estimated_value},k=D(r.employees_list?(l=r.employees_list[0])==null?void 0:l.id:null),N=s=>{r.entry.items_list.push({id:s.id,name:s.name,quantity:s.quantity,max_quantity:s.max_quantity,min_quantity:s.min_quantity,movement_quantity:1,measurement_unit:s.measurement_unit.abbreviation,price:s.price,amount:s.price,employee_id:k.value}),E.value="",v.value=!1,O()},z=s=>{r.entry.items_list=r.entry.items_list.filter(o=>o.id!==s),O()},B=()=>{L("close")},y=()=>{L("submit")};return(s,o)=>(n(),U(oe,{show:a.modal.show,maxWidth:m.value||b.value?"3xl":"2xl",onClose:B},{icon:f(()=>[e("div",{class:M(["mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-400 sm:mx-0 sm:h-10 sm:w-10",{"bg-yellow-400":a.modal.mode==="update","bg-green-400":a.modal.mode==="pay","bg-gray-400":a.modal.mode==="see"}])},[a.modal.mode==="create"?(n(),U(i(re),{key:0,class:"w-5 h-5 text-white"})):p("",!0),a.modal.mode==="update"?(n(),U(i(le),{key:1,class:"w-5 h-5 text-white"})):p("",!0),a.modal.mode==="pay"?(n(),U(i(ee),{key:2,class:"w-5 h-5 text-white"})):p("",!0),a.modal.mode==="see"?(n(),U(i(Z),{key:3,class:"w-5 h-5 text-white"})):p("",!0)],2)]),title:f(()=>[T(c(a.modal.title),1)]),content:f(()=>[e("form",{onSubmit:J(y,["prevent"])},[e("div",pe,[e("section",ye,[_e,he,e("div",ve,[e("div",xe,[be,e("div",fe,[h(e("input",{type:"date",name:"entry-date",id:"entry-date",autocomplete:"on",class:"simple-input disabled:bg-gray-200",autofocus:"true",placeholder:"Data do Entrada",max:i(Q)(),disabled:b.value,"onUpdate:modelValue":o[0]||(o[0]=t=>a.entry.date=t),required:""},null,8,ge),[[g,a.entry.date]]),a.entry.errors.date?(n(),d("p",we,c(a.entry.errors.date),1)):p("",!0)])]),e("div",$e,[ke,e("div",qe,[h(e("input",{type:"text",name:"entry-motive",id:"entry-motive",autocomplete:"on",class:"simple-input disabled:bg-gray-200",disabled:b.value,placeholder:"Motivo do Entrada","onUpdate:modelValue":o[1]||(o[1]=t=>a.entry.motive=t),required:""},null,8,Ce),[[g,a.entry.motive]]),a.entry.errors.motive?(n(),d("p",Ve,c(a.entry.errors.motive),1)):p("",!0)])]),e("div",Ee,[Se,e("div",Ue,[h(e("textarea",{type:"text",observations:"entry-observations",id:"entry-observations",autocomplete:"on",class:"simple-input disabled:bg-gray-200",placeholder:"Descreva a título mais detalhadamente ou insira informações adicionais",disabled:b.value,"onUpdate:modelValue":o[2]||(o[2]=t=>a.entry.observations=t)},null,8,Me),[[g,a.entry.observations]]),a.entry.errors.observations?(n(),d("p",Ie,c(a.entry.errors.observations),1)):p("",!0)])])])]),m.value||b.value?(n(),d("section",De,[Fe,Le,e("div",Oe,[e("div",Te,[e("div",je,[Ae,h(e("input",{id:"item-selecter","onUpdate:modelValue":o[3]||(o[3]=t=>E.value=t),onFocus:o[4]||(o[4]=t=>$("in")),onBlur:o[5]||(o[5]=t=>$("out")),class:"simple-input disabled:bg-gray-200 px-4 py-2 w-full",placeholder:"Digite para pesquisar",disabled:b.value},null,40,Be),[[g,E.value]]),v.value?(n(),d("ul",Ne,[(n(!0),d(F,null,j(A.value,(t,u)=>(n(),d("li",{key:u,class:"py-2 px-4 cursor-pointer hover:bg-gray-200",onMousedown:q=>N(t)},[e("span",{class:M(["inline",{"text-blue-500":t.quantity!=0&&t.quantity>=t.max_quantity,"text-green-500":t.quantity<t.max_quantity&&t.quantity>=t.min_quantity,"text-yellow-500":t.quantity<t.min_quantity&&t.quantity>0,"text-red-500":t.quantity==0}])},"("+c(t.quantity)+")",3),T(" "+c(t.name),1)],40,ze))),128))])):p("",!0)])]),e("div",Re,[r.entry.items_list.length?(n(),d("table",Qe,[e("thead",We,[e("tr",null,[Ge,Pe,Ze,He,Je,Ke,a.modal.mode!=="see"?(n(),d("th",Xe," Deletar")):p("",!0)])]),e("tbody",null,[(n(!0),d(F,null,j(r.entry.items_list,t=>(n(),d("tr",{key:t.id,class:M(["border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid",{"bg-yellow-100":t.movement_quantity==t.quantity,"bg-red-100":t.movement_quantity>t.quantity}])},[e("td",Ye,c(t.id),1),e("td",et,c(t.name),1),e("td",{class:M(["whitespace-nowrap py-4 text-center font-medium",{"text-blue-500":t.quantity!=0&&t.quantity>=t.max_quantity,"text-green-500":t.quantity<t.max_quantity&&t.quantity>=t.min_quantity,"text-yellow-500":t.quantity<t.min_quantity&&t.quantity>0,"text-red-500":t.quantity==0}])},c(t.quantity),3),e("td",tt,[e("input",{type:"number",min:"0.01",step:"0.01",value:t.movement_quantity,onInput:u=>{t.movement_quantity=parseFloat(u.target.value),t.amount=t.price*u.target.value,O()},class:"simple-input disabled:bg-gray-200 w-2/3",disabled:a.modal.mode!=="create"},null,40,st)]),e("td",at,c(t.measurement_unit),1),e("td",ot,[h(e("select",{class:"simple-select disabled:bg-gray-200",disabled:a.modal.mode!=="create","onUpdate:modelValue":u=>t.employee_id=u,onChange:o[6]||(o[6]=u=>k.value=u.target.value),required:""},[(n(!0),d(F,null,j(a.employees_list,u=>(n(),d("option",{key:u.id,value:u.id},c(u.name)+" "+c(u.surname),9,it))),128))],40,nt),[[P,t.employee_id]])]),a.modal.mode!=="see"?(n(),d("td",rt,[e("button",{onClick:u=>z(t.id),title:"Remover "+t.name},"×",8,lt)])):p("",!0)],2))),128))])])):(n(),d("p",dt," Nenhum item selecionado até o momento!"))])])])):p("",!0)])],32)]),footer:f(()=>[a.modal.mode!=="see"?(n(),U(ie,{key:0,class:M({disabled:a.entry.processing}),onClick:o[7]||(o[7]=t=>B())},{default:f(()=>[T(" Cancelar ")]),_:1},8,["class"])):p("",!0),_(ne,{class:M({disabled:a.entry.processing||!V.value}),disabled:a.entry.processing||!V.value,onClick:o[8]||(o[8]=t=>y())},{default:f(()=>[T(c(a.modal.primary_button_txt),1)]),_:1},8,["class","disabled"])]),_:1},8,["show","maxWidth"]))}},ut={class:"font-semibold text-xl text-gray-800 leading-tight"},mt={class:"pt-12 print:hidden print:pt-0"},pt={class:"max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full"},yt={class:"bg-white overflow-hidden shadow-xl sm:rounded-lg p-5"},_t={class:"flex flex-row items-center space-x-3"},ht={class:"cursor-help select-none",title:"O campo de pesquisa ao lado filtra as informações carregadas na pagina. O formulário de pesquisa abaixo busca as informações filtradas no banco de dados e lista na página."},vt={class:"mt-3 grid grid-cols-4 gap-2"},xt={class:"col-span-4 md:col-span-1"},bt=e("label",{for:"search-by-employee",class:"block text-sm font-medium leading-6 text-gray-900"},"Funcionário",-1),ft={class:"mt-2"},gt=e("option",{value:0},"Todos",-1),wt=["value"],$t={key:0,class:"text-red-500 text-sm"},kt={class:"col-span-4 md:col-span-1"},qt=e("label",{for:"search-by-motive",class:"block text-sm font-medium leading-6 text-gray-900"},"Motivo",-1),Ct={class:"mt-2"},Vt={key:0,class:"text-red-500 text-sm"},Et={class:"col-span-2 md:col-span-1"},St=e("label",{for:"search-by-start-date",class:"block text-sm font-medium leading-6 text-gray-900"},"Data (início)",-1),Ut={class:"mt-2"},Mt={key:0,class:"text-red-500 text-sm"},It={class:"col-span-2 md:col-span-1"},Dt=e("label",{for:"search-by-end-date",class:"block text-sm font-medium leading-6 text-gray-900"},"Data (fim)",-1),Ft={class:"mt-2"},Lt={key:0,class:"text-red-500 text-sm"},Ot=["href"],Tt={class:"py-12 print:py-6"},jt={class:"max-w-7xl mx-auto print:max-w-full"},At={class:"px-0 print:px-0"},Bt={class:"bg-white overflow-hidden shadow-xl sm:rounded-lg print:shadow-none"},Nt={class:"flex flex-col"},zt={class:"overflow-x-auto sm:-mx-6 lg:-mx-8"},Rt={class:"inline-block min-w-full py-2 sm:px-6 lg:px-8"},Qt={class:"overflow-hidden"},Wt={class:"min-w-full text-left text-sm font-light"},Gt=e("thead",{class:"border-b font-medium"},[e("tr",null,[e("th",{scope:"col",class:"px-6 py-4 text-center"},"#"),e("th",{scope:"col",class:"px-6 py-4 text-center"},"Motivo"),e("th",{scope:"col",class:"px-6 py-4 text-center"},"Data do Entrada "),e("th",{scope:"col",class:"px-6 py-4 text-center print:hidden",colspan:"2"}," Ações")])],-1),Pt={class:"border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid"},Zt={class:"whitespace-nowrap py-4 text-center font-medium"},Ht={class:"whitespace-nowrap px-2 py-4 text-center"},Jt=["title"],Kt={class:"whitespace-nowrap px-2 py-4 text-center print:hidden"},Xt=["onClick"],Yt=["onClick"],es={key:1,class:"transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid"},ts=e("td",{class:"whitespace-nowrap px-6 py-4 text-center",colspan:"9"},"Não há entradas cadastradas no momento!",-1),ss=[ts],xs={__name:"Entries",props:{page:Object,page_options:{type:Array,default:null},entries_list:Object,employees_list:Array,items:Object,parameters:Object},setup(a){const x=a,L=K.useToast(),r=W({id:null,employee:"",motive:"",date:Q(),total_value:0,observations:"",items_list:[]}),w=D(!1),m=W({employee_id:x.parameters.employee_id,motive:x.parameters.motive,start_date:x.parameters.start_date,end_date:x.parameters.end_date}),V=D(""),b=I(()=>{const y=V.value.toLowerCase();return x.entries_list.filter(l=>{const{id:s,entity_name:o,motive:t,observations:u,date:q,items:S}=l,R=[s.toString(),(o==null?void 0:o.toLowerCase())??"",(t==null?void 0:t.toLowerCase())??"",(u==null?void 0:u.toLowerCase())??"",(q==null?void 0:q.toLowerCase())??""];for(let C of R)if(C.includes(y))return!0;return S.some(C=>C.name.toLowerCase().includes(y))})}),E=I(()=>x.entries_list.reduce((y,l)=>y+l.accounting.partial_value,0)),v=D({mode:"create",show:!1,get title(){switch(this.mode){case"create":return"Criar entrada";case"update":return"Editar entrada";case"see":return"Ver informações da entrada"}},get primary_button_txt(){switch(this.mode){case"create":return"Cadastrar";case"update":return"Atualizar";case"see":return"Fechar"}}}),A=(y,l=null)=>{const s=["update","see"].includes(y);if(l!==null&&s){const o=x.entries_list.find(t=>t.id===l);if(o){const{id:t,entity_name:u,date:q,motive:S,observations:R,accounting:C,items:H}=o;r.id=t,r.employee=u,r.motive=S,r.date=q,r.estimated_value=C.estimated_value,r.total_value=C.total_value,r.partial_value=C.partial_value,r.observations=R,r.items_list=H}}v.value.mode=y,v.value.show=!0},$=()=>{r.reset(),v.value.show=!1},O=()=>{r.post(route("entries.store"),{preserveScroll:!0,onSuccess:()=>$(),onError:y=>{console.log(y)}})},k=()=>{m.post(route("entries.filter"),{preserveScroll:!0})},N=()=>{r.put(route("entries.update",r.id),{preserveScroll:!0,onSuccess:()=>$()})},z=(y,l)=>{confirm(`Você tem certeza que deseja excluir a entrada de material para "${l}"? Todos os materiais comprados voltarão para o estoque! Esta ação não poderá ser desfeita!`)&&r.delete(route("entries.destroy",y),{preserveScroll:!0,onSuccess:()=>L.success("Entrada deletado com sucesso!")})},B=()=>{switch(v.value.mode){case"create":return O();case"update":return N();case"see":return $();default:L.error("Método desconhecido. Informar o Técnico.")}};return(y,l)=>(n(),d(F,null,[_(i(X),{title:a.page.name},null,8,["title"]),_(ae,{page:a.page,page_options:a.page_options},{header:f(()=>[e("h2",ut,c(a.page.name)+" | "+c(i(G)(E.value)),1),_(ce,{icon:"plus",onClick:l[0]||(l[0]=s=>A("create")),title:"Cadastrar Entry",class:"print:hidden"})]),default:f(()=>[e("div",mt,[e("div",pt,[e("div",yt,[e("div",_t,[_(i(ue),{class:"w-5 y-5"}),h(e("input",{type:"text",name:"search_term",id:"search_term",autocomplete:"on",class:"simple-input",autofocus:"true",placeholder:"Pesquisar termo...","onUpdate:modelValue":l[1]||(l[1]=s=>V.value=s)},null,512),[[g,V.value]]),e("span",ht,[_(i(me),{class:"w-5 h-5"})]),w.value?(n(),d("span",{key:1,class:"select-none cursor-pointer",title:"Fechar opções de filtro",onClick:l[3]||(l[3]=s=>w.value=!1)},[_(i(se),{class:"w-5 y-5"})])):(n(),d("span",{key:0,class:"select-none cursor-pointer",title:"Abrir opções de filtro",onClick:l[2]||(l[2]=s=>w.value=!0)},[_(i(te),{class:"w-5 y-5"})]))]),h(e("div",vt,[e("div",xt,[bt,e("div",ft,[h(e("select",{name:"search-by-employee",id:"search-by-employee",class:"simple-select disabled:bg-gray-200","onUpdate:modelValue":l[4]||(l[4]=s=>i(m).employee_id=s),onChange:k},[gt,(n(!0),d(F,null,j(a.employees_list,s=>(n(),d("option",{key:s.id,value:s.id},c(s.name)+" "+c(s.surname),9,wt))),128))],544),[[P,i(m).employee_id]]),i(m).errors.employee_id?(n(),d("p",$t,c(i(m).errors.employee_id),1)):p("",!0)])]),e("div",kt,[qt,e("div",Ct,[h(e("input",{type:"text",name:"search-by-motive",id:"search-by-motive",class:"simple-input disabled:bg-gray-200",placeholder:"Filtrar por motivo","onUpdate:modelValue":l[5]||(l[5]=s=>i(m).motive=s),onInput:k,required:""},null,544),[[g,i(m).motive]]),i(m).errors.motive?(n(),d("p",Vt,c(i(m).errors.motive),1)):p("",!0)])]),e("div",Et,[St,e("div",Ut,[h(e("input",{type:"date",name:"search-by-start-date",id:"search-by-start-date",class:"simple-input disabled:bg-gray-200",placeholder:"Filtrar por período (início)","onUpdate:modelValue":l[6]||(l[6]=s=>i(m).start_date=s),onInput:k,required:""},null,544),[[g,i(m).start_date]]),i(m).errors.start_date?(n(),d("p",Mt,c(i(m).errors.start_date),1)):p("",!0)])]),e("div",It,[Dt,e("div",Ft,[h(e("input",{type:"date",name:"search-by-end-date",id:"search-by-end-date",class:"simple-input disabled:bg-gray-200",placeholder:"Filtrar por período (fim)","onUpdate:modelValue":l[7]||(l[7]=s=>i(m).end_date=s),onInput:k,required:""},null,544),[[g,i(m).end_date]]),i(m).errors.end_date?(n(),d("p",Lt,c(i(m).errors.end_date),1)):p("",!0)])]),e("a",{href:y.route("entries.index"),class:"text-red-500 hover:text-red-700 active:text-red-900 text-sm select-none mt-2",onClick:l[8]||(l[8]=s=>i(m).reset())},"Resetar busca",8,Ot)],512),[[Y,w.value]])])])]),e("div",Tt,[e("div",jt,[e("div",At,[e("div",Bt,[e("div",Nt,[e("div",zt,[e("div",Rt,[e("div",Qt,[e("table",Wt,[Gt,e("tbody",null,[b.value.length?(n(!0),d(F,{key:0},j(b.value,s=>(n(),d("tr",Pt,[e("td",Zt,c(s.id),1),e("td",Ht,[T(c(i(G)(s.motive))+" ",1),s.observations?(n(),d("span",{key:0,title:s.observations,class:"inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help"},"OBS",8,Jt)):p("",!0)]),e("td",Kt,c(i(Q)(s.date,!0)),1),e("td",{class:"whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-indigo-700 active:text-indigo-900 select-none print:hidden",title:"Ver Entrada",onClick:o=>A("see",s.id)},[_(i(Z),{class:"w-5 h-5 m-auto"})],8,Xt),e("td",{class:"whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-red-700 active:text-red-900 select-none print:hidden",title:"Excluir entrada",onClick:o=>z(s.id,s.motive)},[_(i(de),{class:"w-5 h-5 m-auto"})],8,Yt)]))),256)):(n(),d("tr",es,ss))])])])])])])])])])]),_(ct,{modal:v.value,entry:i(r),items:a.items,employees_list:a.employees_list,onSubmit:B,onClose:$},null,8,["modal","entry","items","employees_list"])]),_:1},8,["page","page_options"])],64))}};export{xs as default};