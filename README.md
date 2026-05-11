# Sistema de Gestión Policial

[![Versión](https://img.shields.io/badge/versión-2.0.0-blue.svg)](https://github.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4.svg)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1.svg)](https://mysql.com/)
[![Licencia](https://img.shields.io/badge/licencia-Institucional-red.svg)]()

Sistema web completo para la gestión institucional de dependencias policiales y fuerzas de seguridad. Diseñado para optimizar procesos administrativos, control de personal, gestión de expedientes y generación de reportes.

---

## 📋 Tabla de Contenidos

- [Características Principales](#-características-principales)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación](#-instalación)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Módulos Funcionales](#-módulos-funcionales)
- [Roles y Permisos](#-roles-y-permisos)
- [API Endpoints](#-api-endpoints)
- [Usuarios de Prueba](#-usuarios-de-prueba)
- [Personalización de Columnas](#-personalización-de-columnas)
- [Solución de Problemas](#-solución-de-problemas)
- [Seguridad](#-seguridad)

---

## 🚀 Características Principales

### 🎯 Gestión Integral
- ✅ **CRUD completo** en todos los módulos (Crear, Leer, Actualizar, Eliminar)
- ✅ **Dashboard ejecutivo** configurable con widgets personalizables
- ✅ **Gráficos interactivos** con Chart.js
- ✅ **Exportación avanzada** a CSV y PDF
- ✅ **Sistema de notificaciones** en tiempo real
- ✅ **Personalización de columnas** con drag & drop (guardado por usuario)

### 🔐 Seguridad y Control
- ✅ **Autenticación JWT** (JSON Web Tokens)
- ✅ **Roles y permisos granulares** (4 niveles de acceso)
- ✅ **Jerarquía de dependencias** (Central → Delegaciones → Secciones)
- ✅ **Bitácora de auditoría** completa
- ✅ **Control de acceso por módulo** (ver, crear, editar, eliminar)
- ✅ **Protección contra SQL injection y XSS**

### 👥 Gestión de Personal
- ✅ Datos personales y jerarquías
- ✅ Control de armamento (armas de fuego)
- ✅ Control de chalecos antibalas con fecha de vencimiento
- ✅ Documentos adjuntos (CV, certificados, fotos)
- ✅ Calendario de cumpleaños
- ✅ Gestión de licencias de conducir
- ✅ Obra social y número de afiliado
- ✅ Asignación a secciones de guardia

### ⚖️ Recargos Disciplinarios
- ✅ Registro de faltas y sanciones
- ✅ Tipos de recargo configurables
- ✅ Asignación de personal involucrado
- ✅ Asociación a secciones de guardia
- ✅ Estados de resolución (Pendiente/Resuelto)
- ✅ Historial completo por agente

### 📁 Expedientes
- ✅ Número único de expediente
- ✅ Sistema de elevaciones con imágenes/PDF
- ✅ Adjuntos con drag & drop
- ✅ Estados: Pendiente, En Trámite, Derivado, Resuelto, Elevado, Archivado
- ✅ Búsqueda avanzada por múltiples criterios
- ✅ Personalización de columnas de tabla

### 📅 Licencias
- ✅ Tipos de licencia configurables
- ✅ Cálculo automático de fechas fin
- ✅ Consideración de días hábiles y viaje
- ✅ Estados: Pendiente, Aprobada, Rechazada, En Curso, Finalizada
- ✅ Control de días hábiles y fines de semana

### 👤 Usuarios y Roles
- ✅ Gestión completa de usuarios
- ✅ Roles: Administrador Central, Supervisor Delegación, Jefe Sección, Operador
- ✅ Permisos configurables por módulo y acción
- ✅ Asignación de dependencia específica
- ✅ Niveles de acceso: solo_propio, solo_subordinado, delegacion, todas

### ⚙️ Configuración
- ✅ Catálogos dinámicos (jerarquías, oficinas, juzgados, tipos de licencia, etc.)
- ✅ Feriados nacionales y provinciales
- ✅ Estructura de dependencias jerárquica
- ✅ Personalización del dashboard
- ✅ Logo del sistema personalizable
- ✅ Personalización de columnas por tabla y por usuario

---

## 💻 Requisitos del Sistema

### Software Necesario

| Software | Versión Mínima | Recomendada |
|----------|---------------|--------------|
| PHP | 7.4 | 8.0+ |
| MySQL | 5.7 | 8.0+ |
| Apache | 2.4 | 2.4+ |
| XAMPP/WAMP/LAMP | - | Última versión |

### Extensiones PHP Requeridas

```bash
✅ PDO
✅ MySQLi
✅ JSON
✅ OpenSSL
✅ mbstring
✅ fileinfo
✅ GD (para imágenes)
✅ Zip (para exportación)
