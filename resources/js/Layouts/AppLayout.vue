<script setup>
    import { ref } from 'vue'
    import { Head, router } from '@inertiajs/vue3'
    import Banner from '@/Components/Banner.vue'
    import Dropdown from '@/Components/Dropdown.vue'
    import DropdownLink from '@/Components/DropdownLink.vue'
    import NavLink from '@/Components/NavLink.vue'
    import SubNavLink from '@/Components/SubNavLink.vue'
    import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
    import { RectangleGroupIcon, ChevronDownIcon, ChevronUpIcon, ArrowDownIcon, UserGroupIcon, UserIcon, PresentationChartLineIcon, RectangleStackIcon, ArrowPathRoundedSquareIcon, BanknotesIcon, BriefcaseIcon } from '@heroicons/vue/24/outline'

    const props = defineProps({
        page: Object,
        page_options: {
            type: Array,
            default: null,
        },
        payslip_month: {
            type: Number,
            default: null,
        },
        payslip_year: {
            type: Number,
            default: null,
        }
    })

    const showingNavigationDropdown = ref(false)
    const options_collapse = ref(false)

    const switchToTeam = (team) => {
        router.put(route('current-team.update'), {
            team_id: team.id,
        }, {
            preserveState: false,
        })
    }

    const logout = () => {
        router.post(route('logout'))
    }
</script>

