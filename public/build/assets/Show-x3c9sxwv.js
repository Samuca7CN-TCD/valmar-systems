import{_ as c}from"./AppLayout-BpgBCMDE.js";import p from"./DeleteUserForm-RELRgS1p.js";import l from"./LogoutOtherBrowserSessionsForm-5vCHPcQ7.js";import{S as s}from"./SectionBorder-DnjWoG0v.js";import f from"./TwoFactorAuthenticationForm-D80sLElB.js";import u from"./UpdatePasswordForm-CTpVuK2w.js";import d from"./UpdateProfileInformationForm-FxULCHy7.js";import{o,c as _,w as n,a as i,e as r,b as t,f as a,F as h}from"./app-B9AaS3di.js";import"./DialogModal-9gg8G9_6.js";import"./SectionTitle-87WhAzgr.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./Modal-tn0Up1zP.js";import"./DangerButton-BO6dyJjV.js";import"./TextInput-DNwentXv.js";import"./SecondaryButton-DopaOyGA.js";import"./ActionMessage-Du8voaq-.js";import"./PrimaryButton-C6t27UzX.js";import"./InputLabel-BR6BrfA6.js";import"./FormSection-CiWHEXkW.js";const g=i("h2",{class:"font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"}," Profile ",-1),$={class:"max-w-7xl mx-auto py-10 sm:px-6 lg:px-8"},k={key:0},w={key:1},y={key:2},G={__name:"Show",props:{confirmsTwoFactorAuthentication:Boolean,sessions:Array},setup(m){return(e,x)=>(o(),_(c,{title:"Profile"},{header:n(()=>[g]),default:n(()=>[i("div",null,[i("div",$,[e.$page.props.jetstream.canUpdateProfileInformation?(o(),r("div",k,[t(d,{user:e.$page.props.auth.user},null,8,["user"]),t(s)])):a("",!0),e.$page.props.jetstream.canUpdatePassword?(o(),r("div",w,[t(u,{class:"mt-10 sm:mt-0"}),t(s)])):a("",!0),e.$page.props.jetstream.canManageTwoFactorAuthentication?(o(),r("div",y,[t(f,{"requires-confirmation":m.confirmsTwoFactorAuthentication,class:"mt-10 sm:mt-0"},null,8,["requires-confirmation"]),t(s)])):a("",!0),t(l,{sessions:m.sessions,class:"mt-10 sm:mt-0"},null,8,["sessions"]),e.$page.props.jetstream.hasAccountDeletionFeatures?(o(),r(h,{key:3},[t(s),t(p,{class:"mt-10 sm:mt-0"})],64)):a("",!0)])])]),_:1}))}};export{G as default};