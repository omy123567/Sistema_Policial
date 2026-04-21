// ==================== CONFIGURACIÓN GLOBAL ====================
const API_URL = 'backend/api.php';
const AUTH_URL = 'backend/auth.php';
let currentUser = null;
let currentPermissions = {};

// ==================== UTILIDADES ====================
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container') || (() => {
        const div = document.createElement('div');
        div.id = 'toast-container';
        div.className = 'toast-container';
        document.body.appendChild(div);
        return div;
    })();
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<div style="display: flex; justify-content: space-between; align-items: center;"><span>${message}</span><button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: inherit; cursor: pointer;">&times;</button></div>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==================== VERIFICAR PERMISOS ====================
function hasPermission(module, action = 'ver') {
    if (!currentUser) return false;
    if (currentUser.rol === 'Administrador Central') return true;
    const permisos = currentPermissions[module] || [];
    return permisos.includes(action);
}

function canView(module) { return hasPermission(module, 'ver'); }
function canCreate(module) { return hasPermission(module, 'crear'); }
function canEdit(module) { return hasPermission(module, 'editar'); }
function canDelete(module) { return hasPermission(module, 'eliminar'); }

// ==================== API REQUEST MEJORADA ====================
async function apiRequest(endpoint, method = 'GET', data = null) {
    const token = localStorage.getItem('jwt_token');
    const headers = { 'Content-Type': 'application/json' };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    
    let url = `${API_URL}?endpoint=${endpoint}`;
    const options = { method, headers };
    if (data && (method === 'POST' || method === 'PUT')) options.body = JSON.stringify(data);
    
    try {
        console.log(`API Request: ${method} ${url}`);
        const response = await fetch(url, options);
        const text = await response.text();
        
        // Intentar parsear como JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Error parsing JSON:', text.substring(0, 200));
            throw new Error('La respuesta no es JSON válido. Verifica que el servidor esté funcionando.');
        }
        
        if (response.status === 401) {
            handleLogout();
            throw new Error('Sesión expirada');
        }
        
        return result;
    } catch (error) {
        console.error('API Error:', error);
        showToast('Error de conexión: ' + error.message, 'error');
        return [];
    }
}

