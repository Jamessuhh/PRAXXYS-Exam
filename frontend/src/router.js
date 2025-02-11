import { createRouter, createWebHistory } from 'vue-router';
import DefaultLayout from './components/layouts/DefaultLayout.vue';
import Login from './pages/auth/Login.vue';
import Signup from './pages/auth/Signup.vue';
import Home from './pages/Home.vue';
import Products from './pages/Products.vue';
import NotFound from './pages/error/404.vue';
import useUserStore from './store/user';


const routes = [
    { 

        path: '/', 
        component: DefaultLayout,
        children: [
            { path: '/', name: 'Home', component: Home },
            { path: '/product', name: 'Products', component: Products },
        ],
        beforeEnter: async (to, from, next) => {
            try {
                const userStore = useUserStore();
                await userStore.fetchUser();
                next();
            } catch (error) {
                next({ name: 'Login' });
            }
        }
     },
     { 
        path: '/login', 
        name: 'Login', 
        component: Login 
    },
    { 
        path: '/signup', 
        name: 'Signup', 
        component: Signup 

    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: NotFound
    }
];


const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router