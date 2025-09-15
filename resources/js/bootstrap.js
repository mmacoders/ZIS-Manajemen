import axios from 'axios';
window.axios = axios;

window.axios.defaults.baseURL = 'http://localhost:8000/api';
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';