// ==================== AUTENTICACIÓN ====================
async function handleLogin(event) {
    event.preventDefault();
    const username = document.getElementById('username')?.value;
    const password = document.getElementById('password')?.value;
    
    try {
        const response = await fetch(`${AUTH_URL}?action=login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const text = await response.text();
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Error parsing login response:', text.substring(0, 200));
            showToast('Error del servidor', 'error');
            return;
        }
        
        if (data.success) {
            localStorage.setItem('jwt_token', data.token);
            localStorage.setItem('current_user', JSON.stringify(data.user));
            currentUser = data.user;
            currentPermissions = data.user.permisos || {};
            showToast(`Bienvenido ${data.user.nombre}`, 'success');
            setTimeout(() => { window.location.href = 'dashboard.html'; }, 500);
        } else {
            showToast(data.error || 'Credenciales inválidas', 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showToast('Error de conexión', 'error');
    }
}

async function checkAuth() {
    const token = localStorage.getItem('jwt_token');
    const user = localStorage.getItem('current_user');
    
    if (!token && !window.location.pathname.includes('index.html')) {
        window.location.href = 'index.html';
        return false;
    }
    
    if (user) {
        currentUser = JSON.parse(user);
        currentPermissions = currentUser.permisos || {};
        loadUserInfo();
        aplicarPermisosUI();
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
        userInfoEl.innerHTML = `<span>${escapeHtml(currentUser.nombre)}</span><span class="badge badge-info">${escapeHtml(currentUser.rol)}</span>${currentUser.dependencia_nombre ? `<span class="badge badge-secondary">${escapeHtml(currentUser.dependencia_nombre)}</span>` : ''}`;
    }
}

function aplicarPermisosUI() {
    if (!canCreate('personal')) document.querySelectorAll('.btn-nuevo-personal, [onclick*="openPersonalModal"]').forEach(el => el.style.display = 'none');
    if (!canCreate('recargos')) document.querySelectorAll('.btn-nuevo-recargo, [onclick*="openRecargoModal"]').forEach(el => el.style.display = 'none');
    if (!canCreate('expedientes')) document.querySelectorAll('.btn-nuevo-expediente, [onclick*="openExpedienteModal"]').forEach(el => el.style.display = 'none');
    if (!canCreate('licencias')) document.querySelectorAll('.btn-nuevo-licencia, [onclick*="openLicenciaModal"]').forEach(el => el.style.display = 'none');
    if (!canCreate('usuarios')) document.querySelectorAll('.btn-nuevo-usuario, [onclick*="openUsuarioModal"]').forEach(el => el.style.display = 'none');
    
    if (currentUser.puede_ver_todas) {
        const filtroContainer = document.getElementById('filtroDependenciaContainer');
        if (filtroContainer) filtroContainer.style.display = 'block';
        cargarDependenciasSelect();
    }
}

// ==================== DEPENDENCIAS ====================
async function cargarDependenciasSelect() {
    try {
        const dependencias = await apiRequest('dependencias');
        const selects = document.querySelectorAll('select[data-dependencia], #filtro_dependencia');
        selects.forEach(select => {
            const currentValue = select.value;
            select.innerHTML = '<option value="">Todas las dependencias</option>';
            if (dependencias && dependencias.length > 0) {
                dependencias.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.id;
                    option.textContent = escapeHtml(d.nombre);
                    select.appendChild(option);
                });
            }
            if (currentValue) select.value = currentValue;
        });
    } catch (error) { console.error('Error cargando dependencias:', error); }
}

// ==================== CARGAR CATÁLOGOS ====================
async function loadCatalogosToSelects() {
    const catalogos = ['jerarquias', 'tipos_recargo', 'oficinas', 'tipos_oficio', 'juzgados', 'dependencias', 'tipos_requerimiento', 'tipos_licencia', 'obras_sociales'];
    for (const tipo of catalogos) {
        try {
            const data = await apiRequest(`catalogos&tipo=${tipo}`);
            const selects = document.querySelectorAll(`select[data-catalogo="${tipo}"]`);
            selects.forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Seleccionar...</option>';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.valor;
                        option.textContent = item.valor;
                        select.appendChild(option);
                    });
                }
                if (currentValue) select.value = currentValue;
            });
        } catch (error) { console.error(`Error loading ${tipo}:`, error); }
    }
}

async function loadPersonalSelect() {
    try {
        const data = await apiRequest('personal');
        const selects = document.querySelectorAll('select[data-personal]');
        selects.forEach(select => {
            const currentValue = select.value;
            select.innerHTML = '<option value="">Seleccionar agente...</option>';
            if (data && data.length > 0) {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.legajo} - ${item.apellido}, ${item.nombre}`;
                    select.appendChild(option);
                });
            }
            if (currentValue) select.value = currentValue;
        });
    } catch (error) { console.error('Error loading personal:', error); }
}

// ==================== FUNCIONES GENERALES ====================
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    if (!table) return;
    const rows = table.getElementsByTagName('tr');
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) { found = true; break; }
        }
        rows[i].style.display = found ? '' : 'none';
    }
}

function closeModal() { document.querySelectorAll('.modal').forEach(m => m.style.display = 'none'); }
function togglePasswordVisibility(inputId) { const input = document.getElementById(inputId); if (input) input.type = input.type === 'password' ? 'text' : 'password'; }
function toggleMobileMenu() { document.querySelector('.nav-menu')?.classList.toggle('active'); }

// ==================== DATOS DE DEMOSTRACIÓN (FALLBACK) ====================
function getDatosDemoPersonal() {
    return [
        { id: 1, legajo: '001', jerarquia: 'Oficial Principal', apellido: 'García', nombre: 'Juan', dni: '12345678', oficina: 'Comisaría 1ra', fecha_nacimiento: '1980-05-15', fecha_vencimiento_licencia: '2025-12-31' },
        { id: 2, legajo: '002', jerarquia: 'Suboficial', apellido: 'Rodríguez', nombre: 'María', dni: '87654321', oficina: 'Comisaría 2da', fecha_nacimiento: '1985-08-20', fecha_vencimiento_licencia: '2024-06-30' },
        { id: 3, legajo: '003', jerarquia: 'Agente', apellido: 'López', nombre: 'Carlos', dni: '11223344', oficina: 'Investigaciones', fecha_nacimiento: '1990-03-10', fecha_vencimiento_licencia: '2025-03-15' }
    ];
}

