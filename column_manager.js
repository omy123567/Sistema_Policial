// column_manager.js - Gestor de columnas personalizable
class ColumnManager {
    constructor(tableName, defaultColumns, columnLabels) {
        this.tableName = tableName;
        this.defaultColumns = defaultColumns;
        this.columnLabels = columnLabels;
        this.activeColumns = [...defaultColumns];
        this.columnOrder = [...defaultColumns];
        this.onChangeCallback = null;
        this.loadConfig();
    }

    async loadConfig() {
        try {
            const token = localStorage.getItem('jwt_token');
            const response = await fetch(`backend/api.php?endpoint=table_config&tabla=${this.tableName}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const config = await response.json();
            
            if (config && Array.isArray(config) && config.length > 0) {
                this.columnOrder = config;
                this.activeColumns = config;
            }
        } catch(e) {
            console.error('Error loading config:', e);
        }
    }

    async saveConfig() {
        try {
            const token = localStorage.getItem('jwt_token');
            await fetch(`backend/api.php?endpoint=table_config&tabla=${this.tableName}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ columnas: this.columnOrder })
            });
            if (this.onChangeCallback) this.onChangeCallback(this.columnOrder);
            return true;
        } catch(e) {
            console.error('Error saving config:', e);
            return false;
        }
    }

    getActiveColumns() {
        return this.columnOrder;
    }

    setOnChange(callback) {
        this.onChangeCallback = callback;
    }

    openConfigModal() {
        const modal = document.getElementById('columnConfigModal');
        if (!modal) {
            this.createConfigModal();
        }
        this.renderConfigModal();
        document.getElementById('columnConfigModal').style.display = 'flex';
    }

    createConfigModal() {
        const modalHTML = `
            <div id="columnConfigModal" class="modal" style="display:none;">
                <div class="modal-content" style="max-width:500px;">
                    <div class="modal-header">
                        <h3><i class="fas fa-columns"></i> Personalizar Columnas</h3>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary">Arrastre las columnas para cambiar el orden. Desmarque para ocultar.</p>
                        <div id="columnOrderList" class="column-order-list"></div>
                        <div class="mt-2">
                            <button id="resetColumnsBtn" class="btn btn-secondary btn-sm">Restablecer</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="closeColumnConfigBtn" class="btn btn-secondary">Cancelar</button>
                        <button id="saveColumnConfigBtn" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        document.getElementById('closeColumnConfigBtn').addEventListener('click', () => {
            document.getElementById('columnConfigModal').style.display = 'none';
        });
        document.getElementById('saveColumnConfigBtn').addEventListener('click', async () => {
            await this.saveConfig();
            document.getElementById('columnConfigModal').style.display = 'none';
            if (this.onChangeCallback) this.onChangeCallback(this.columnOrder);
        });
        document.getElementById('resetColumnsBtn').addEventListener('click', () => {
            this.columnOrder = [...this.defaultColumns];
            this.renderConfigModal();
        });
        document.querySelectorAll('#columnConfigModal .close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('columnConfigModal').style.display = 'none';
            });
        });
    }

    renderConfigModal() {
        const container = document.getElementById('columnOrderList');
        if (!container) return;
        
        container.innerHTML = '';
        const allColumns = [...new Set([...this.defaultColumns, ...this.columnOrder])];
        
        allColumns.forEach(col => {
            const isVisible = this.columnOrder.includes(col);
            const item = document.createElement('div');
            item.className = 'column-order-item';
            item.setAttribute('data-col', col);
            item.setAttribute('draggable', 'true');
            item.innerHTML = `
                <i class="fas fa-grip-vertical drag-icon"></i>
                <span class="col-name">${this.columnLabels[col] || col}</span>
                <input type="checkbox" class="col-toggle" ${isVisible ? 'checked' : ''}>
            `;
            
            // Drag & drop events
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', col);
                item.classList.add('dragging');
            });
            item.addEventListener('dragend', (e) => {
                item.classList.remove('dragging');
            });
            item.addEventListener('dragover', (e) => {
                e.preventDefault();
            });
            item.addEventListener('drop', (e) => {
                e.preventDefault();
                const fromCol = e.dataTransfer.getData('text/plain');
                const toCol = col;
                if (fromCol !== toCol) {
                    const fromIndex = this.columnOrder.indexOf(fromCol);
                    const toIndex = this.columnOrder.indexOf(toCol);
                    if (fromIndex !== -1 && toIndex !== -1) {
                        this.columnOrder.splice(toIndex, 0, this.columnOrder.splice(fromIndex, 1)[0]);
                        this.renderConfigModal();
                    }
                }
            });
            
            const checkbox = item.querySelector('.col-toggle');
            checkbox.addEventListener('change', (e) => {
                if (e.target.checked) {
                    if (!this.columnOrder.includes(col)) {
                        this.columnOrder.push(col);
                    }
                } else {
                    const index = this.columnOrder.indexOf(col);
                    if (index !== -1) {
                        this.columnOrder.splice(index, 1);
                    }
                }
                this.renderConfigModal();
            });
            
            container.appendChild(item);
        });
    }
}

// Exponer globalmente
window.ColumnManager = ColumnManager;