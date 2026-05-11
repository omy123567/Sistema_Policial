// ==================== CONFIGURACIÓN GLOBAL ====================
const API_URL = 'backend/api.php';
const AUTH_URL = 'backend/auth.php';
let currentUser = null;
let currentPermissions = {};

// ==================== UTILIDADES ====================
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<div style="display: flex; justify-content: space-between; align-items: center;">
        <span>${message}</span>
        <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: inherit; cursor: pointer;">&times;</button>
    </div>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getToken() {
    return localStorage.getItem('jwt_token');
}

// ==================== VERIFICAR PERMISOS ====================
function esAdministrador() {
    const user = localStorage.getItem('current_user');
    if (!user) return false;
    const userData = JSON.parse(user);
    return (userData.rol_id === 1 || userData.rol === 'Administrador Central');
}

function puedeGestionarUsuarios() {
    return esAdministrador();
}

function puedeVerConfiguracion() {
    return esAdministrador();
}

// Aplicar permisos al menú de navegación
function aplicarPermisosMenu() {
    const user = localStorage.getItem('current_user');
    if (!user) return;
    
    const adminLinks = ['usuarios.html', 'configuracion.html'];
    const esAdmin = esAdministrador();
    
    adminLinks.forEach(link => {
        const elemento = document.querySelector(`a[href="${link}"]`);
        if (elemento) {
            if (!esAdmin) {
                elemento.style.display = 'none';
                const li = elemento.closest('li');
                if (li) li.style.display = 'none';
            } else {
                elemento.style.display = 'flex';
                const li = elemento.closest('li');
                if (li) li.style.display = 'block';
            }
        }
    });
}

// ==================== API REQUEST ====================
async function apiRequest(endpoint, method = 'GET', data = null) {
    const token = getToken();
    const headers = { 'Content-Type': 'application/json' };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    
    let url = `${API_URL}?endpoint=${endpoint}`;
    const options = { method, headers };
    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        const text = await response.text();
        
        let result;
        try {
            result = JSON.parse(text);
        } catch(e) {
            console.error('Error parsing JSON:', text.substring(0, 200));
            throw new Error('Respuesta inválida del servidor');
        }
        
        if (response.status === 401) {
            handleLogout();
            throw new Error('Sesión expirada');
        }
        if (response.status === 403) {
            showToast(result.error || 'No tiene permisos para esta acción', 'error');
            throw new Error('Permiso denegado');
        }
        return result;
    } catch (error) {
        console.error('API Error:', error);
        showToast('Error de conexión: ' + error.message, 'error');
        return method === 'GET' ? [] : { success: false, error: error.message };
    }
}

// ==================== AUTENTICACIÓN ====================
async function handleLogin(event) {
    if (event) event.preventDefault();
    
    const username = document.getElementById('username')?.value;
    const password = document.getElementById('password')?.value;
    
    if (!username || !password) {
        showToast('Ingrese usuario y contraseña', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`${AUTH_URL}?action=login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('jwt_token', data.token);
            localStorage.setItem('current_user', JSON.stringify(data.user));
            currentUser = data.user;
            currentPermissions = data.user.permisos || {};
            showToast(`Bienvenido ${data.user.nombre}`, 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 500);
        } else {
            showToast(data.error || 'Credenciales inválidas', 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showToast('Error de conexión', 'error');
    }
}

async function checkAuth() {
    const token = getToken();
    const user = localStorage.getItem('current_user');
    
    if (!token && !window.location.pathname.includes('index.html')) {
        window.location.href = 'index.html';
        return false;
    }
    
    if (user) {
        currentUser = JSON.parse(user);
        currentPermissions = currentUser.permisos || {};
        loadUserInfo();
        aplicarPermisosMenu();
        return true;
    }
    return false;
}

function handleLogout() {
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('current_user');
    currentUser = null;
    currentPermissions = {};
    window.location.href = 'index.html';
}

function loadUserInfo() {
    const userInfoEl = document.getElementById('userInfo');
    if (userInfoEl && currentUser) {
        userInfoEl.innerHTML = `<span>${escapeHtml(currentUser.nombre)}</span><span class="badge badge-info">${escapeHtml(currentUser.rol)}</span>`;
    }
}

// ==================== FUNCIONES GENERALES ====================
function closeModal() {
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    if (input) input.type = input.type === 'password' ? 'text' : 'password';
}

function toggleMobileMenu() {
    document.querySelector('.nav-menu')?.classList.toggle('active');
}

function calcularEdad(fechaNacimiento) {
    if (!fechaNacimiento) return '-';
    const hoy = new Date();
    const nac = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nac.getFullYear();
    const m = hoy.getMonth() - nac.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;
    return edad;
}

// ==================== TEMA ====================
const THEME_KEY = 'sistema_policial_theme';

function applyTheme(theme) {
    if (theme === 'light') {
        document.body.classList.add('light-theme');
        const icon = document.getElementById('themeIcon');
        if (icon) icon.className = 'fas fa-sun';
    } else {
        document.body.classList.remove('light-theme');
        const icon = document.getElementById('themeIcon');
        if (icon) icon.className = 'fas fa-moon';
    }
    localStorage.setItem(THEME_KEY, theme);
}

function getCurrentTheme() {
    const saved = localStorage.getItem(THEME_KEY);
    if (saved === 'light' || saved === 'dark') return saved;
    return 'dark';
}

function initTheme() {
    const savedTheme = getCurrentTheme();
    applyTheme(savedTheme);
    const toggleBtn = document.getElementById('themeToggleBtn');
    if (toggleBtn) toggleBtn.addEventListener('click', () => {
        const newTheme = getCurrentTheme() === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
        showToast(`Tema cambiado a ${newTheme === 'light' ? 'claro' : 'oscuro'}`, 'info');
    });
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', async () => {
    console.log('🚀 Sistema iniciado');
    
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) loginForm.addEventListener('submit', handleLogin);
    
    // Logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) logoutBtn.addEventListener('click', handleLogout);
    
    // Mobile menu
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) hamburger.addEventListener('click', toggleMobileMenu);
    
    // Close modals
    document.querySelectorAll('.close-modal').forEach(btn => btn.addEventListener('click', closeModal));
    window.addEventListener('click', (e) => {
        if (e.target.classList?.contains('modal')) closeModal();
    });
    
    // Theme
    initTheme();
    
    // Verificar autenticación (excepto en login)
    if (!window.location.pathname.includes('index.html')) {
        await checkAuth();
    }
});

// Exponer funciones globales
window.handleLogin = handleLogin;
window.handleLogout = handleLogout;
window.togglePasswordVisibility = togglePasswordVisibility;
window.toggleMobileMenu = toggleMobileMenu;
window.closeModal = closeModal;
window.apiRequest = apiRequest;
window.showToast = showToast;
window.escapeHtml = escapeHtml;
window.getToken = getToken;
window.calcularEdad = calcularEdad;
window.esAdministrador = esAdministrador;
window.puedeGestionarUsuarios = puedeGestionarUsuarios;
window.aplicarPermisosMenu = aplicarPermisosMenu;