<template>
    <div class="min-w-[320px]">
        <div class="flex flex-row">
            <div v-if="page_options"
                class="hidden xl:block print:hidden md:w-1/4 lg:1/5 xl:1/6 min-h-screen relative bg-white text-black shadow-xl">
                <div class="w-full flex-col-config">
                    <!-- Logo -->
                    <a href="/" class="p-10 bg-slate-100">
                        <img class="hidden xl:block w-fit" src="/storage/img/theme/logos/logo-name.png" />
                        <img class="xl:hidden w-fit" src="/storage/img/theme/logos/complete_logo.svg" />
                    </a>
                    <ul class="w-full">
                        <!-- Item "Todos" -->
                        <SubNavLink v-if="page.type === 'warehouse'" :href="route(page.type + '.index')"
                            :active="route().current(page.type + '.index')" class="w-full hover:bg-gray-100 py-5"
                            :class="{ 'bg-gray-200': route().current(page.type + '.index') }">
                            <li class="text-gray-600 flex flex-row justify-center items-center align-middle md:ml-10">
                                <span class="inline-block mx-2">
                                    <RectangleGroupIcon class="w-4 h-4" />
                                </span>
                                <span class="hidden md:inline-block">Todos</span>
                            </li>
                        </SubNavLink>

                        <!-- Iteração sobre page_options -->
                        <SubNavLink v-if="page.type === 'warehouse'" v-for="option in page_options" :key="option.id"
                            :href="route(`${page.type}.filter`, option.id)"
                            :active="route().current(page.type + '.filter', option.id)"
                            class="w-full hover:bg-gray-100 py-5"
                            :class="{ 'bg-gray-200': route().current(page.type + '.filter', option.id) }">
                            <li class="text-gray-600 flex flex-row justify-center items-center align-middle md:ml-10">
                                <span class="inline-block mx-2">
                                    <RectangleGroupIcon v-if="page.type === 'warehouse'" class="w-4 h-4" />
                                    <UserIcon v-else class="w-4 h-4" />
                                </span>
                                <span class="hidden md:inline-block">{{ option.name }}</span>
                            </li>
                        </SubNavLink>

                        <SubNavLink v-if="page.type === 'payslip'" v-for="option in page_options" :key="option.id"
                            :href="route(`${page.type}.filter`, { employee: option.id, month: payslip_month, year: payslip_year })"
                            :active="route().current(page.type + '.filter', option.id) || (route().current('payslip.index') && option.id === page_options[0].id)"
                            class="w-full hover:bg-gray-100 py-5"
                            :class="{ 'bg-gray-200': route().current(page.type + '.filter', option.id) || (route().current('payslip.index') && option.id === page_options[0].id) }">
                            <li class="text-gray-600 flex flex-row justify-center items-center align-middle md:ml-10">
                                <span class="inline-block mx-2">
                                    <RectangleGroupIcon v-if="page.type === 'warehouse'" class="w-4 h-4" />
                                    <UserIcon v-else class="w-4 h-4" />
                                </span>
                                <span class="hidden md:inline-block">{{ option.name }}</span>
                            </li>
                        </SubNavLink>
                    </ul>

                    <Dropdown />
                </div>
            </div>
            <div class="min-h-screen print:w-full"
                :class="{ 'w-full': !page_options, 'w-full xl:w-5/6': page_options }">

                <Head class="print:hidden" :title="page ? page.name : 'Valmar Inox'" />

                <Banner class="print:hidden" />

                <div class="min-h-screen h-full bg-gray-100 print:bg-white">
                    <nav class="bg-white border-b border-gray-100 print:hidden">
                        <!-- Primary Navigation Menu -->
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-2">
                            <div class="flex justify-between h-16">
                                <div class="flex">
                                    <a href="/" class="h-full flex items-center">
                                        <img src="/storage/img/theme/logos/logo-name.png" class="h-7 my-auto"
                                            :class="{ 'lg:hidden': page_options }" />
                                    </a>

                                    <!-- Navigation Links -->
                                    <div class="hidden space-x-8 lg:space-x-4 xl:space-x-8 sm:-my-px sm:ml-10 lg:flex">
                                        <NavLink v-if="$page.props.auth.user.hierarchy < 2"
                                            :href="route('dashboard.index')"
                                            :active="route().current('home') || route().current('dashboard.*')">
                                            <span class="inline-block mx-2">
                                                <PresentationChartLineIcon class="w-4 h-4" />
                                            </span>
                                            <span class="inline-block">Dashboard</span>
                                        </NavLink>

                                        <NavLink :has_dropdown="true" :href="'#'"
                                            :active="route().current('warehouse.*')">
                                            <template #trigger>
                                                <span class="inline-block mx-2">
                                                    <RectangleStackIcon class="w-4 h-4" />
                                                </span>
                                                Almoxarifado
                                                <ChevronDownIcon class="ml-2 -mr-0.5 mt-0.5 h-4 w-4 text-gray-700" />
                                            </template>
                                            <template #options>
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Página principal
                                                </div>
                                                <DropdownLink :href="route('warehouse.index')">Almoxarifado
                                                </DropdownLink>
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Páginas Auxiliares
                                                </div>
                                                <DropdownLink :href="route('warehouse.print_items', 0)">Lista de Items
                                                </DropdownLink>
                                                <DropdownLink :href="route('warehouse.print_items', 1)">Lista
                                                    para
                                                    Compra
                                                </DropdownLink>
                                                <DropdownLink :href="route('warehouse.use_table')">Tabela de Usos
                                                </DropdownLink>
                                            </template>
                                        </NavLink>

                                        <NavLink :has_dropdown="true" :href="'#'"
                                            :active="route().current('entries.*') || route().current('uses.*') || route().current('sells.*')">
                                            <template #trigger>
                                                <span class="inline-block mx-2">
                                                    <ArrowPathRoundedSquareIcon class="w-4 h-4" />
                                                </span>
                                                Movimentações
                                                <ChevronDownIcon class="ml-2 -mr-0.5 mt-0.5 h-4 w-4 text-gray-700" />
                                            </template>
                                            <template #options>
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Opções de movimentação
                                                </div>
                                                <DropdownLink :href="route('entries.index')">Entradas
                                                </DropdownLink>
                                                <DropdownLink :href="route('uses.index')">Usos
                                                </DropdownLink>
                                                <DropdownLink :href="route('sells.index')">Vendas
                                                </DropdownLink>
                                            </template>
                                        </NavLink>
                                        <NavLink v-if="$page.props.auth.user.hierarchy < 3"
                                            :href="route('payments.index')" :active="route().current('payments.*')">
                                            <span class="inline-block mx-2">
                                                <BanknotesIcon class="w-4 h-4" />
                                            </span>
                                            <span class="inline-block">Pagamentos</span>
                                        </NavLink>
                                        <NavLink v-if="$page.props.auth.user.hierarchy < 3"
                                            :href="route('services.index')" :active="route().current('services.*')">
                                            <span class="inline-block mx-2">
                                                <BriefcaseIcon class="w-4 h-4" />
                                            </span>
                                            <span class="inline-block">Serviços</span>
                                        </NavLink>
                                        <NavLink :has_dropdown="true" :href="'#'"
                                            :active="route().current('employees.*')">
                                            <template #trigger>
                                                <span class="inline-block mx-2">
                                                    <UserGroupIcon class="w-4 h-4" />
                                                </span>
                                                Funcionários
                                                <ChevronDownIcon class="ml-2 -mr-0.5 mt-0.5 h-4 w-4 text-gray-700" />
                                            </template>
                                            <template #options>
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Página principal
                                                </div>
                                                <DropdownLink :href="route('employees.index')">Funcionários
                                                </DropdownLink>
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Pagamento de Funcionários
                                                </div>
                                                <DropdownLink :href="route('payslip.index')">Holerite
                                                </DropdownLink>
                                                <DropdownLink :href="route('employee.overtime_calculation')">Cálculo
                                                    de
                                                    Horas Extras
                                                </DropdownLink>
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Impressões
                                                </div>
                                                <DropdownLink :href="route('payslip.print', { mode: 'control' })">
                                                    Controle de Pagamentos
                                                </DropdownLink>
                                                <DropdownLink :href="route('payslip.print', { mode: 'payslip' })">
                                                    Recibos de Holerite
                                                </DropdownLink>
                                                <DropdownLink
                                                    :href="route('payslip.print', { mode: 'transportation-voucher' })">
                                                    Recibos de Vale Transporte
                                                </DropdownLink>
                                            </template>
                                        </NavLink>
                                    </div>
                                </div>

                                <div class="hidden lg:flex sm:items-center sm:ml-6">
                                    <div class="ml-3 relative">
                                        <!-- Teams Dropdown -->
                                        <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                            <template #trigger>
                                                <span class="inline-flex rounded-md">
                                                    <button type="button"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition">
                                                        {{ $page.props.auth.user.current_team.name }}

                                                        <svg class="ml-2 -mr-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>

                                            <template #content>
                                                <div class="w-60">
                                                    <!-- Team Management -->
                                                    <template v-if="$page.props.jetstream.hasTeamFeatures">
                                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                                            Manage Team
                                                        </div>

                                                        <!-- Team Settings -->
                                                        <DropdownLink
                                                            :href="route('teams.show', $page.props.auth.user.current_team)">
                                                            Team Settings
                                                        </DropdownLink>

                                                        <DropdownLink v-if="$page.props.jetstream.canCreateTeams"
                                                            :href="route('teams.create')">
                                                            Create New Team
                                                        </DropdownLink>

                                                        <div class="border-t border-gray-100" />

                                                        <!-- Team Switcher -->
                                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                                            Switch Teams
                                                        </div>

                                                        <template v-for="team in $page.props.auth.user.all_teams"
                                                            :key="team.id">
                                                            <form @submit.prevent="switchToTeam(team)">
                                                                <DropdownLink as="button">
                                                                    <div class="flex items-center">
                                                                        <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                                            class="mr-2 h-5 w-5 text-green-400"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            fill="none" viewBox="0 0 24 24"
                                                                            stroke-width="1.5" stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>

                                                                        <div>{{ team.name }}</div>
                                                                    </div>
                                                                </DropdownLink>
                                                            </form>
                                                        </template>
                                                    </template>
                                                </div>
                                            </template>
                                        </Dropdown>
                                    </div>

                                    <!-- Settings Dropdown -->
                                    <div class="ml-3 relative">
                                        <Dropdown align="right" width="48">
                                            <template #trigger>
                                                <button v-if="$page.props.jetstream.managesProfilePhotos"
                                                    class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                    <img class="h-8 w-8 rounded-full object-cover"
                                                        :src="$page.props.auth.user.profile_photo_url"
                                                        :alt="$page.props.auth.user.name">
                                                </button>

                                                <span v-else class="inline-flex rounded-md">
                                                    <button type="button"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition">
                                                        {{ $page.props.auth.user.name }}

                                                        <svg class="ml-2 -mr-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>

                                            <template #content>
                                                <!-- Account Management -->
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Gerenciar Conta
                                                </div>

                                                <DropdownLink :href="route('profile.show')">
                                                    Profile
                                                </DropdownLink>

                                                <DropdownLink v-if="$page.props.jetstream.hasApiFeatures"
                                                    :href="route('api-tokens.index')">
                                                    Tokens de API
                                                </DropdownLink>

                                                <DropdownLink v-if="$page.props.auth.user.hierarchy === 1"
                                                    :href="route('register')">
                                                    Resgistrar novo usuário
                                                </DropdownLink>

                                                <div class="border-t border-gray-100" />

                                                <!-- Authentication -->
                                                <form @submit.prevent="logout">
                                                    <DropdownLink as="button">
                                                        Sair
                                                    </DropdownLink>
                                                </form>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>

                                <!-- Hamburger -->
                                <div class="-mr-2 flex items-center lg:hidden">
                                    <button
                                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition"
                                        @click="showingNavigationDropdown = !showingNavigationDropdown">
                                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                            <path
                                                :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6h16M4 12h16M4 18h16" />
                                            <path
                                                :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Responsive Navigation Menu -->
                        <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                            class="xl:hidden">
                            <div class="pt-2 pb-3 space-y-1">
                                <ResponsiveNavLink :href="route('dashboard.index')"
                                    :active="route().current('home') || route().current('dashboard.*')">
                                    <span class="inline-block mx-2">
                                        <PresentationChartLineIcon class="w-4 h-4" />
                                    </span>
                                    <span class="inline-block">Dashboard</span>
                                </ResponsiveNavLink>
                                <ResponsiveNavLink :has_dropdown="true" :href="'#'"
                                    :active="route().current('warehouse.*')">
                                    <template #trigger>
                                        <span class="inline-block mx-2">
                                            <RectangleStackIcon class="w-4 h-4" />
                                        </span>
                                        Almoxarifado
                                        <ChevronDownIcon class="ml-2 -mr-0.5 mt-0.5 h-4 w-4 text-gray-700" />
                                    </template>
                                    <template #options>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Página principal
                                        </div>
                                        <DropdownLink :href="route('warehouse.index')">Almoxarifado
                                        </DropdownLink>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Páginas Auxiliares
                                        </div>
                                        <DropdownLink :href="route('warehouse.print_items', 0)">Lista de Items
                                        </DropdownLink>
                                        <DropdownLink :href="route('warehouse.print_items', 1)">Lista
                                            para
                                            Compra
                                        </DropdownLink>
                                        <DropdownLink :href="route('warehouse.use_table')">Tabela de Usos
                                        </DropdownLink>
                                    </template>
                                </ResponsiveNavLink>

                                <ResponsiveNavLink :has_dropdown="true" :href="'#'"
                                    :active="route().current('entries.*') || route().current('uses.*') || route().current('sells.*')">
                                    <template #trigger>
                                        <span class="inline-block mx-2">
                                            <ArrowPathRoundedSquareIcon class="w-4 h-4" />
                                        </span>
                                        Movimentações
                                        <ChevronDownIcon class="ml-2 -mr-0.5 mt-0.5 h-4 w-4 text-gray-700" />
                                    </template>
                                    <template #options>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Opções de movimentação
                                        </div>
                                        <DropdownLink :href="route('entries.index')">Entradas
                                        </DropdownLink>
                                        <DropdownLink :href="route('uses.index')">Usos
                                        </DropdownLink>
                                        <DropdownLink :href="route('sells.index')">Vendas
                                        </DropdownLink>
                                    </template>
                                </ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('payments.index')"
                                    :active="route().current('payments.*')">
                                    <span class="inline-block mx-2">
                                        <BanknotesIcon class="w-4 h-4" />
                                    </span>
                                    <span class="inline-block">Pagamentos</span>
                                </ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('services.index')"
                                    :active="route().current('services.*')">
                                    <span class="inline-block mx-2">
                                        <BriefcaseIcon class="w-4 h-4" />
                                    </span>
                                    <span class="inline-block">Serviços</span>
                                </ResponsiveNavLink>
                                <ResponsiveNavLink :has_dropdown="true" :href="'#'"
                                    :active="route().current('employees.*')">
                                    <template #trigger>
                                        <span class="inline-block mx-2">
                                            <UserGroupIcon class="w-4 h-4" />
                                        </span>
                                        Funcionários
                                        <ChevronDownIcon class="ml-2 -mr-0.5 mt-0.5 h-4 w-4 text-gray-700" />
                                    </template>
                                    <template #options>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Página principal
                                        </div>
                                        <DropdownLink :href="route('employees.index')">Funcionários
                                        </DropdownLink>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Pagamento de Funcionários
                                        </div>
                                        <DropdownLink :href="route('payslip.index')">Holerite
                                        </DropdownLink>
                                        <DropdownLink :href="route('employee.overtime_calculation')">Cálculo de
                                            Horas Extras
                                        </DropdownLink>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Impressões
                                        </div>
                                        <DropdownLink :href="route('payslip.print', { mode: 'control' })">
                                            Controle de Pagamentos
                                        </DropdownLink>
                                        <DropdownLink :href="route('payslip.print', { mode: 'payslip' })">
                                            Recibos de Holerite
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('payslip.print', { mode: 'transportation-voucher' })">
                                            Recibos de Vale Transporte
                                        </DropdownLink>
                                    </template>
                                </ResponsiveNavLink>













                            </div>

                            <!-- Responsive Settings Options -->
                            <div class="pt-4 pb-1 border-t border-gray-200">
                                <div class="flex items-center px-4">
                                    <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 mr-3">
                                        <img class="w-fit rounded-full object-cover"
                                            :src="$page.props.auth.user.profile_photo_url"
                                            :alt="$page.props.auth.user.name">
                                    </div>

                                    <div>
                                        <div class="font-medium text-base text-gray-800">
                                            {{ $page.props.auth.user.name }}
                                        </div>
                                        <div class="font-medium text-sm text-gray-500">
                                            {{ $page.props.auth.user.email }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 space-y-1">
                                    <ResponsiveNavLink :href="route('profile.show')"
                                        :active="route().current('profile.show')">
                                        Profile
                                    </ResponsiveNavLink>

                                    <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures"
                                        :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                        Tokens de API
                                    </ResponsiveNavLink>

                                    <!-- Authentication -->
                                    <form method="POST" @submit.prevent="logout">
                                        <ResponsiveNavLink as="button">
                                            Sair
                                        </ResponsiveNavLink>
                                    </form>

                                    <!-- Team Management -->
                                    <template v-if="$page.props.jetstream.hasTeamFeatures">
                                        <div class="border-t border-gray-200" />

                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Gerenciar Time
                                        </div>

                                        <!-- Team Settings -->
                                        <ResponsiveNavLink
                                            :href="route('teams.show', $page.props.auth.user.current_team)"
                                            :active="route().current('teams.show')">
                                            Configurações do Time
                                        </ResponsiveNavLink>

                                        <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams"
                                            :href="route('teams.create')" :active="route().current('teams.create')">
                                            Criar Novo Time
                                        </ResponsiveNavLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Team Switcher -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Alternar Times
                                        </div>

                                        <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                            <form @submit.prevent="switchToTeam(team)">
                                                <ResponsiveNavLink as="button">
                                                    <div class="flex items-center">
                                                        <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                            class="mr-2 h-5 w-5 text-green-400"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <div>{{ team.name }}</div>
                                                    </div>
                                                </ResponsiveNavLink>
                                            </form>
                                        </template>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </nav>

                    <!-- Page Heading -->
                    <header v-if="$slots.header" class="bg-white shadow border-b md:border-none print:hidden">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            <slot name="header" />
                        </div>
                    </header>


                    <div v-if="page_options"
                        class="xl:hidden print:hidden bg-white shadow border-b md:border-none text-gray-700 select-none cursor-pointer"
                        title="Clique para ver as opções da página" @click="options_collapse = !options_collapse">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-row space-x-3 items-center">
                            <p>Opções da Página</p>
                            <ChevronUpIcon v-if="options_collapse" class="w-4 h-4" />
                            <ChevronDownIcon v-else class="w-4 h-4" />
                        </div>

                        <ul v-if="options_collapse" class="w-full">
                            <SubNavLink v-if="page.type === 'warehouse'" :href="route(page.type + '.index')"
                                :active="route().current(page.type + '.index')" class="w-full hover:bg-gray-100 py-5"
                                :class="{ 'bg-gray-200': route().current(`${page.type}.index`) }">
                                <li class="text-gray-600 flex items-center">
                                    <span class="inline-block mx-2">
                                        <RectangleGroupIcon class="w-4 h-4" />
                                    </span>
                                    <span>Todos</span>
                                </li>
                            </SubNavLink>


                            <SubNavLink v-if="page.type === 'warehouse'" v-for="option in page_options" :key="option.id"
                                :href="route(`${page.type}.filter`, option.id)"
                                :active="route().current(`${page.type}.filter`, option.id)"
                                class="w-full hover:bg-gray-100 py-5"
                                :class="{ 'bg-gray-200': (route().current(`${page.type}.filter`, option.id)) }">
                                <li class="text-gray-600 flex items-center">
                                    <span class="inline-block mx-2">
                                        <RectangleGroupIcon class="w-4 h-4" />
                                    </span>
                                    <span>{{ option.name }}</span>
                                </li>
                            </SubNavLink>

                            <SubNavLink v-if="page.type === 'payslip'" v-for="option in page_options" :key="option.id"
                                :href="route(`${page.type}.filter`, { employee: option.id, month: payslip_month, year: payslip_year })"
                                :active="route().current(`${page.type}.filter`, option.id) || (route().current('payslip.index') && option.id === page_options[0].id)"
                                class="w-full hover:bg-gray-100 py-5"
                                :class="{ 'bg-gray-200': (route().current(`${page.type}.filter`, option.id) || (route().current('payslip.index') && option.id === page_options[0].id)) }">
                                <li class="text-gray-600 flex items-center">
                                    <span class="inline-block mx-2">
                                        <UserIcon class="w-4 h-4" />
                                    </span>
                                    <span>{{ option.name }}</span>
                                </li>
                            </SubNavLink>
                        </ul>

                    </div>

                    <!-- Page Content -->
                    <main>
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>