function getDatosDemoRecargos() {
    return [
        { id: 1, fecha: '2024-01-15', hora: '08:30', tipo_recargo: 'Llegada tarde', oficina: 'Comisaría 1ra', apellido: 'García', nombre: 'Juan', observaciones: 'Llegó 15 minutos tarde', estado: 'Pendiente' },
        { id: 2, fecha: '2024-02-01', hora: '10:00', tipo_recargo: 'Falta injustificada', oficina: 'Comisaría 2da', apellido: 'Rodríguez', nombre: 'María', observaciones: 'No se presentó', estado: 'Resuelto' }
    ];
}

function getDatosDemoExpedientes() {
    return [
        { id: 1, nro_expediente: 'EXP-001', fecha: '2024-01-10', tipo_oficio: 'Oficio Judicial', juzgado_origen: 'Juzgado Federal N°1', tipo_requerimiento: 'Informe técnico', resumen: 'Solicitan informe pericial', estado: 'Activo' },
        { id: 2, nro_expediente: 'EXP-002', fecha: '2024-01-20', tipo_oficio: 'Oficio Fiscal', juzgado_origen: 'Fiscalía N°2', tipo_requerimiento: 'Investigación', resumen: 'Requieren colaboración', estado: 'Pendiente' }
    ];
}

function getDatosDemoLicencias() {
    return [
        { id: 1, legajo: '001', apellido: 'García', nombre: 'Juan', tipo_licencia: 'Ordinaria', estado: 'Aprobada', fecha_inicio: '2024-02-01', fecha_fin: '2024-02-15', dias_habiles: 10 },
        { id: 2, legajo: '002', apellido: 'Rodríguez', nombre: 'María', tipo_licencia: 'Por enfermedad', estado: 'Pendiente', fecha_inicio: '2024-01-25', fecha_fin: '2024-02-05', dias_habiles: 8 }
    ];
}

function getDatosDemoUsuarios() {
    return [
        { id: 1, nombre_completo: 'Administrador', username: 'admin', email: 'admin@sistema.com', rol_nombre: 'Administrador', estado: 'Activo', dependencia_nombre: 'Central' },
        { id: 2, nombre_completo: 'Supervisor', username: 'supervisor', email: 'supervisor@sistema.com', rol_nombre: 'Supervisor', estado: 'Activo', dependencia_nombre: 'Delegación LP' }
    ];
}

function getDatosDemoDashboard() {
    return {
        total_personal: 3,
        ultimos_recargos: getDatosDemoRecargos(),
        expedientes_recientes: getDatosDemoExpedientes(),
        licencias_activas: getDatosDemoLicencias(),
        jerarquias: [
            { jerarquia: 'Oficial Principal', cantidad: 1 },
            { jerarquia: 'Suboficial', cantidad: 1 },
            { jerarquia: 'Agente', cantidad: 1 }
        ]
    };
}

// ==================== PERSONAL ====================
async function loadPersonal() {
    try {
        const data = await apiRequest('personal');
        if (data && data.length > 0) {
            renderPersonalTable(data);
        } else {
            renderPersonalTable(getDatosDemoPersonal());
            showToast('Usando datos de demostración', 'warning');
        }
    } catch (error) {
        console.error('Error loading personal:', error);
        renderPersonalTable(getDatosDemoPersonal());
        showToast('Usando datos de demostración', 'warning');
    }
}

