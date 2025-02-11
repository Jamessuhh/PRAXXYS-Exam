<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { 
  Bars3Icon,
  BellIcon,
  HomeIcon,
  ShoppingBagIcon,
  ChartBarIcon,
  SunIcon,
  MoonIcon
} from '@heroicons/vue/24/outline'
import axiosClient from '../../axios.js'
import router from '../../router.js'
import useUserStore from '../../store/user'

const route = useRoute()

const isSidebarOpen = ref(true)
const isDarkMode = ref(localStorage.getItem('theme') === 'forest')
const userStore = useUserStore()

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value
  const theme = isDarkMode.value ? 'forest' : 'emerald'
  document.documentElement.setAttribute('data-theme', theme)
  localStorage.setItem('theme', theme)
}

// Initialize theme on component mount
const initTheme = () => {
  const savedTheme = localStorage.getItem('theme') || 'emerald'
  document.documentElement.setAttribute('data-theme', savedTheme)
}
initTheme()

function logout() {
  axiosClient.post('/logout')
  .then(response => {
    router.push({name: 'Login'})
  })
}
</script>

<template>
  <div class="min-h-screen bg-base-200 [transition:none]">
    <!-- Navbar -->
    <div class="navbar [transition:none] fixed w-full top-0 z-30">
      <div class="flex-none">
        <button class="btn btn-square btn-ghost" @click="toggleSidebar">
          <Bars3Icon class="w-6 h-6" />
        </button>
      </div>
      <div class="flex-1">
        <a class="btn btn-ghost text-xl">Admin</a>
      </div>
      <div class="flex-none">
        <button class="btn btn-ghost btn-circle" @click="toggleTheme">
          <SunIcon v-if="isDarkMode" class="h-5 w-5" />
          <MoonIcon v-else class="h-5 w-5" />
        </button>
        <!-- <button class="btn btn-ghost btn-circle">
          <div class="indicator">
            <BellIcon class="h-5 w-5" />
            <span class="badge badge-xs badge-primary indicator-item"></span>
          </div>
        </button> -->
        <div class="dropdown dropdown-end">
          <label tabindex="0" class="btn btn-ghost btn-circle avatar">
            <div class="w-10 rounded-full">
              <img :src="`https://ui-avatars.com/api/?name=${userStore.user?.name || 'U'}`" />
            </div>
          </label>
          <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
            <li><a>Profile</a></li>
            <li><a>Settings</a></li>
            <li><a @click="logout">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Main Content with Sidebar -->
    <div class="flex pt-16 min-h-screen">
      <!-- Sidebar -->
      <aside :class="`sticky top-16 backdrop-blur-md [transition:none] h-[calc(100vh-4rem)] overflow-y-auto transition-[transform] duration-300 z-20 ${
        isSidebarOpen ? 'w-64 translate-x-0' : 'w-16 -translate-x-full md:translate-x-0'
      }`">
        <ul class="menu w-full p-4">
          <li class="w-full">
            <router-link to="/" :class="[
              'w-full flex items-center px-2 py-2',
              route.path === '/' ? 'active bg-base-300 rounded-lg' : ''
            ]">
              <HomeIcon class="h-5 w-5 min-w-4 transition-transform duration-300 ease-in-out hover:scale-110" />
              <span :class="[
                'ml-2',
                isSidebarOpen ? 'block' : 'hidden'
              ]">Dashboard</span>
            </router-link>
          </li>
          <li class="w-full">
            <router-link to="/Product" :class="[
              'w-full flex items-center px-2 py-2',
              route.path === '/Product' ? 'active bg-base-300 rounded-lg' : ''
            ]">
              <ShoppingBagIcon class="h-5 w-5 min-w-4 transition-transform duration-300 ease-in-out hover:scale-110" />
              <span :class="[
                'ml-2',
                isSidebarOpen ? 'block' : 'hidden'
              ]">Products</span>
            </router-link>
          </li>
          <!-- <li class="w-full">
            <router-link to="/statistics" :class="[
              'w-full flex items-center px-2 py-2',
              route.path === '/statistics' ? 'active bg-base-300 rounded-lg' : ''
            ]">
              <ChartBarIcon class="h-5 w-5 min-w-5" />
              <span :class="[
                'ml-2',
                isSidebarOpen ? 'block' : 'hidden'
              ]">Statistics</span>
            </router-link>
          </li> -->
        </ul>
      </aside>

      <!-- Overlay for mobile when sidebar is open -->
      <div 
        v-if="isSidebarOpen" 
        @click="toggleSidebar"
        class="fixed inset-0 bg-opacity-20 backdrop-blur-sm md:hidden z-10"
      ></div>

      <!-- Main Content -->
      <main class="flex-1 p-4 relative">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<style scoped>
</style>
