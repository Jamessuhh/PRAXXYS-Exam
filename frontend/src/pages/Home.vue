<script setup>
import { storeToRefs } from 'pinia'
import useUserStore from '../store/user'

const userStore = useUserStore()
const { user } = storeToRefs(userStore)

// Add these for demo purposes - you can replace with real data later
const stats = [
  { title: 'Total Users', value: '31K', desc: 'Jan 1st - Feb 1st', icon: 'users', color: 'text-base-content' },
  { title: 'New Orders', value: '4,200', desc: '↗︎ 400 (22%)', icon: 'shopping-cart', color: 'text-primary' },
  { title: 'Revenue', value: '$25,600', desc: '↗︎ 90 (14%)', icon: 'dollar-sign', color: 'text-success' },
  { title: 'Pending Tasks', value: '15', desc: '↘︎ 90 (14%)', icon: 'clock', color: 'text-warning' }
]
</script>

<template>
    <div class="p-4 sm:p-6 lg:p-8 bg-base-100">
        <!-- Dashboard Header - Added animation and improved styling -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl font-bold text-base-content">
                Dashboard
                <span class="text-primary">Overview</span>
            </h1>
            <p class="mt-2 text-base text-base-content/70">
                Welcome back, {{ user?.name || 'Guest' }}! Here's an overview of your statistics
            </p>
        </div>

        <!-- Stats Cards - Improved with icons and hover effects -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div v-for="(stat, index) in stats" :key="index"
                 class="stats bg-base-200 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <i :class="`fa-solid fa-${stat.icon} text-2xl`"></i>
                    </div>
                    <div class="stat-title text-base-content/70">{{ stat.title }}</div>
                    <div :class="`stat-value ${stat.color}`">{{ stat.value }}</div>
                    <div class="stat-desc text-base-content/60">{{ stat.desc }}</div>
                </div>
            </div>
        </div>

        <!-- Main Content Area - Added card hover effects and better spacing -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Activity -->
            <div class="lg:col-span-2">
                <div class="card bg-base-200 hover:shadow-lg transition-all duration-300">
                    <div class="card-body">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="card-title text-base-content">Recent Activity</h2>
                            <button class="btn btn-ghost btn-sm">View All</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th class="text-base-content/70">Name</th>
                                        <th class="text-base-content/70">Status</th>
                                        <th class="text-base-content/70">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="hover">
                                        <td>Project Alpha</td>
                                        <td><div class="badge badge-success gap-2">Completed</div></td>
                                        <td>2024-03-20</td>
                                    </tr>
                                    <tr class="hover">
                                        <td>Project Beta</td>
                                        <td><div class="badge badge-warning gap-2">In Progress</div></td>
                                        <td>2024-03-19</td>
                                    </tr>
                                    <tr class="hover">
                                        <td>Project Gamma</td>
                                        <td><div class="badge badge-error gap-2">Delayed</div></td>
                                        <td>2024-03-18</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions - Improved button styling -->
            <div class="lg:col-span-1">
                <div class="card bg-base-200 hover:shadow-lg transition-all duration-300">
                    <div class="card-body">
                        <h2 class="card-title text-base-content mb-4">Quick Actions</h2>
                        <div class="space-y-4">
                            <button class="btn btn-primary w-full hover:scale-105 transition-transform">
                                <i class="fa-solid fa-plus mr-2"></i> Create New Project
                            </button>
                            <button class="btn btn-secondary w-full hover:scale-105 transition-transform">
                                <i class="fa-solid fa-file-export mr-2"></i> Generate Report
                            </button>
                            <button class="btn w-full hover:scale-105 transition-transform">
                                <i class="fa-solid fa-chart-line mr-2"></i> View Analytics
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notifications - Added hover effects and improved styling -->
                <div class="card bg-base-200 mt-6 hover:shadow-lg transition-all duration-300">
                    <div class="card-body">
                        <h2 class="card-title text-base-content">Recent Notifications</h2>
                        <div class="space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-8">
                                        <span class="text-xs">UN</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium text-base-content">New user registered</p>
                                    <p class="text-sm text-base-content/60">2 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content rounded-full w-8">
                                        <span class="text-xs">UP</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium text-base-content">System update completed</p>
                                    <p class="text-sm text-base-content/60">5 hours ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