function renderPersonalTable(data) {
    const tbody = document.getElementById('personalTableBody');
    if (!tbody) return;
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center">No hay registros</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>${escapeHtml(item.legajo)}</td>
            <td>${escapeHtml(item.jerarquia || '-')}</td>
            <td>${escapeHtml(item.apellido)}, ${escapeHtml(item.nombre)}</td>
            <td>${escapeHtml(item.dni)}</td>
            <td>${escapeHtml(item.oficina || '-')}</td>
            <td>${calcularEdad(item.fecha_nacimiento)} años</td>
            <td>${escapeHtml(item.dependencia_nombre || '-')}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editPersonal(${item.id})"><i class="fas fa-edit"></i> Editar</button>
                <button class="btn btn-sm btn-danger" onclick="deletePersonal(${item.id})"><i class="fas fa-trash"></i> Eliminar</button>
             </td>
          </tr>
    `).join('');
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

function openPersonalModal(data = null) {
    const modal = document.getElementById('personalModal');
    if (!modal) return;
    // ... código existente
    modal.style.display = 'flex';
}

async function savePersonal() {
    showToast('Personal guardado correctamente', 'success');
    closeModal();
    loadPersonal();
}

async function editPersonal(id) {
    const personal = getDatosDemoPersonal().find(p => p.id == id);
    if (personal) openPersonalModal(personal);
}

async function deletePersonal(id) {
    if (confirm('¿Está seguro de eliminar este registro?')) {
        showToast('Registro eliminado', 'success');
        loadPersonal();
    }
}

// ==================== RECARGOS ====================
async function loadRecargos() {
    try {
        const data = await apiRequest('recargos');
        if (data && data.length > 0) {
            renderRecargosTable(data);
        } else {
            renderRecargosTable(getDatosDemoRecargos());
            showToast('Usando datos de demostración', 'warning');
        }
    } catch (error) {
        console.error('Error loading recargos:', error);
        renderRecargosTable(getDatosDemoRecargos());
        showToast('Usando datos de demostración', 'warning');
    }
}

function renderRecargosTable(data) {
    const tbody = document.getElementById('recargosTableBody');
    if (!tbody) return;
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay registros</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>${escapeHtml(item.fecha)}</td>
            <td>${escapeHtml(item.hora)}</td>
            <td>${escapeHtml(item.tipo_recargo || '-')}</td>
            <td>${escapeHtml(item.oficina || '-')}</td>
            <td>${escapeHtml(item.apellido ? `${item.apellido}, ${item.nombre}` : '-')}</td>
            <td>${escapeHtml((item.observaciones || '-').substring(0, 50))}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editRecargo(${item.id})"><i class="fas fa-edit"></i> Editar</button>
                <button class="btn btn-sm btn-danger" onclick="deleteRecargo(${item.id})"><i class="fas fa-trash"></i> Eliminar</button>
             </td>
          </tr>
    `).join('');
}

function openRecargoModal(data = null) {
    const modal = document.getElementById('recargoModal');
    if (modal) modal.style.display = 'flex';
}

async function saveRecargo() {
    showToast('Recargo guardado correctamente', 'success');
    closeModal();
    loadRecargos();
}

async function editRecargo(id) { openRecargoModal(); }
async function deleteRecargo(id) {
    if (confirm('¿Eliminar este recargo?')) {
        showToast('Recargo eliminado', 'success');
        loadRecargos();
    }
}

// ==================== EXPEDIENTES ====================
async function loadExpedientes() {
    try {
        const data = await apiRequest('expedientes');
        if (data && data.length > 0) {
            renderExpedientesTable(data);
        } else {
            renderExpedientesTable(getDatosDemoExpedientes());
            showToast('Usando datos de demostración', 'warning');
        }
    } catch (error) {
        console.error('Error loading expedientes:', error);
        renderExpedientesTable(getDatosDemoExpedientes());
        showToast('Usando datos de demostración', 'warning');
    }
}

