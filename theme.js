// theme.js - Manejo de tema oscuro/claro

// Key para localStorage
const THEME_KEY = 'sistema_policial_theme';

// Temas disponibles
const THEMES = {
    DARK: 'dark',
    LIGHT: 'light'
};

// Función para aplicar el tema
function applyTheme(theme) {
    if (theme === THEMES.LIGHT) {
        document.body.classList.add('light-theme');
        document.documentElement.style.colorScheme = 'light';
    } else {
        document.body.classList.remove('light-theme');
        document.documentElement.style.colorScheme = 'dark';
    }
    
    // Actualizar icono del botón si existe
    const themeIcon = document.getElementById('themeIcon');
    if (themeIcon) {
        themeIcon.className = theme === THEMES.LIGHT ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    // Guardar en localStorage
    localStorage.setItem(THEME_KEY, theme);
}

// Función para obtener el tema actual
function getCurrentTheme() {
    const savedTheme = localStorage.getItem(THEME_KEY);
    if (savedTheme && (savedTheme === THEMES.DARK || savedTheme === THEMES.LIGHT)) {
        return savedTheme;
    }
    // Detectar preferencia del sistema
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return THEMES.DARK;
    }
    return THEMES.DARK; // Por defecto oscuro
}

// Función para toggle entre temas
function toggleTheme() {
    const currentTheme = getCurrentTheme();
    const newTheme = currentTheme === THEMES.DARK ? THEMES.LIGHT : THEMES.DARK;
    applyTheme(newTheme);
    
    // Mostrar notificación
    const message = newTheme === THEMES.LIGHT ? 'Modo claro activado' : 'Modo oscuro activado';
    if (typeof showToast === 'function') {
        showToast(message, 'info');
    } else {
        console.log(message);
    }
}

// Función para agregar botón de tema a todas las páginas
function addThemeButton() {
    const userInfoDiv = document.querySelector('.user-info');
    if (userInfoDiv && !document.getElementById('themeButton')) {
        const themeBtn = document.createElement('button');
        themeBtn.id = 'themeButton';
        themeBtn.className = 'theme-toggle';
        themeBtn.title = 'Cambiar tema (oscuro/claro)';
        themeBtn.onclick = toggleTheme;
        
        const currentTheme = getCurrentTheme();
        const icon = document.createElement('i');
        icon.id = 'themeIcon';
        icon.className = currentTheme === THEMES.LIGHT ? 'fas fa-sun' : 'fas fa-moon';
        themeBtn.appendChild(icon);
        
        // Insertar antes del botón de logout
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            userInfoDiv.insertBefore(themeBtn, logoutBtn);
        } else {
            userInfoDiv.appendChild(themeBtn);
        }
    }
}

// Inicializar tema al cargar la página
function initTheme() {
    const savedTheme = getCurrentTheme();
    applyTheme(savedTheme);
    addThemeButton();
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}