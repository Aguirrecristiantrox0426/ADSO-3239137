# Reporte de Errores - Software Jurídico

## Problemas encontrados y posibles soluciones

---

## 1. No permite crear clientes nuevos

### Problema

El formulario se llena correctamente, pero al dar clic en “crear” no guarda la información.

### Posible solución

- Revisar si el botón está enviando la información al backend.
- Validar que la tabla de clientes y el endpoint de creación funcionen correctamente.
- Revisar errores en consola y en la base de datos.

---

## 2. Error al filtrar términos por prioridad alta

### Problema

Al seleccionar “prioridad alta” aparece una alerta de mal funcionamiento.

### Posible solución

- Verificar que el valor de prioridad enviado desde el frontend coincida con el esperado en backend y base de datos.
- Revisar consultas SQL y validaciones de filtros.

---

## 3. No deja crear usuarios por error en roles

### Problema

Al seleccionar un rol aparece una alerta y no permite guardar el usuario.

### Posible solución

- Verificar que existan roles registrados en la base de datos.
- Revisar que el sistema envíe correctamente el ID del rol seleccionado.
- Validar relaciones entre usuarios y roles.

---

## 4. Error al crear tareas

### Problema

No permite seleccionar procesos ni clientes relacionados y aparece “entrada inválida”.

### Posible solución

- Revisar relaciones entre tareas, clientes y procesos.
- Validar que los select carguen información desde la base de datos.
- Revisar que el backend reciba correctamente los datos obligatorios.

---

# Conclusión general

Con estos problemas se concluye que el sistema jurídico aún se encuentra en una etapa temprana de desarrollo.

La interfaz gráfica ya está construida, pero existen fallas en:

- integración entre frontend y backend,
- validaciones,
- relaciones de base de datos,
- carga de información dinámica.

Esto indica que el sistema todavía necesita pruebas funcionales, corrección de lógica y validación de conexiones entre módulos antes de entrar en producción.
