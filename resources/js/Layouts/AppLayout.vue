<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Banner from '@/Components/Banner.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import SubNavLink from '@/Components/SubNavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { RectangleGroupIcon, ChevronDownIcon, ChevronUpIcon, ArrowDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    page: Object,
    page_options: {
        type: Object,
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
                class="hidden md:block md:w-1/4 lg:1/5 xl:1/6 min-h-screen relative bg-white text-black shadow-xl">
                <div class="w-full flex-col-config">
                    <!-- Logo -->
                    <div class="p-10 bg-slate-100">
                        <img class="hidden xl:block" src="/storage/img/theme/logos/logo-name.png" />
                        <img class="xl:hidden" src="/storage/img/theme/logos/complete_logo.svg" />
                    </div>
                    <ul class="w-full">
                        <SubNavLink :href="route(page.type + '.index')" :active="route().current(page.type + '.index')"
                            class="w-full hover:bg-gray-100 py-5"
                            :class="{ 'bg-gray-200': route().current(page.type + '.index') }">
                            <li class="text-gray-600 flex flex-row justify-center items-center align-middle md:ml-10
                            ">
                                <span class="inline-block mx-2">
                                    <RectangleGroupIcon class="w-5 h-5" />
                                </span>
                                <span class="hidden md:inline-block">Todos</span>
                            </li>
                        </SubNavLink>
                        <SubNavLink v-for="option in page_options" :href="route(page.type + '.filter', option.id)"
                            :active="route().current(page.type + '.filter', option.id)"
                            class="w-full hover:bg-gray-100 py-5"
                            :class="{ 'bg-gray-200': route().current(page.type + '.filter', option.id) }">
                            <li class="text-gray-600 flex flex-row justify-center items-center align-middle md:ml-10
                            ">
                                <span class="inline-block mx-2">
                                    <RectangleGroupIcon class="w-5 h-5" />
                                </span>
                                <span class="hidden md:inline-block">{{ option.name }}</span>
                            </li>
                        </SubNavLink>
                    </ul>
                    <Dropdown />
                </div>
            </div>
            <div class="min-h-screen"
                :class="{ 'w-full': !page_options, 'w-full md:w-3/4 lg:4/5 xl:w-5/6': page_options }">

                <Head :title="page ? page.name : 'Nada'" />

                <Banner />

                <div class="min-h-screen bg-gray-100">
                    <nav class="bg-white border-b border-gray-100 print:hidden">
                        <!-- Primary Navigation Menu -->
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            <div class="flex justify-between h-16">
                                <div class="flex">

                                    <div class="h-full flex items-center">
                                        <img v-if="!page_options" src="/storage/img/theme/logos/logo-name.png"
                                            class="h-[40px]" />
                                    </div>

                                    <!-- Navigation Links -->
                                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 lg:flex">
                                        <NavLink :href="route('dashboard.index')"
                                            :active="route().current('home') || route().current('dashboard.*')">
                                            <span class="inline-block mx-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                                </svg>
                                            </span>
                                            <span class="inline-block">Dashboard</span>
                                        </NavLink>

                                        <NavLink :has_dropdown="true" :href="'#'"
                                            :active="route().current('warehouse.*')">
                                            <template #trigger>
                                                <span class="inline-block mx-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                                    </svg>
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
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                                    </svg>
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
                                        <NavLink :href="route('payments.index')"
                                            :active="route().current('payments.*')">
                                            <span class="inline-block mx-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                                </svg>
                                            </span>
                                            <span class="inline-block">Pagamentos</span>
                                        </NavLink>
                                        <NavLink :href="route('services.index')"
                                            :active="route().current('services.*')">
                                            <span class="inline-block mx-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                                </svg>
                                            </span>
                                            <span class="inline-block">Serviços</span>
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
                                                    Manage Account
                                                </div>

                                                <DropdownLink :href="route('profile.show')">
                                                    Perfil
                                                </DropdownLink>

                                                <DropdownLink v-if="$page.props.jetstream.hasApiFeatures"
                                                    :href="route('api-tokens.index')">
                                                    API Tokens
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
                            class="lg:hidden">
                            <div class="pt-2 pb-3 space-y-1">
                                <ResponsiveNavLink :href="route('dashboard.index')"
                                    :active="route().current('dashboard')">
                                    Dashboard
                                </ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('warehouse.index')"
                                    :active="route().current('warehouse')">
                                    Almoxarifado
                                </ResponsiveNavLink>
                            </div>

                            <!-- Responsive Settings Options -->
                            <div class="pt-4 pb-1 border-t border-gray-200">
                                <div class="flex items-center px-4">
                                    <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 mr-3">
                                        <img class="h-10 w-10 rounded-full object-cover"
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
                                        API Tokens
                                    </ResponsiveNavLink>

                                    <!-- Authentication -->
                                    <form method="POST" @submit.prevent="logout">
                                        <ResponsiveNavLink as="button">
                                            Log Out
                                        </ResponsiveNavLink>
                                    </form>

                                    <!-- Team Management -->
                                    <template v-if="$page.props.jetstream.hasTeamFeatures">
                                        <div class="border-t border-gray-200" />

                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Team
                                        </div>

                                        <!-- Team Settings -->
                                        <ResponsiveNavLink
                                            :href="route('teams.show', $page.props.auth.user.current_team)"
                                            :active="route().current('teams.show')">
                                            Team Settings
                                        </ResponsiveNavLink>

                                        <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams"
                                            :href="route('teams.create')" :active="route().current('teams.create')">
                                            Create New Team
                                        </ResponsiveNavLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Team Switcher -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Switch Teams
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
                    <header v-if="$slots.header" class="bg-white shadow border-b md:border-none">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            <slot name="header" />
                        </div>
                    </header>


                    <div v-if="page_options"
                        class="md:hidden bg-white shadow border-b md:border-none text-gray-700 select-none cursor-pointer"
                        title="Clique para ver as opções da página" @click="options_collapse = !options_collapse">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-row space-x-3 items-center">
                            <p>Opções da Página</p>
                            <ChevronUpIcon v-if="options_collapse" class="w-5 h-5" />
                            <ChevronDownIcon v-else class="w-5 h-5" />
                        </div>

                        <ul v-if="options_collapse" class="w-full">
                            <SubNavLink :href="route(page.type + '.index')"
                                :active="route().current(page.type + '.index')" class="w-full hover:bg-gray-100 py-5"
                                :class="{ 'bg-gray-200': route().current(page.type + '.index') }">
                                <li class="text-gray-600 flex items-center
                            ">
                                    <span class="inline-block mx-2">
                                        <RectangleGroupIcon class="w-5 h-5" />
                                    </span>
                                    <span>Todos</span>
                                </li>
                            </SubNavLink>
                            <SubNavLink v-for="option in page_options" :href="route(page.type + '.filter', option.id)"
                                :active="route().current(page.type + '.filter', option.id)"
                                class="w-full hover:bg-gray-100 py-5"
                                :class="{ 'bg-gray-200': route().current(page.type + '.filter', option.id) }">
                                <li class="text-gray-600 flex items-center
                            ">
                                    <span class="inline-block mx-2">
                                        <RectangleGroupIcon class="w-5 h-5" />
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