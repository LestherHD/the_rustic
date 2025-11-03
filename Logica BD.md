## 🧩 TABLAS Y DESCRIPCIÓN GENERAL (CON CONEXIONES)
### 1. **platos**
> Tabla principal de platos o productos del menú.
| Campo | Descripción |
|--------|--------------|
| `id` | PK del plato |
| `nombre` | Nombre del plato |
| `descripcion` | Descripción larga |
| `precio_venta` | Precio sugerido al público |
| `costo_produccion` | Se calcula desde receta |
| `imagen` | URL/Path de imagen (guardada en /public/images/platos) |
| `categoria_id` | FK a categorías |
| `visible_en_menu` | Si se muestra o no al público |
| `activo` | Si está disponible |
**Relaciones:** Tiene 1️⃣:N muchas `recetas` / Pertenece a `categorias`
---
### 2. **categorias**
> Grupos de platos: Entradas, Pizzas, Postres...
| Campo | Descripción |
|--------|--------------|
| `id` | PK de categoría |
| `nombre` | Nombre: Entradas, Sopas, etc. |
| `visible_publico` | Si se muestra al público |
**Relaciones:** Tiene muchos `platos`
---
### 3. **recetas**
> Recetas de cada plato o subreceta.
| Campo | Descripción |
|--------|--------------|
| `id` | PK de la receta |
| `plato_id` | FK al plato (o nulo si es subreceta) |
| `nombre` | Nombre de la receta (ej. "Verdura Caldo") |
| `version` | Control de cambios |
| `observaciones` | Notas adicionales |
**Relaciones:** Tiene muchos `detalle_recetas` / Puede ser usada como `subreceta` en otros platos
---
### 4. **detalle_recetas**
> Ingredientes o subrecetas de una receta.
| Campo | Descripción |
|--------|--------------|
| `id` | PK |
| `receta_id` | FK a `recetas` |
| `ingrediente_id` | FK a `ingredientes` (si aplica) |
| `subreceta_id` | FK a `recetas` (si aplica) |
| `tipo` | `'ingrediente'` o `'subreceta'` |
| `cantidad` | Cantidad requerida |
**Relaciones:** Relación compuesta, tipo árbol 🌳 (para subrecetas)
---
### 5. **ingredientes**
> Materias primas (papa, sal, queso, etc.)
| Campo | Descripción |
|--------|--------------|
| `id` | PK |
| `nombre` | Nombre del ingrediente |
| `unidad_medida` | "onza", "unidad", "gramo", etc. |
| `stock_actual` | Inventario real |
| `stock_minimo` | Para alertas automáticas |
| `costo_unitario` | CMP actual de la unidad |
**Relaciones:** Usado en `detalle_recetas` / Movimiento registrado en `movimientos_inventario`
---
### 6. **movimientos_inventario**
> Registro de entradas y salidas de inventario.
| Campo | Descripción |
|--------|--------------|
| `id` | PK |
| `ingrediente_id` | FK a ingrediente |
| `tipo_movimiento` | `'entrada'`, `'salida'`, `'ajuste'` |
| `cantidad` | Cuánto se movió |
| `referencia_tipo` | `'pedido'`, `'compra'`, etc. |
| `referencia_id` | ID relacionado |
| `comentario` | Observación |
**Relaciones:** Apunta a `ingredientes` / Usado por triggers o eventos de pedido
---
### 7. **pedidos**
> Pedido completo de una mesa, cliente, o grupo.
| Campo | Descripción |
|--------|--------------|
| `id` | PK |
| `mesa_id` | FK a mesa |
| `usuario_id` | Mesero/a responsable |
| `estado` | `abierto`, `en_preparacion`, etc. |
| `total` | Total del pedido calculado |
**Relaciones:** Tiene muchos `detalle_pedidos`
---
### 8. **detalle_pedidos**
> Línea del pedido (1 plato x cantidad)
| Campo | Descripción |
|--------|--------------|
| `id` | PK |
| `pedido_id` | FK a `pedidos` |
| `plato_id` | FK a `platos` |
| `cantidad` | Cuántas porciones |
| `precio_unitario` | Precio de ese momento |
**Relaciones:** Cada registro activa el trigger de manufactura e inventario / Conecta con recetas → ingredientes
---
### 9. **mesas**
> Mapa visual y control de estados
| Campo | Descripción |
|--------|--------------|
| `id` | PK |
| `nombre` | "Mesa 1", "Terraza", etc. |
| `pos_x` | Coordenada en mapa |
| `pos_y` | Coordenada en mapa |
| `estado` | `libre`, `ocupada`, etc. |
**Relaciones:** Se usa en `pedidos`
---
### 10. **usuarios**
> Meseros, admins, cocineros (vía Filament roles)
| Campo | Descripción |
|--------|--------------|
| `id` | PK |
| `name` | Nombre completo |
| `email` | Login email |
| `password` | Hash bcrypt |
| `role` | `admin`, `mesero`, etc. |
**Relaciones:** Se usa en `pedidos`
---
## 🧾 Todas las tablas seguirán este patrón Laravel:
```php
$table->id();
$table->timestamps();
$table->softDeletes();




platos ───┬──── recetas ───── detalle_recetas ───── ingredientes
          │                             └──────→ subrecetas (otras recetas)
          └──── detalle_pedidos ────── pedidos ───── mesas
                                             └──── usuarios (meseros)
ingredientes ───── movimientos_inventario
