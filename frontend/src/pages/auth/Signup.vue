<script setup>
import { ref } from 'vue'
import { LockClosedIcon } from '@heroicons/vue/24/solid'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import axiosClient from '../../axios.js'
import { useRouter } from 'vue-router'
import useUserStore from '../../store/user.js'

const router = useRouter()
const isLoading = ref(false)
const errorMessage = ref('')

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const togglePassword = () => {
    showPassword.value = !showPassword.value
}

const togglePasswordConfirmation = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value
}

async function submit() {
    if (isLoading.value) return

    try {
        isLoading.value = true
        errorMessage.value = ''
        
        // First get CSRF cookie
        await axiosClient.get('/sanctum/csrf-cookie')
        
        // Then attempt registration
        await axiosClient.post('/register', form.value)
        
        // If successful, log the user in
        const userStore = useUserStore()
        await userStore.fetchUser()
        await router.push({name: 'Home'})
        
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'An error occurred during registration'
        console.error('Registration error:', error.response?.data || error.message)
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
                    Create your account
                </h2>
            </div>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <!-- Error Message Display -->
                <div v-if="errorMessage" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm">
                    {{ errorMessage }}
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Full Name</label>
                    <div class="mt-1">
                        <input
                            id="name"
                            name="name"
                            type="text"
                            v-model="form.name"
                            required
                            class="pl-4 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            placeholder="John Doe"
                        />
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
                    <div class="mt-1">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            v-model="form.email"
                            required
                            class="pl-4 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            placeholder="name@company.com"
                        />
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
                            placeholder="••••••••"
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

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Confirm Password</label>
                    <div class="mt-1 relative">
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            required
                            class="pl-4 block w-full rounded-md border-0 py-1.5 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            @click="togglePasswordConfirmation"
                            class="absolute inset-y-0 right-0 flex items-center pr-3"
                        >
                            <EyeIcon v-if="showPasswordConfirmation" class="h-5 w-5 text-gray-500 hover:text-gray-600" />
                            <EyeSlashIcon v-else class="h-5 w-5 text-gray-500 hover:text-gray-600" />
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="isLoading"
                    class="flex w-full justify-center items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <LockClosedIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                    {{ isLoading ? 'Creating Account...' : 'Create Account' }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Already have an account?
                <router-link to="/login" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">
                    Sign in instead
                </router-link>
            </p>
        </div>
    </div>
</template>

<style scoped>

</style>
