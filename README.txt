SISTEMA WEB DE VENTAS E INVENTARIO - SNACKS
Requisitos: XAMPP (Apache, PHP 8+, MySQL/MariaDB).

INSTALACIÓN
1. Copia la carpeta sistema_snacks_php dentro de C:\xampp\htdocs\
2. Abre XAMPP e inicia Apache y MySQL.
3. Entra a http://localhost/phpmyadmin
4. Importa schema.sql para crear la base de datos snacks y sus tablas.
   IMPORTANTE: schema.sql reinicia las tablas del sistema. Haz una copia de seguridad
   antes de importarlo si ya tienes datos.
5. Revisa config.php. Por defecto usa usuario MySQL root y contraseña vacía.
6. Abre http://localhost/sistema_snacks_php/setup.php una sola vez.
7. Cuentas iniciales: admin / admin123 y empleado / empleado123.
8. Por seguridad, elimina setup.php después de ejecutarlo y cambia las contraseñas.

FUNCIONES
- Inicio de sesión con roles administrador y empleado.
- El administrador registra, edita y desactiva administradores y empleados desde Usuarios.
- El empleado puede realizar ventas; no puede administrar productos, usuarios ni inventario.
- El administrador registra y modifica productos, precios, imágenes y existencias.
- Carrito de venta con cálculo automático de subtotales y total.
- Al finalizar una venta, el sistema valida el stock y descuenta automáticamente las unidades.
- La venta y el descuento de stock se ejecutan dentro de una transacción: si algo falla, se revierte todo.
- Inventario permite corregir existencias y tiene botón Imprimir inventario.
- Informe diario y comprobante individual imprimibles.

NOTA SOBRE INVENTARIO
El stock se descuenta cuando se presiona Finalizar venta, no cuando se agrega un producto
al carrito. Antes de registrar, el sistema vuelve a consultar y bloquear las filas de stock;
si no hay suficientes unidades, la venta no se guarda y no se descuenta nada.

NOTA SOBRE CSS
El archivo assets/style.css incluye comentarios en español que explican cada sección,
selector y propiedad principal: estructura general, menú, botones, ventas, tablas,
formularios, estadísticas, login, comprobantes, adaptación móvil e impresión.

Para producción configura HTTPS, credenciales seguras y permisos de escritura limitados
en uploads/. No publiques setup.php.


DESPLIEGUE EN RENDER + AIVEN
1. Sube el contenido de esta carpeta al directorio raíz del repositorio GitHub.
   Dockerfile debe quedar al mismo nivel que index.php y config.php.
2. En Render crea un servicio tipo Web Service conectado al repositorio.
3. Selecciona Runtime: Docker.
4. En Environment configura:
   DB_HOST = host exacto que muestra Aiven
   DB_PORT = 17820
   DB_NAME = sistema_snacks
   DB_USER = avnadmin
   DB_PASSWORD = contraseña de Aiven
   DB_SSL_CA = /etc/secrets/ca.pem
5. Carga el certificado CA de Aiven como Secret File en Render con el nombre ca.pem.
6. No ejecutes schema.sql sobre la base que ya contiene datos; este proyecto ya apunta
   a sistema_snacks y no necesita recrear las tablas.
7. El usuario administrador inicial solo se crea si ejecutas setup.php y la tabla usuarios
   está vacía. Después elimina setup.php y cambia las contraseñas iniciales.

IMPORTANTE: no publiques la contraseña ni el certificado CA en GitHub.
