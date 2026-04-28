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

// ==================== CARGAR CATÁLOGOS A DATALISTS ====================
async function loadDatalists() {
    const token = getToken();
    if (!token) return;
    
    const catalogos = [
        'jerarquias', 'oficinas', 'obras_sociales', 'tipos_recargo',
        'tipos_oficio', 'juzgados', 'dependencias', 'tipos_requerimiento', 'tipos_licencia'
    ];
    
    for (const tipo of catalogos) {
        try {
            const response = await fetch(`${API_URL}?endpoint=catalogos&tipo=${tipo}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const data = await response.json();
            const datalistId = `${tipo}List`;
            const datalist = document.getElementById(datalistId);
            if (datalist && data && Array.isArray(data)) {
                datalist.innerHTML = data.map(item => `<option value="${escapeHtml(item.valor)}">`).join('');
                console.log(`✅ Cargado ${tipo}: ${data.length} opciones`);
            }
        } catch (error) {
            console.error(`Error loading ${tipo}:`, error);
        }
    }
    
    // Cargar personal para datalist
    try {
        const response = await fetch(`${API_URL}?endpoint=personal`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const personal = await response.json();
        const personalList = document.getElementById('personalList');
        if (personalList && personal && Array.isArray(personal)) {
            personalList.innerHTML = personal.map(item => 
                `<option value="${item.id}|${item.legajo} - ${item.apellido}, ${item.nombre}">`
            ).join('');
            console.log(`✅ Cargado personal: ${personal.length} agentes`);
        }
    } catch (error) {
        console.error('Error loading personal:', error);
    }
}

// Sincronizar inputs con hidden fields
function initDatalistSync() {
    // Jerarquías (Personal)
    const jerarquiaInput = document.getElementById('jerarquia_input');
    const jerarquiaHidden = document.getElementById('jerarquia');
    if (jerarquiaInput && jerarquiaHidden) {
        jerarquiaInput.addEventListener('change', () => { jerarquiaHidden.value = jerarquiaInput.value; });
    }
    
    // Oficinas (Personal)
    const oficinaInput = document.getElementById('oficina_input');
    const oficinaHidden = document.getElementById('oficina');
    if (oficinaInput && oficinaHidden) {
        oficinaInput.addEventListener('change', () => { oficinaHidden.value = oficinaInput.value; });
    }
    
    // Obra Social (Personal)
    const obraSocialInput = document.getElementById('obra_social_input');
    const obraSocialHidden = document.getElementById('obra_social');
    if (obraSocialInput && obraSocialHidden) {
        obraSocialInput.addEventListener('change', () => { obraSocialHidden.value = obraSocialInput.value; });
    }
    
    // Tipo de Recargo (Recargos)
    const tipoRecargoInput = document.getElementById('recargo_tipo_input');
    const tipoRecargoHidden = document.getElementById('recargo_tipo');
    if (tipoRecargoInput && tipoRecargoHidden) {
        tipoRecargoInput.addEventListener('change', () => { tipoRecargoHidden.value = tipoRecargoInput.value; });
    }
    
    // Oficina en recargos
    const oficinaRecargoInput = document.getElementById('recargo_oficina_input');
    const oficinaRecargoHidden = document.getElementById('recargo_oficina');
    if (oficinaRecargoInput && oficinaRecargoHidden) {
        oficinaRecargoInput.addEventListener('change', () => { oficinaRecargoHidden.value = oficinaRecargoInput.value; });
    }
    
    // Personal en recargos
    const personalInput = document.getElementById('recargo_personal_input');
    const personalHidden = document.getElementById('recargo_personal');
    if (personalInput && personalHidden) {
        personalInput.addEventListener('change', () => {
            const parts = personalInput.value.split('|');
            personalHidden.value = parts[0] || '';
        });
    }
    
    // Licencias - Agente
    const licenciaAgenteInput = document.getElementById('licencia_agente_input');
    const licenciaAgenteHidden = document.getElementById('licencia_agente');
    if (licenciaAgenteInput && licenciaAgenteHidden) {
        licenciaAgenteInput.addEventListener('change', () => {
            const parts = licenciaAgenteInput.value.split('|');
            licenciaAgenteHidden.value = parts[0] || '';
        });
    }
    
    // Licencias - Tipo Licencia
    const tipoLicenciaInput = document.getElementById('licencia_tipo_input');
    const tipoLicenciaHidden = document.getElementById('licencia_tipo');
    if (tipoLicenciaInput && tipoLicenciaHidden) {
        tipoLicenciaInput.addEventListener('change', () => { tipoLicenciaHidden.value = tipoLicenciaInput.value; });
    }
    
    // Expedientes - Tipo de Oficio
    const tipoOficioInput = document.getElementById('expediente_tipo_oficio_input');
    const tipoOficioHidden = document.getElementById('expediente_tipo_oficio');
    if (tipoOficioInput && tipoOficioHidden) {
        tipoOficioInput.addEventListener('change', () => { tipoOficioHidden.value = tipoOficioInput.value; });
    }
    
    // Expedientes - Juzgado
    const juzgadoInput = document.getElementById('expediente_juzgado_input');
    const juzgadoHidden = document.getElementById('expediente_juzgado');
    if (juzgadoInput && juzgadoHidden) {
        juzgadoInput.addEventListener('change', () => { juzgadoHidden.value = juzgadoInput.value; });
    }
    
    // Expedientes - Tipo Requerimiento
    const tipoRequerimientoInput = document.getElementById('expediente_tipo_requerimiento_input');
    const tipoRequerimientoHidden = document.getElementById('expediente_tipo_requerimiento');
    if (tipoRequerimientoInput && tipoRequerimientoHidden) {
        tipoRequerimientoInput.addEventListener('change', () => { tipoRequerimientoHidden.value = tipoRequerimientoInput.value; });
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

// ==================== PERSONAL ====================
async function loadPersonal() {
    const tbody = document.getElementById('personalTableBody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center">Cargando...</td></tr>';
    
    try {
        const data = await apiRequest('personal');
        if (data && Array.isArray(data)) {
            renderPersonalTable(data);
            showToast(`✅ ${data.length} agentes cargados`, 'success');
        } else {
            renderPersonalTable([]);
            showToast('No hay datos de personal', 'warning');
        }
    } catch (error) {
        console.error('Error loading personal:', error);
        showToast('Error cargando personal', 'error');
        renderPersonalTable([]);
    }
}

function renderPersonalTable(data) {
    const tbody = document.getElementById('personalTableBody');
    if (!tbody) return;
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay registros</td></tr>';
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
                <button class="btn-accion btn-editar" onclick="editPersonal(${item.id})">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn-accion btn-eliminar" onclick="deletePersonal(${item.id})">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </td>
        </table>
    `).join('');
}

function openPersonalModal(data = null) {
    const modal = document.getElementById('personalModal');
    if (!modal) return;
    
    if (data) {
        document.getElementById('personalId').value = data.id;
        document.getElementById('legajo').value = data.legajo || '';
        document.getElementById('jerarquia_input').value = data.jerarquia || '';
        document.getElementById('jerarquia').value = data.jerarquia || '';
        document.getElementById('apellido').value = data.apellido || '';
        document.getElementById('nombre').value = data.nombre || '';
        document.getElementById('dni').value = data.dni || '';
        document.getElementById('sexo').value = data.sexo || '';
        document.getElementById('oficina_input').value = data.oficina || '';
        document.getElementById('oficina').value = data.oficina || '';
        document.getElementById('fecha_nacimiento').value = data.fecha_nacimiento || '';
        document.getElementById('tiene_arma').checked = data.tiene_arma == 1;
        document.getElementById('arma_marca').value = data.arma_marca || '';
        document.getElementById('arma_modelo').value = data.arma_modelo || '';
        document.getElementById('arma_serie').value = data.arma_serie || '';
        document.getElementById('sin_arma_justificacion').value = data.sin_arma_justificacion || '';
        document.getElementById('nro_credencial').value = data.nro_credencial || '';
        document.getElementById('nro_licencia_conducir').value = data.nro_licencia_conducir || '';
        document.getElementById('fecha_vencimiento_licencia').value = data.fecha_vencimiento_licencia || '';
        document.getElementById('obra_social_input').value = data.obra_social || '';
        document.getElementById('obra_social').value = data.obra_social || '';
        document.getElementById('nro_afiliado').value = data.nro_afiliado || '';
        document.getElementById('telefono').value = data.telefono || '';
        document.getElementById('email').value = data.email || '';
        document.getElementById('direccion').value = data.direccion || '';
        document.getElementById('modalTitle').textContent = 'Editar Personal';
        
        const armamentoFields = document.getElementById('armamentoFields');
        const sinArmaFields = document.getElementById('sinArmaFields');
        if (armamentoFields && sinArmaFields) {
            if (data.tiene_arma == 1) {
                armamentoFields.style.display = 'block';
                sinArmaFields.style.display = 'none';
            } else {
                armamentoFields.style.display = 'none';
                sinArmaFields.style.display = 'block';
            }
        }
    } else {
        document.getElementById('personalForm')?.reset();
        document.getElementById('personalId').value = '';
        document.getElementById('tiene_arma').checked = false;
        if (document.getElementById('armamentoFields')) document.getElementById('armamentoFields').style.display = 'none';
        if (document.getElementById('sinArmaFields')) document.getElementById('sinArmaFields').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Nuevo Personal';
    }
    modal.style.display = 'flex';
}

async function savePersonal() {
    const id = document.getElementById('personalId')?.value;
    const data = {
        legajo: document.getElementById('legajo')?.value,
        jerarquia: document.getElementById('jerarquia')?.value,
        apellido: document.getElementById('apellido')?.value,
        nombre: document.getElementById('nombre')?.value,
        dni: document.getElementById('dni')?.value,
        sexo: document.getElementById('sexo')?.value,
        oficina: document.getElementById('oficina')?.value,
        fecha_nacimiento: document.getElementById('fecha_nacimiento')?.value,
        tiene_arma: document.getElementById('tiene_arma')?.checked ? 1 : 0,
        arma_marca: document.getElementById('arma_marca')?.value,
        arma_modelo: document.getElementById('arma_modelo')?.value,
        arma_serie: document.getElementById('arma_serie')?.value,
        sin_arma_justificacion: document.getElementById('sin_arma_justificacion')?.value,
        nro_credencial: document.getElementById('nro_credencial')?.value,
        nro_licencia_conducir: document.getElementById('nro_licencia_conducir')?.value,
        fecha_vencimiento_licencia: document.getElementById('fecha_vencimiento_licencia')?.value,
        obra_social: document.getElementById('obra_social')?.value,
        nro_afiliado: document.getElementById('nro_afiliado')?.value,
        telefono: document.getElementById('telefono')?.value,
        email: document.getElementById('email')?.value,
        direccion: document.getElementById('direccion')?.value
    };
    
    if (!data.legajo || !data.apellido || !data.nombre || !data.dni) {
        showToast('Complete los campos requeridos', 'warning');
        return;
    }
    
    try {
        let result;
        if (id) {
            result = await apiRequest(`personal&id=${id}`, 'PUT', data);
        } else {
            result = await apiRequest('personal', 'POST', data);
        }
        
        if (result && result.success) {
            showToast(`Personal ${id ? 'actualizado' : 'creado'} correctamente`, 'success');
            closeModal();
            await loadPersonal();
            await loadDatalists();
        } else {
            showToast('Error al guardar: ' + (result?.error || 'Error desconocido'), 'error');
        }
    } catch (error) {
        showToast('Error guardando personal', 'error');
    }
}

async function editPersonal(id) {
    try {
        const data = await apiRequest(`personal&id=${id}`);
        if (data && data.id) openPersonalModal(data);
        else showToast('No se encontraron datos', 'error');
    } catch (error) {
        showToast('Error cargando datos', 'error');
    }
}

async function deletePersonal(id) {
    if (confirm('¿Está seguro de eliminar este registro?')) {
        try {
            const result = await apiRequest(`personal&id=${id}`, 'DELETE');
            if (result && result.success) {
                showToast('Registro eliminado', 'success');
                await loadPersonal();
            } else {
                showToast('Error al eliminar', 'error');
            }
        } catch (error) {
            showToast('Error eliminando registro', 'error');
        }
    }
}

// ==================== DASHBOARD ====================
async function loadDashboard() {
    try {
        const data = await apiRequest('dashboard');
        if (data) {
            renderDashboard(data);
            showToast('Dashboard actualizado', 'success');
        } else {
            showToast('Error cargando dashboard', 'error');
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showToast('Error cargando dashboard', 'error');
    }
}

function renderDashboard(data) {
    const totalPersonal = document.getElementById('totalPersonal');
    const totalRecargos = document.getElementById('totalRecargos');
    const totalExpedientes = document.getElementById('totalExpedientes');
    const totalLicencias = document.getElementById('totalLicencias');
    
    if (totalPersonal) totalPersonal.textContent = data.total_personal || 0;
    if (totalRecargos) totalRecargos.textContent = (data.ultimos_recargos || []).length;
    if (totalExpedientes) totalExpedientes.textContent = (data.expedientes_recientes || []).length;
    if (totalLicencias) totalLicencias.textContent = (data.licencias_activas || []).length;
    
    // Renderizar jerarquías
    const jerarquiasContainer = document.getElementById('jerarquiasContainer');
    if (jerarquiasContainer && data.jerarquias && data.jerarquias.length > 0) {
        const max = Math.max(...data.jerarquias.map(j => j.cantidad), 1);
        jerarquiasContainer.innerHTML = data.jerarquias.map(j => `
            <div class="bar-item">
                <div class="bar-label">
                    <span>${escapeHtml(j.jerarquia || 'Sin especificar')}</span>
                    <span>${j.cantidad}</span>
                </div>
                <div class="bar">
                    <div class="bar-fill" style="width: ${(j.cantidad / max) * 100}%">${j.cantidad}</div>
                </div>
            </div>
        `).join('');
    } else if (jerarquiasContainer) {
        jerarquiasContainer.innerHTML = '<div class="text-center">No hay datos de jerarquías</div>';
    }
    
    // Renderizar últimos recargos
    const recargosContainer = document.getElementById('ultimosRecargos');
    if (recargosContainer && data.ultimos_recargos && data.ultimos_recargos.length > 0) {
        recargosContainer.innerHTML = data.ultimos_recargos.map(r => `
            <div class="list-item">
                <div><strong>${escapeHtml(r.fecha)}</strong> - ${escapeHtml(r.tipo_recargo || 'Sin tipo')}</div>
                <div class="text-secondary" style="font-size: 0.75rem;">${escapeHtml(r.apellido ? `${r.apellido}, ${r.nombre}` : 'Sin asignar')}</div>
            </div>
        `).join('');
    } else if (recargosContainer) {
        recargosContainer.innerHTML = '<div class="text-center">No hay recargos registrados</div>';
    }
    
    // Renderizar expedientes recientes
    const expedientesContainer = document.getElementById('expedientesRecientes');
    if (expedientesContainer && data.expedientes_recientes && data.expedientes_recientes.length > 0) {
        expedientesContainer.innerHTML = data.expedientes_recientes.map(e => `
            <div class="list-item">
                <div><strong>${escapeHtml(e.nro_expediente)}</strong> - ${escapeHtml(e.fecha)}</div>
                <div class="text-secondary" style="font-size: 0.75rem;">${escapeHtml(e.tipo_oficio || 'Sin tipo')}</div>
            </div>
        `).join('');
    } else if (expedientesContainer) {
        expedientesContainer.innerHTML = '<div class="text-center">No hay expedientes registrados</div>';
    }
    
    // Renderizar licencias activas
    const licenciasContainer = document.getElementById('licenciasActivas');
    if (licenciasContainer && data.licencias_activas && data.licencias_activas.length > 0) {
        licenciasContainer.innerHTML = data.licencias_activas.map(l => `
            <div class="list-item">
                <div><strong>${escapeHtml(l.apellido ? `${l.apellido}, ${l.nombre}` : 'Sin agente')}</strong></div>
                <div class="text-secondary" style="font-size: 0.75rem;">${escapeHtml(l.tipo_licencia)} - ${escapeHtml(l.fecha_inicio)}</div>
                <span class="badge badge-${l.estado === 'Aprobada' ? 'success' : 'warning'}">${l.estado}</span>
            </div>
        `).join('');
    } else if (licenciasContainer) {
        licenciasContainer.innerHTML = '<div class="text-center">No hay licencias activas</div>';
    }
}

// ==================== EXPEDIENTES ====================
async function loadExpedientes() {
    try {
        const data = await apiRequest('expedientes');
        if (data && Array.isArray(data)) {
            renderExpedientesTable(data);
            showToast(`✅ ${data.length} expedientes cargados`, 'success');
        } else {
            renderExpedientesTable([]);
            showToast('No hay datos de expedientes', 'warning');
        }
    } catch (error) {
        console.error('Error loading expedientes:', error);
        showToast('Error cargando expedientes', 'error');
        renderExpedientesTable([]);
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
                <button class="btn-accion btn-editar" onclick="viewExpediente(${item.id})">
                    <i class="fas fa-eye"></i> Ver
                </button>
                <button class="btn-accion btn-eliminar" onclick="deleteExpediente(${item.id})">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
             </td>
        </tr>
    `).join('');
}

async function viewExpediente(id) {
    try {
        const data = await apiRequest(`expedientes&id=${id}`);
        if (data) openExpedienteModal(data);
    } catch (error) {
        showToast('Error cargando expediente', 'error');
    }
}

async function deleteExpediente(id) {
    if (confirm('¿Eliminar este expediente?')) {
        try {
            const result = await apiRequest(`expedientes&id=${id}`, 'DELETE');
            if (result && result.success) {
                showToast('Expediente eliminado', 'success');
                await loadExpedientes();
            }
        } catch (error) {
            showToast('Error eliminando expediente', 'error');
        }
    }
}

function openExpedienteModal(data = null) {
    const modal = document.getElementById('expedienteModal');
    if (!modal) return;
    
    if (data) {
        document.getElementById('expedienteId').value = data.id;
        document.getElementById('expediente_nro').value = data.nro_expediente;
        document.getElementById('expediente_fecha').value = data.fecha;
        document.getElementById('expediente_tipo_oficio_input').value = data.tipo_oficio || '';
        document.getElementById('expediente_tipo_oficio').value = data.tipo_oficio || '';
        document.getElementById('expediente_juzgado_input').value = data.juzgado_origen || '';
        document.getElementById('expediente_juzgado').value = data.juzgado_origen || '';
        document.getElementById('expediente_tipo_requerimiento_input').value = data.tipo_requerimiento || '';
        document.getElementById('expediente_tipo_requerimiento').value = data.tipo_requerimiento || '';
        document.getElementById('expediente_resumen').value = data.resumen || '';
        document.getElementById('expediente_informe').value = data.nro_informe_tecnico || '';
        document.getElementById('expediente_estado').value = data.estado || 'Pendiente';
        document.getElementById('modalTitle').textContent = 'Editar Expediente';
    } else {
        document.getElementById('expedienteForm')?.reset();
        document.getElementById('expedienteId').value = '';
        document.getElementById('expediente_fecha').value = new Date().toISOString().split('T')[0];
        document.getElementById('modalTitle').textContent = 'Nuevo Expediente';
    }
    modal.style.display = 'flex';
}

async function saveExpediente() {
    const id = document.getElementById('expedienteId')?.value;
    const data = {
        nro_expediente: document.getElementById('expediente_nro')?.value,
        fecha: document.getElementById('expediente_fecha')?.value,
        tipo_oficio: document.getElementById('expediente_tipo_oficio')?.value,
        juzgado_origen: document.getElementById('expediente_juzgado')?.value,
        tipo_requerimiento: document.getElementById('expediente_tipo_requerimiento')?.value,
        resumen: document.getElementById('expediente_resumen')?.value,
        nro_informe_tecnico: document.getElementById('expediente_informe')?.value,
        estado: document.getElementById('expediente_estado')?.value
    };
    
    if (!data.nro_expediente || !data.fecha) {
        showToast('Complete los campos requeridos', 'warning');
        return;
    }
    
    try {
        let result;
        if (id) {
            result = await apiRequest(`expedientes&id=${id}`, 'PUT', data);
        } else {
            result = await apiRequest('expedientes', 'POST', data);
        }
        
        if (result && result.success) {
            showToast(`Expediente ${id ? 'actualizado' : 'creado'} correctamente`, 'success');
            closeModal();
            await loadExpedientes();
        } else {
            showToast('Error al guardar', 'error');
        }
    } catch (error) {
        showToast('Error guardando expediente', 'error');
    }
}

// ==================== USUARIOS ====================
async function loadUsuarios() {
    try {
        const data = await apiRequest('usuarios');
        if (data && Array.isArray(data)) {
            renderUsuariosTable(data);
            showToast(`✅ ${data.length} usuarios cargados`, 'success');
        } else {
            renderUsuariosTable([]);
            showToast('No hay datos de usuarios', 'warning');
        }
    } catch (error) {
        console.error('Error loading usuarios:', error);
        showToast('Error cargando usuarios', 'error');
        renderUsuariosTable([]);
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
            <td>${item.estado === 'Activo' ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>'}</td>
            <td>
                <button class="btn-accion btn-editar" onclick="editUsuario(${item.id})">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn-accion btn-eliminar" onclick="deleteUsuario(${item.id})">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
             </td>
        </tr>
    `).join('');
}

function openUsuarioModal(data = null) {
    const modal = document.getElementById('usuarioModal');
    if (!modal) return;
    
    if (data) {
        document.getElementById('usuarioId').value = data.id;
        document.getElementById('usuario_nombre').value = data.nombre_completo;
        document.getElementById('usuario_username').value = data.username;
        document.getElementById('usuario_email').value = data.email;
        document.getElementById('usuario_rol').value = data.rol_id;
        document.getElementById('usuario_estado').value = data.estado;
        document.getElementById('modalTitle').textContent = 'Editar Usuario';
    } else {
        document.getElementById('usuarioForm')?.reset();
        document.getElementById('usuarioId').value = '';
        document.getElementById('modalTitle').textContent = 'Nuevo Usuario';
    }
    modal.style.display = 'flex';
}

async function saveUsuario() {
    const id = document.getElementById('usuarioId')?.value;
    const data = {
        nombre_completo: document.getElementById('usuario_nombre')?.value,
        username: document.getElementById('usuario_username')?.value,
        email: document.getElementById('usuario_email')?.value,
        password: document.getElementById('usuario_password')?.value,
        rol_id: document.getElementById('usuario_rol')?.value,
        estado: document.getElementById('usuario_estado')?.value
    };
    
    if (!data.nombre_completo || !data.username || !data.email) {
        showToast('Complete los campos requeridos', 'warning');
        return;
    }
    
    try {
        let result;
        if (id) {
            result = await apiRequest(`usuarios&id=${id}`, 'PUT', data);
        } else {
            if (!data.password) {
                showToast('La contraseña es requerida', 'error');
                return;
            }
            result = await apiRequest('usuarios', 'POST', data);
        }
        
        if (result && result.success) {
            showToast(`Usuario ${id ? 'actualizado' : 'creado'} correctamente`, 'success');
            closeModal();
            await loadUsuarios();
        } else {
            showToast('Error al guardar', 'error');
        }
    } catch (error) {
        showToast('Error guardando usuario', 'error');
    }
}

async function editUsuario(id) {
    try {
        const data = await apiRequest(`usuarios&id=${id}`);
        if (data && data.id) openUsuarioModal(data);
        else showToast('No se encontraron datos', 'error');
    } catch (error) {
        showToast('Error cargando datos', 'error');
    }
}

async function deleteUsuario(id) {
    if (confirm('¿Eliminar este usuario?')) {
        try {
            const result = await apiRequest(`usuarios&id=${id}`, 'DELETE');
            if (result && result.success) {
                showToast('Usuario eliminado', 'success');
                await loadUsuarios();
            }
        } catch (error) {
            showToast('Error eliminando usuario', 'error');
        }
    }
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
    
    // Verificar autenticación y cargar según la página
    const page = window.location.pathname;
    
    if (page.includes('dashboard.html')) {
        if (await checkAuth()) {
            await loadDashboard();
        }
    } else if (page.includes('personal.html')) {
        if (await checkAuth()) {
            await loadDatalists();
            await loadPersonal();
            initDatalistSync();
            
            const chk = document.getElementById('tiene_arma');
            if (chk) {
                chk.addEventListener('change', function(e) {
                    const arm = document.getElementById('armamentoFields');
                    const sin = document.getElementById('sinArmaFields');
                    if (arm && sin) {
                        arm.style.display = e.target.checked ? 'block' : 'none';
                        sin.style.display = e.target.checked ? 'none' : 'block';
                    }
                });
            }
        }
    } else if (page.includes('recargos.html')) {
        if (await checkAuth()) {
            await loadDatalists();
            initDatalistSync();
            // La función loadRecargos está en recargos.html
            if (typeof window.loadRecargos === 'function') {
                window.loadRecargos();
            }
        }
    } else if (page.includes('expedientes.html')) {
        if (await checkAuth()) {
            await loadDatalists();
            await loadExpedientes();
            initDatalistSync();
        }
    } else if (page.includes('licencias.html')) {
        if (await checkAuth()) {
            await loadDatalists();
            initDatalistSync();
            // La función loadLicencias está en licencias.html
            if (typeof window.loadLicencias === 'function') {
                window.loadLicencias();
            }
        }
    } else if (page.includes('usuarios.html')) {
        if (await checkAuth()) {
            await loadUsuarios();
        }
    } else if (page.includes('configuracion.html')) {
        if (await checkAuth()) {
            await loadDatalists();
        }
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
window.loadDatalists = loadDatalists;
window.initDatalistSync = initDatalistSync;
window.loadPersonal = loadPersonal;
window.savePersonal = savePersonal;
window.editPersonal = editPersonal;
window.deletePersonal = deletePersonal;
window.openPersonalModal = openPersonalModal;
window.loadExpedientes = loadExpedientes;
window.saveExpediente = saveExpediente;
window.viewExpediente = viewExpediente;
window.deleteExpediente = deleteExpediente;
window.openExpedienteModal = openExpedienteModal;
window.loadUsuarios = loadUsuarios;
window.saveUsuario = saveUsuario;
window.editUsuario = editUsuario;
window.deleteUsuario = deleteUsuario;
window.openUsuarioModal = openUsuarioModal;
window.loadDashboard = loadDashboard;