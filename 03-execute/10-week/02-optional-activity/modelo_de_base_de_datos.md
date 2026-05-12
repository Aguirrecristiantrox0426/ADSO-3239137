# Modelado Base de Datos - Software Jurídico

# MODELADO DE BASE DE DATOS — SOFTWARE JURÍDICO

## 1. ANÁLISIS DEL SISTEMA

El software permitirá:

- Registrar clientes
- Gestionar abogados
- Crear procesos jurídicos
- Agendar términos y audiencias
- Registrar consultas
- Controlar tareas
- Gestionar conflictos de interés
- Manejar usuarios y permisos

---

## 2. MÓDULOS DEL SISTEMA

- Inicio
- Clientes
- Procesos
- Consulta
- Tareas
- Términos
- Conflictos
- Documental
- Seguridad

---

## 3. MÓDULOS Y ENTIDADES — SOFTWARE JURÍDICO

### Inicio

- dashboard
- estadisticas
- indicadores
- notificaciones
- alertas
- actividad_reciente

### Clientes

- clientes
- tipos_cliente
- contactos_cliente
- direcciones_cliente
- empresas
- representantes_legales
- clientes_procesos

### Procesos

- procesos
- tipos_proceso
- estados_proceso
- actuaciones
- tipos_actuacion
- documentos
- tipos_documento
- expedientes
- evidencias
- comentarios_proceso
- etiquetas
- proceso_etiqueta
- abogados_procesos
- juzgados
- audiencias

### Consulta

- consultas
- historial_consultas
- fuentes_juridicas
- jurisprudencias
- normativas

### Tareas

- tareas
- estados_tarea
- prioridades
- asignaciones_tarea
- comentarios_tarea
- adjuntos_tarea

### Términos

- terminos
- tipos_termino
- alertas
- recordatorios
- calendario_eventos

### Conflictos

- conflictos_interes
- tipos_conflicto
- evaluaciones_conflicto
- clientes_relacionados

### Seguridad

- usuarios
- roles
- permisos
- usuario_rol
- rol_permiso
- sesiones_usuario
- auditoria
- logs_error
- tokens_acceso

### Documental

- documentos
- versiones_documento
- categorias_documento
- firmas_digitales
- repositorio_archivos

---

## 4. RELACIONES PRINCIPALES

- Un cliente puede tener muchos procesos.
- Un abogado puede gestionar muchos procesos.
- Un proceso puede tener muchos documentos.
- Un proceso puede tener muchas audiencias.
- Un proceso puede tener muchos términos.
- Un usuario puede tener muchas tareas.