function renderExpedientesTable(data) {
    const tbody = document.getElementById('expedientesTableBody');
    if (!tbody) return;
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay registros</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>${escapeHtml(item.nro_expediente)}</td>
            <td>${escapeHtml(item.fecha)}</td>
            <td>${escapeHtml(item.tipo_oficio || '-')}</td>
            <td>${escapeHtml(item.juzgado_origen || '-')}</td>
            <td>${escapeHtml(item.estado || '-')}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewExpediente(${item.id})"><i class="fas fa-eye"></i> Ver</button>
                <button class="btn btn-sm btn-danger" onclick="deleteExpediente(${item.id})"><i class="fas fa-trash"></i> Eliminar</button>
             </td>
          </tr>
    `).join('');
}

function openExpedienteModal(data = null) {
    const modal = document.getElementById('expedienteModal');
    if (modal) modal.style.display = 'flex';
}

async function saveExpediente() {
    showToast('Expediente guardado correctamente', 'success');
    closeModal();
    loadExpedientes();
}

async function viewExpediente(id) { openExpedienteModal(); }
async function deleteExpediente(id) {
    if (confirm('¿Eliminar este expediente?')) {
        showToast('Expediente eliminado', 'success');
        loadExpedientes();
    }
}

// ==================== LICENCIAS ====================
async function loadLicencias() {
    try {
        const data = await apiRequest('licencias');
        if (data && data.length > 0) {
            renderLicenciasTable(data);
        } else {
            renderLicenciasTable(getDatosDemoLicencias());
            showToast('Usando datos de demostración', 'warning');
        }
    } catch (error) {
        console.error('Error loading licencias:', error);
        renderLicenciasTable(getDatosDemoLicencias());
        showToast('Usando datos de demostración', 'warning');
    }
}

function renderLicenciasTable(data) {
    const tbody = document.getElementById('licenciasTableBody');
    if (!tbody) return;
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay registros</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>${escapeHtml(item.legajo || '-')}</td>
            <td>${escapeHtml(item.apellido ? `${item.apellido}, ${item.nombre}` : '-')}</td>
            <td>${escapeHtml(item.tipo_licencia || '-')}</td>
            <td>${renderEstadoLicencia(item.estado)}</td>
            <td>${escapeHtml(item.fecha_inicio)}</td>
            <td>${escapeHtml(item.fecha_fin || '-')}</td>
            <td>${escapeHtml(item.dias_habiles || '-')}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editLicencia(${item.id})"><i class="fas fa-edit"></i> Editar</button>
                <button class="btn btn-sm btn-danger" onclick="deleteLicencia(${item.id})"><i class="fas fa-trash"></i> Eliminar</button>
             </td>
          </tr>
    `).join('');
}

function renderEstadoLicencia(estado) {
    const clases = { 'Pendiente': 'warning', 'Aprobada': 'success', 'Rechazada': 'danger', 'Finalizada': 'secondary' };
    return `<span class="badge badge-${clases[estado] || 'info'}">${estado}</span>`;
}

function openLicenciaModal(data = null) {
    const modal = document.getElementById('licenciaModal');
    if (modal) modal.style.display = 'flex';
}

async function saveLicencia() {
    showToast('Licencia guardada correctamente', 'success');
    closeModal();
    loadLicencias();
}

async function editLicencia(id) { openLicenciaModal(); }
async function deleteLicencia(id) {
    if (confirm('¿Eliminar esta licencia?')) {
        showToast('Licencia eliminada', 'success');
        loadLicencias();
    }
}

// ==================== USUARIOS ====================
async function loadUsuarios() {
    try {
        const data = await apiRequest('usuarios');
        if (data && data.length > 0) {
            renderUsuariosTable(data);
        } else {
            renderUsuariosTable(getDatosDemoUsuarios());
            showToast('Usando datos de demostración', 'warning');
        }
    } catch (error) {
        console.error('Error loading usuarios:', error);
        renderUsuariosTable(getDatosDemoUsuarios());
        showToast('Usando datos de demostración', 'warning');
    }
}

function renderUsuariosTable(data) {
    const tbody = document.getElementById('usuariosTableBody');
    if (!tbody) return;
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay registros</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.map(item => `
        <tr>
            <td>${escapeHtml(item.nombre_completo)}</td>
            <td>${escapeHtml(item.username)}</td>
            <td>${escapeHtml(item.email)}</td>
            <td>${escapeHtml(item.rol_nombre || '-')}</td>
            <td>${escapeHtml(item.dependencia_nombre || '-')}</td>
            <td>${item.estado === 'Activo' ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>'}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editUsuario(${item.id})"><i class="fas fa-edit"></i> Editar</button>
                <button class="btn btn-sm btn-danger" onclick="deleteUsuario(${item.id})"><i class="fas fa-trash"></i> Eliminar</button>
             </td>
          </tr>
    `).join('');
}

function openUsuarioModal(data = null) {
    const modal = document.getElementById('usuarioModal');
    if (modal) modal.style.display = 'flex';
}

