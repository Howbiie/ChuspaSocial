# Revisión de SQL: concatenación de datos del usuario

**Fecha:** 2026-10-18
**Rama:** security/audit-sql
**Alcance:** todos los archivos PHP del plugin (`src/`)
**Método:** buscar las llamadas al gestor de base de datos de Moodle (`$DB`) y
comprobar que todo dato variable viaja como parámetro (placeholder), nunca
concatenado dentro de la sentencia SQL.

## Resumen

- Archivos PHP revisados: 23.
- Llamadas al gestor de base de datos encontradas: 6 (dos `get_records_sql`,
  un `get_record` y tres `delete_records`).
- Llamadas SQL que concatenan datos del usuario: 0.
- Problema encontrado: ninguno.

## Consultas encontradas

| Archivo | Línea | Consulta | Estado |
| --- | --- | --- | --- |
| src/classes/local/listing_service.php | 56 | `get_records_sql` | OK: usa los parámetros nombrados `:title` y `:author`. |
| src/classes/local/listing_service.php | 90 | `get_records_sql` | OK: usa el parámetro nombrado `:status` y la lista `:courseN` que genera `get_in_or_equal`. |
| src/classes/local/post_service.php | 62 | `get_record` | OK: la condición va como array (`['id' => $postid]`). |
| src/classes/local/post_service.php | 103 | `delete_records` | OK: la condición va como array (`['postid' => $postid]`). |
| src/classes/local/post_service.php | 109 | `delete_records` | OK: la condición va como array (`['postid' => $postid]`). |
| src/classes/local/post_service.php | 123 | `delete_records` | OK: la condición va como array (`['id' => $postid]`). |

Las siguientes líneas construyen las condiciones sin concatenar datos: los
comodines del `LIKE` se arman con `sql_like` y `sql_like_escape`
(src/classes/local/listing_service.php, líneas 46-48) y la lista de cursos con
`get_in_or_equal` (línea 76).

## Archivos revisados sin consultas SQL

- src/classes/event/comment_created.php
- src/classes/event/listing_created.php
- src/classes/event/post_created.php
- src/classes/event/post_deleted.php
- src/classes/event/reaction_added.php
- src/classes/form/listing_form.php
- src/classes/form/post_form.php
- src/classes/local/hook_callbacks.php
- src/db/access.php
- src/db/events.php
- src/db/messages.php
- src/db/services.php
- src/db/tag.php
- src/index.php
- src/lang/en/local_chuspasocial.php
- src/lang/es/local_chuspasocial.php
- src/settings.php
- src/tests/lib_test.php
- src/tests/local/persistent/report_test.php
- src/tests/local/post_service_test.php
- src/version.php

## Uso obligatorio de placeholders nombrados

Queda prohibida la concatenación o interpolación directa de variables en consultas SQL,
incluidos los métodos DML de Moodle, aunque los valores hayan sido validados.
En consultas con `get_records_sql`, los valores deben enviarse mediante
placeholders nombrados (`:param`) y un array asociativo de parámetros.

### Ejemplo incorrecto

```php
$sql = "SELECT * FROM {local_chuspasocial_post} WHERE userid = " . $userid;
$posts = $DB->get_records_sql($sql);
```

La concatenación directa de variables está prohibida. También está prohibida
la interpolación de variables dentro de cadenas SQL.

### Ejemplo correcto

```php
$sql = "SELECT * FROM {local_chuspasocial_post} WHERE userid = :userid";
$params = ["userid" => $userid];
$posts = $DB->get_records_sql($sql, $params);
```

El valor de `:userid` se transmite por separado mediante el array `$params`.
Cada placeholder debe tener una entrada correspondiente en el array.

Para condiciones `IN` dinámicas, utilizar `$DB->get_in_or_equal()`
y vincular los parámetros resultantes, sin concatenar valores.

## Conclusión

No se encontró ninguna consulta que concatene datos del usuario. Todas las
llamadas al gestor de base de datos usan parámetros o condiciones por array, por
lo que no hace falta abrir ningún issue de seguridad nuevo.
