import { T as w, o as i, e as d, b as t, u as s, Z as b, w as m, t as k, h as u, a, c as x, f as c, j as y, n as h, i as v, F as V } from "./app-CQCMULRS.js"; import { A as B } from "./AuthenticationCard-BkVoWs8L.js"; import { A as C } from "./AuthenticationCardLogo-Drn4GA8i.js"; import { _ as $ } from "./Checkbox-yBpD-_qz.js"; import { _ as f, a as p } from "./TextInput-BCvVd_uq.js"; import { _ as g } from "./InputLabel-D-VFDvNE.js"; import { _ as A } from "./PrimaryButton-CCpSxDR1.js"; import "./_plugin-vue_export-helper-DlAUqK2U.js"; const F = { key: 0, class: "mb-4 font-medium text-sm text-green-600 dark:text-green-400" }, L = { class: "mt-4" }, N = { class: "block mt-4" }, q = { class: "flex items-center" }, P = a("span", { class: "ms-2 text-sm text-gray-600 dark:text-gray-400" }, "Lembre-se de mim", -1), R = { class: "flex items-center justify-end mt-4" }, Z = { __name: "Login", props: { canResetPassword: Boolean, status: String }, setup(l) { const e = w({ email: "", password: "", remember: !1 }), _ = () => { e.transform(n => ({ ...n, remember: e.remember ? "on" : "" })).post(route("login"), { onFinish: () => e.reset("password") }) }; return (n, o) => (i(), d(V, null, [t(s(b), { title: "Log in" }), t(B, null, { logo: m(() => [t(C)]), default: m(() => [l.status ? (i(), d("div", F, k(l.status), 1)) : u("", !0), a("form", { onSubmit: v(_, ["prevent"]) }, [a("div", null, [t(g, { for: "email", value: "Email" }), t(f, { id: "email", modelValue: s(e).email, "onUpdate:modelValue": o[0] || (o[0] = r => s(e).email = r), type: "email", class: "mt-1 block w-full", required: "", autofocus: "", autocomplete: "username" }, null, 8, ["modelValue"]), t(p, { class: "mt-2", message: s(e).errors.email }, null, 8, ["message"])]), a("div", L, [t(g, { for: "password", value: "Senha" }), t(f, { id: "password", modelValue: s(e).password, "onUpdate:modelValue": o[1] || (o[1] = r => s(e).password = r), type: "password", class: "mt-1 block w-full", required: "", autocomplete: "current-password" }, null, 8, ["modelValue"]), t(p, { class: "mt-2", message: s(e).errors.password }, null, 8, ["message"])]), a("div", N, [a("label", q, [t($, { checked: s(e).remember, "onUpdate:checked": o[2] || (o[2] = r => s(e).remember = r), name: "remember" }, null, 8, ["checked"]), P])]), a("div", R, [l.canResetPassword ? (i(), x(s(y), { key: 0, href: n.route("password.request"), class: "underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" }, { default: m(() => [c(" Forgot your password? ")]), _: 1 }, 8, ["href"])) : u("", !0), t(A, { class: h(["ms-4", { "opacity-25": s(e).processing }]), disabled: s(e).processing }, { default: m(() => [c(" Log in ")]), _: 1 }, 8, ["class", "disabled"])])], 32)]), _: 1 })], 64)) } }; export { Z as default };