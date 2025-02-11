import axios from 'axios';
import router from './router.js';


const axiosClient = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    withCredentials: true,
    withXSRFToken: true,
});

// axiosClient.interceptors.request.use((config) => {
//     config.headers.Authorization = `Bearer ${localStorage.getItem('token')}`;
// });

axiosClient.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            router.push({name: 'Login'})
        }
        return Promise.reject(error)
    }
);

export default axiosClient;