# Orden de migraciones

Las migraciones usan un prefijo numérico consecutivo (`001_` a `042_`). Ese
prefijo es el orden autoritativo de ejecución; la fecha que le sigue se conserva
para identificar la migración histórica de origen.

La migración `001_0000_00_00_000000_create_legacy_core_tables.php` prepara las
tablas base (`users`, `vehicles` y `parts`) de las que dependen migraciones
históricas. Las migraciones de creación posteriores completan las tablas que no
existan y evitan volver a crear estructuras presentes.

Las comprobaciones de metadatos evitan `Schema::hasColumn()` y consultan solo los
campos disponibles en MySQL 5.6. El atributo JSON de características se almacena
como `TEXT` para conservar compatibilidad con ese servidor; los casts de Laravel
continúan serializándolo como JSON.

Las migraciones renombradas comprueban las tablas, columnas e índices relevantes
antes de crearlos. Esto permite registrar los nombres nuevos en una instalación
que ya tenga el esquema anterior y también ejecutar una instalación limpia:

```bash
php artisan migrate
php artisan migrate:fresh
```

Las migraciones nuevas deben agregarse con el siguiente prefijo consecutivo y
colocarse después de todas sus dependencias.
