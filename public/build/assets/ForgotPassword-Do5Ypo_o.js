import { T as u, o as i, e as m, b as e, u as t, Z as c, w as o, t as f, h as p, a, n as _, f as g, i as w, F as x } from "./app-CQCMULRS.js"; import { A as y } from "./AuthenticationCard-BkVoWs8L.js"; import { A as b } from "./AuthenticationCardLogo-Drn4GA8i.js"; import { _ as h, a as k } from "./TextInput-BCvVd_uq.js"; import { _ as V } from "./InputLabel-D-VFDvNE.js"; import { _ as v } from "./PrimaryButton-CCpSxDR1.js"; import "./_plugin-vue_export-helper-DlAUqK2U.js"; const C = a("div", { class: "mb-4 text-sm text-gray-600 dark:text-gray-400" }, " Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one. ", -1), F = { key: 0, class: "mb-4 font-medium text-sm text-green-600 dark:text-green-400" }, N = { class: "flex items-center justify-end mt-4" }, j = { __name: "ForgotPassword", props: { status: String }, setup(r) { const s = u({ email: "" }), n = () => { s.post(route("password.email")) }; return (A, l) => (i(), m(x, null, [e(t(c), { title: "Forgot Senha" }), e(y, null, { logo: o(() => [e(b)]), default: o(() => [C, r.status ? (i(), m("div", F, f(r.status), 1)) : p("", !0), a("form", { onSubmit: w(n, ["prevent"]) }, [a("div", null, [e(V, { for: "email", value: "Email" }), e(h, { id: "email", modelValue: t(s).email, "onUpdate:modelValue": l[0] || (l[0] = d => t(s).email = d), type: "email", class: "mt-1 block w-full", required: "", autofocus: "", autocomplete: "username" }, null, 8, ["modelValue"]), e(k, { class: "mt-2", message: t(s).errors.email }, null, 8, ["message"])]), a("div", N, [e(v, { class: _({ "opacity-25": t(s).processing }), disabled: t(s).processing }, { default: o(() => [g(" Email Senha Reset Link ")]), _: 1 }, 8, ["class", "disabled"])])], 32)]), _: 1 })], 64)) } }; export { j as default };