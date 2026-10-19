# Promociones automáticas por paquetes

## Reglas

En Filament > Tienda > Promociones se configura la categoría, precio normal
elegible, unidades del paquete, precio del paquete, vigencia y estado activo.
Ejemplo: Gorras, Q350, 2 unidades por Q500.

Se combinan modelos únicamente cuando categoría y precio normal coinciden.
Dos unidades cuestan Q500; tres Q850; cuatro Q1,000. Se repiten paquetes
completos. Las unidades sobrantes se cobran a precio normal.

Se selecciona una sola promoción por grupo de categoría y precio: la que produzca
el mayor ahorro para la cantidad actual. Los empates favorecen el ID más antiguo.
Otros grupos del carrito pueden tener sus propias promociones, sin acumularlas
sobre las mismas unidades. El campo `type` permite añadir otros tipos más adelante;
esta versión admite exclusivamente `category_bundle`.

`compare_price` sigue siendo una referencia visual. El precio de elegibilidad es
`products.price`. No se descuenta de nuevo la diferencia entre esos dos campos.
En catálogo se muestra el precio por unidad y las condiciones del paquete;
el orden por precio usa el precio unitario, porque el precio del paquete depende
del conjunto comprado.

La fecha inicial es inclusiva y la final exclusiva. Las fechas usan el horario de
la aplicación. Confirmar `APP_TIMEZONE=America/Guatemala` si ese es el horario
comercial del negocio. Una categoría inactiva no genera promociones.

## Importes e historial

El cálculo de paquetes y reparto de descuentos usa centavos enteros.
El ahorro se distribuye proporcionalmente entre todas las unidades del grupo,
con el método de restos mayores y desempate por ID del producto. Esto mantiene
exactamente el total aunque existan diferencias de un centavo entre líneas.

`order_items.subtotal` y `orders.subtotal` son importes netos después de promociones.
`promotion_discount` informa del ahorro ya incluido: NO se vuelve a restar.
Cada línea conserva `base_unit_price`, descuento y una copia descriptiva de la
promoción. `unit_price` conserva un promedio efectivo redondeado; el subtotal de
línea es el importe definitivo y puede diferir de promedio × cantidad por redondeo.
Las pantallas del cliente muestran precio normal y ahorro para evitar esa confusión.
Los reportes de productos siguen sumando subtotales netos.

El monedero se aplica después. La recompensa del 2% usa subtotal neto menos saldo
utilizado, excluyendo envío. Se mantienen las reservas, devoluciones, reversiones
e idempotencia existentes. El estado de pago continúa con el flujo actual, también
cuando el saldo cubre todo el pedido; no se añadió un cobro automático.

Al confirmar, se consultan y bloquean productos y promociones aplicables. Si cambió
el precio o la promoción desde el ingreso al checkout se bloquea la compra y se pide
volver al carrito. Las cantidades y la huella de cotización están protegidas frente
a cambios desde el cliente. Los pedidos antiguos conservan sus importes y no se
recalculan con promociones nuevas.

## Instalación en el proyecto real

1. Respaldar base de datos y probar primero en una copia de desarrollo.
2. Copiar los archivos de esta entrega e instalar dependencias del lock existente.
3. Ejecutar `php artisan migrate`.
4. Ejecutar `php artisan db:seed --class=PromotionPermissionsSeeder`.
   Este seeder agrega permisos y los concede al rol Administrador si existe;
   conserva las asignaciones anteriores. No es necesario ejecutar todos los seeders.
5. Ejecutar `php artisan optimize:clear`.
6. Crear la promoción en Filament, verificar las fechas y activarla.
7. Verificar una compra de dos modelos distintos y una de tres unidades, incluyendo
   monedero, correo, detalle, reporte y cancelación.

No se crea ni activa ninguna promoción automáticamente al migrar.
No se modificó la base de datos real.

## Verificación automatizada

`php vendor/phpunit/phpunit/phpunit tests/Unit/BundleCalculatorTest.php tests/Feature/PromotionsTest.php`

PHPUnit está configurado para SQLite en memoria de forma forzada, evitando que
RefreshDatabase opere sobre la base de datos habitual. Estas pruebas no sustituyen
una prueba de concurrencia sobre el motor de base de datos del servidor.
