# Etiquetas de productos (para ti y tu compañero)

## Qué se agregó

- Columna `productos.etiquetas` (texto, ej. `peluche,stich,regalo,infantil`)
- Relleno automático de los **90 productos** del catálogo actual
- El chatbot busca también en `etiquetas`
- El admin API acepta el campo `etiquetas` al crear/editar

## Opción A — Laravel (recomendada)

```powershell
cd E:\...\EstiloDoradoLaravel
git pull origin main
php artisan migrate
```

Luego en phpMyAdmin → `productos` → deberías ver la columna **etiquetas**.

## Opción B — Solo phpMyAdmin (sin artisan)

1. Abre phpMyAdmin → base `estilo_dorado_bd`
2. Pestaña **SQL**
3. Carga el archivo `database/sql/etiquetas_productos.sql`  
   o pega su contenido y ejecuta
4. Si dice que la columna ya existe, comenta la línea `ALTER TABLE...` y ejecuta solo los `UPDATE`

## Exportar para tu compañero

En phpMyAdmin:

1. Selecciona la base `estilo_dorado_bd` (o solo la tabla `productos`)
2. **Exportar** → método **Rápido** → SQL → Continuar
3. Guarda el `.sql` y envíaselo por Drive/WhatsApp

O exporta solo:

```sql
SELECT id_producto, nombre, descripcion, etiquetas, id_categoria, precio_venta, stock
FROM productos
ORDER BY id_producto;
```

(Exportar como CSV también sirve para que revise en Excel.)

## Qué puede revisar tu compañero

- Corregir o ampliar etiquetas (ej. agregar jerga de la tienda)
- Mejorar descripciones si quieren
- No borrar la columna; solo editar el texto de `etiquetas`

Formato: palabras en minúsculas separadas por coma, sin espacios raros:

```text
peluche,cerdita,tiburon,regalo,infantil
```
