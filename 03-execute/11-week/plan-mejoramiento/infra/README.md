# Entorno local de RENT-TOOLS

## Requisitos
- Docker Desktop con Docker Compose.
- Puertos disponibles: 4173 (frontend), 8000 (API directa) y 8080 (página informativa).

## Inicio rápido
1. Copie `.env.example` como `.env`.
2. Cambie las contraseñas de base de datos y las cuentas de demostración si el entorno se va a compartir.
3. Desde esta carpeta ejecute:

   ```bash
   docker compose up -d --build
   ```

4. Abra el frontend en <http://localhost:4173>.
5. Verifique la API en <http://localhost:4173/api/v1/health> o <http://localhost:8000/api/v1/health>.

El frontend Docker usa `/api/v1` en el mismo origen y Nginx reenvía `/api/` al servicio Laravel. Así, el navegador no realiza llamadas entre puertos y no depende de CORS. En desarrollo con Vite, el proxy de `app/vite.config.js` reenvía `/api` a `http://localhost:8000`; puede cambiar el destino con `API_PROXY_TARGET`.

## Cuentas iniciales
El contenedor ejecuta migraciones y un seeder idempotente al iniciar. Por defecto crea:
- Administrador: `admin@renttools.test` / `Admin123!`
- Asesor: `asesor@renttools.test` / `Asesor123!`

Las credenciales se configuran con `DEMO_ADMIN_EMAIL`, `DEMO_ADMIN_PASSWORD`, `DEMO_ADVISOR_EMAIL` y `DEMO_ADVISOR_PASSWORD` en `.env`. Cambie las contraseñas antes de publicar o desplegar fuera del entorno de demostración.

## Datos y persistencia
El seeder agrega categorías, herramientas, clientes, usuarios y un alquiler de demostración sin duplicarlos en ejecuciones posteriores. Los registros se almacenan en MySQL. El volumen `rent-tools-data` conserva la información.

Para detener los servicios sin borrar datos:

```bash
docker compose down
```

No use `docker compose down -v`: eliminaría el volumen de MySQL.

## Diagnóstico
```bash
docker compose ps
docker compose logs --tail=100 api
docker compose logs --tail=100 app
curl -i http://localhost:4173/api/v1/health
```
