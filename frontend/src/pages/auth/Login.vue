<script setup>
import { ref } from 'vue'
import { LockClosedIcon } from '@heroicons/vue/24/solid'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import axiosClient from '../../axios.js'
import router from '../../router.js'
import useUserStore from '../../store/user.js'

const form = ref({
    login: '',
    password: '',
    remember: false
})

const showPassword = ref(false)
const errorMessage = ref('')
const isLoading = ref(false)

const togglePassword = () => {
    showPassword.value = !showPassword.value
}

async function submit() {
    if (isLoading.value) return
    
    try {
        isLoading.value = true
        errorMessage.value = ''
        await axiosClient.get('/sanctum/csrf-cookie');
        
        const loginData = {
            login: form.value.login,
            password: form.value.password,
            remember: form.value.remember
        }
        
        const { data } = await axiosClient.post('/login', loginData);
        
        const userStore = useUserStore();
        await userStore.fetchUser();
        await router.push({name: 'Home'});
        
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'An error occurred during login'
        console.error('Login error:', error.response?.data || error.message);
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
        <div class="min-h-screen flex items-center justify-center">
            <div class="bg-white p-8 shadow-xl rounded-xl max-w-md w-full mx-auto">
                <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                    <h2 class="mt-4 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
                        Sign in to your account
                    </h2>
                </div>

                <form class="mt-6 space-y-5" @submit.prevent="submit" @keyup.enter="submit">
                    <div>
                        <label for="login" class="block text-sm font-medium leading-6 text-gray-900">Email or Username</label>
                        <div class="mt-1">
                            <input
                                id="login"
                                name="login"
                                type="text"
                                v-model="form.login"
                                required
                                class="pl-4 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                placeholder="Enter email or username"
                            />
                            <span v-if="errorMessage" class="mt-2 text-sm text-red-600">
                                {{ errorMessage }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                        <div class="mt-1 relative">
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                class="pl-4 block w-full rounded-md border-0 py-1.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                placeholder="Password"
                            />
                            <button
                                type="button"
                                @click="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3"
                            >
                                <EyeIcon v-if="showPassword" class="h-5 w-5 text-gray-500 hover:text-gray-600" />
                                <EyeSlashIcon v-else class="h-5 w-5 text-gray-500 hover:text-gray-600" />
                            </button>
                        </div>
                        
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                id="remember-me"
                                name="remember-me"
                                type="checkbox"
                                v-model="form.remember"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                            />
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                        </div>

                        <div class="text-sm">
                            <!-- <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">
                                Forgot password?
                            </a> -->
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="flex w-full justify-center items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <LockClosedIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                        {{ isLoading ? 'Signing in...' : 'Sign in' }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-500">
                    Not a member?
                    <router-link to="/signup" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">
                        Create an account
                    </router-link>
                </p>

            </div>
        </div>
</template>

<style scoped>

</style>