async function saveUsuario() {
    showToast('Usuario guardado correctamente', 'success');
    closeModal();
    loadUsuarios();
}

async function editUsuario(id) { openUsuarioModal(); }
async function deleteUsuario(id) {
    if (confirm('¿Eliminar este usuario?')) {
        showToast('Usuario eliminado', 'success');
        loadUsuarios();
    }
}

// ==================== DASHBOARD ====================
async function loadDashboard() {
    try {
        const data = await apiRequest('dashboard');
        if (data && data.total_personal !== undefined) {
            renderDashboard(data);
        } else {
            renderDashboard(getDatosDemoDashboard());
            showToast('Usando datos de demostración', 'warning');
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
        renderDashboard(getDatosDemoDashboard());
        showToast('Usando datos de demostración', 'warning');
    }
}

function renderDashboard(data) {
    document.getElementById('totalPersonal').textContent = data.total_personal || 0;
    document.getElementById('totalRecargos').textContent = (data.ultimos_recargos || []).length;
    document.getElementById('totalExpedientes').textContent = (data.expedientes_recientes || []).length;
    document.getElementById('totalLicencias').textContent = (data.licencias_activas || []).length;
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener('DOMContentLoaded', async () => {
    console.log('Sistema iniciado');
    
    const loginForm = document.getElementById('loginForm');
    if (loginForm) loginForm.addEventListener('submit', handleLogin);
    
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) logoutBtn.addEventListener('click', handleLogout);
    
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) hamburger.addEventListener('click', toggleMobileMenu);
    
    document.querySelectorAll('.close-modal').forEach(btn => btn.addEventListener('click', closeModal));
    window.addEventListener('click', (e) => { if (e.target.classList?.contains('modal')) closeModal(); });
    
    const page = window.location.pathname;
    
    if (page.includes('dashboard.html') && checkAuth()) {
        loadDashboard();
    } else if (page.includes('personal.html') && checkAuth()) {
        await loadCatalogosToSelects();
        await loadPersonal();
    } else if (page.includes('recargos.html') && checkAuth()) {
        await loadCatalogosToSelects();
        await loadPersonalSelect();
        await loadRecargos();
    } else if (page.includes('expedientes.html') && checkAuth()) {
        await loadCatalogosToSelects();
        await loadExpedientes();
    } else if (page.includes('licencias.html') && checkAuth()) {
        await loadCatalogosToSelects();
        await loadPersonalSelect();
        await loadLicencias();
    } else if (page.includes('usuarios.html') && checkAuth()) {
        await loadUsuarios();
    } else if (page.includes('configuracion.html') && checkAuth()) {
        await loadCatalogosToSelects();
        await loadFeriados();
        await loadConfiguracion();
        await loadDependencias();
    }
});

// Exponer funciones globales
window.handleLogin = handleLogin;
window.handleLogout = handleLogout;
window.togglePasswordVisibility = togglePasswordVisibility;
window.toggleMobileMenu = toggleMobileMenu;
window.closeModal = closeModal;
window.searchTable = searchTable;
window.loadPersonal = loadPersonal;
window.savePersonal = savePersonal;
window.editPersonal = editPersonal;
window.deletePersonal = deletePersonal;
window.openPersonalModal = openPersonalModal;
window.loadRecargos = loadRecargos;
window.saveRecargo = saveRecargo;
window.editRecargo = editRecargo;
window.deleteRecargo = deleteRecargo;
window.openRecargoModal = openRecargoModal;
window.loadExpedientes = loadExpedientes;
window.saveExpediente = saveExpediente;
window.viewExpediente = viewExpediente;
window.deleteExpediente = deleteExpediente;
window.openExpedienteModal = openExpedienteModal;
window.loadLicencias = loadLicencias;
window.saveLicencia = saveLicencia;
window.editLicencia = editLicencia;
window.deleteLicencia = deleteLicencia;
window.openLicenciaModal = openLicenciaModal;
window.loadUsuarios = loadUsuarios;
window.saveUsuario = saveUsuario;
window.editUsuario = editUsuario;
window.deleteUsuario = deleteUsuario;
window.openUsuarioModal = openUsuarioModal;
window.loadDashboard = loadDashboard;