# Catálogo de eventos de auditoría y observadores de Moodle

## Objetivo

Este documento describe los cinco eventos de auditoría que emite el plugin `local_chuspasocial`, los datos que entregan a la API de eventos de Moodle y la forma en que otros plugins pueden suscribirse a ellos mediante observadores (*observers*).

Los eventos se encuentran en `classes/event/` (en el repositorio, `src/classes/event/`) y extienden `\core\event\base`. Los servicios los crean con `::create([...])` y los emiten mediante `->trigger()` después de guardar o eliminar los registros correspondientes.

## 1. Catálogo de eventos

| Evento (clase en `local_chuspasocial\event`) | Acción auditada | `crud` | `objecttable` | Servicio emisor |
| --- | --- | --- | --- | --- |
| `post_created` | Creación de una publicación | `c` | `local_chuspasocial_post` | `post_service::create_post()` |
| `post_deleted` | Eliminación de una publicación | `d` | `local_chuspasocial_post` | `post_service::delete_post()` |
| `comment_created` | Creación de un comentario | `c` | `local_chuspasocial_comment` | `comment_service::add_comment()` |
| `reaction_added` | Adición de una reacción | `c` | `local_chuspasocial_reaction` | `reaction_service` |
| `listing_created` | Creación de un anuncio del marketplace | `c` | `local_chuspasocial_listing` | `listing_service::create_listing()` |

Todos los eventos declaran `edulevel = \core\event\base::LEVEL_PARTICIPATING` y obtienen su nombre localizado mediante `get_string(...)` en el componente `local_chuspasocial`.

## 2. Estructura común del payload

Los servicios suministran los siguientes campos a `::create()`:

- **`objectid`**: identificador del registro creado o eliminado en `objecttable`.
- **`context`**: objeto de contexto de Moodle asociado con la operación. No debe confundirse con un ID numérico.
- **`userid`**: ID del usuario que realizó la acción (`$USER->id`).
- **`other`**: datos adicionales específicos del evento, cuando se proporcionan.

Además, las clases de evento definen **`crud`**, **`edulevel`** y **`objecttable`** en `init()`. Moodle incorpora metadatos de evento como el nombre de clase y el instante de emisión; los observadores pueden acceder a la información a través del objeto del evento y de `get_data()`.

> **Importante:** Los ejemplos siguientes muestran los campos suministrados explícitamente por los servicios. No son volcados completos de un evento real: Moodle completa otros campos del registro de evento. Los IDs son ilustrativos.

### 2.1. `post_created`

**Clase:** `\local_chuspasocial\event\post_created`
**Origen:** `post_service::create_post()`
**Tabla:** `local_chuspasocial_post`
**CRUD:** `c`

```php
\local_chuspasocial\event\post_created::create([
    'objectid' => $post->get('id'),
    'context' => $context,
    'userid' => $USER->id,
])->trigger();
```

`objectid` identifica la nueva publicación. No se pasa un arreglo `other`. La descripción del evento menciona el usuario y la publicación.

### 2.2. `post_deleted`

**Clase:** `\local_chuspasocial\event\post_deleted`
**Origen:** `post_service::delete_post()`
**Tabla:** `local_chuspasocial_post`
**CRUD:** `d`

```php
$event = \local_chuspasocial\event\post_deleted::create([
    'objectid' => $postid,
    'context' => $context,
    'userid' => $USER->id,
]);
// El servicio confirma la transacción y después emite el evento.
$event->trigger();
```

`objectid` conserva el ID de la publicación eliminada. El servicio emite el evento después de confirmar la transacción de eliminación. La descripción del evento también utiliza `courseid`, disponible como dato del evento de Moodle; el servicio no lo pasa explícitamente a `::create()`.

### 2.3. `comment_created`

**Clase:** `\local_chuspasocial\event\comment_created`
**Origen:** `comment_service::add_comment()`
**Tabla:** `local_chuspasocial_comment`
**CRUD:** `c`

```php
\local_chuspasocial\event\comment_created::create([
    'objectid' => $comment->get('id'),
    'context' => $context,
    'userid' => (int) $USER->id,
    'other' => ['postid' => $postid],
])->trigger();
```

`objectid` identifica el comentario y **`other['postid']`** identifica la publicación comentada. La descripción incorpora `postid` cuando está presente. El servicio dispara el evento después de guardar el comentario.

### 2.4. `reaction_added`

**Clase:** `\local_chuspasocial\event\reaction_added`
**Origen:** `reaction_service`
**Tabla:** `local_chuspasocial_reaction`
**CRUD:** `c`

```php
\local_chuspasocial\event\reaction_added::create([
    'objectid' => $reactionid,
    'context' => $context,
    'userid' => (int) $USER->id,
    'other' => ['reactiontype' => $record->type],
])->trigger();
```

`objectid` identifica la reacción y **`other['reactiontype']`** contiene su tipo (el servicio utiliza `reaction::TYPE_LIKE`). La clase valida que exista `other['reactiontype']` y lanza `\coding_exception` si falta. El ID de la publicación reaccionada no se incluye explícitamente en `other`; si un observador lo necesita, deberá consultarlo a partir de la reacción, sujeto a permisos y disponibilidad.

### 2.5. `listing_created`

**Clase:** `\local_chuspasocial\event\listing_created`
**Origen:** `listing_service::create_listing()`
**Tabla:** `local_chuspasocial_listing`
**CRUD:** `c`

```php
\local_chuspasocial\event\listing_created::create([
    'objectid' => (int) $listing->get('id'),
    'context' => \context_system::instance(),
    'userid' => (int) $USER->id,
])->trigger();
```

Este evento utiliza explícitamente el **contexto del sistema**. Su método `validate_data()` exige un `objectid` no vacío y `get_url()` devuelve la dirección `/local/chuspasocial/listing.php?id=<objectid>`. No se suministra `other`.

## 3. Cómo suscribir observadores externos

Un observador externo se registra **en el plugin que desea escuchar el evento**, no en el archivo de eventos de ChuspaSocial. Por ejemplo, un plugin local denominado hipotéticamente `local_auditoriaexterna` puede escuchar `post_created`.

### 3.1. Declarar la suscripción

En el plugin externo, crear o editar `local/auditoriaexterna/db/events.php`:

```php
<?php
// Archivo del plugin externo local_auditoriaexterna.
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => \local_chuspasocial\event\post_created::class,
        'callback' => '\\local_auditoriaexterna\\observer::post_created',
    ],
];
```

Para escuchar otro evento, sustituir `eventname` por la clase correspondiente del catálogo. Se pueden añadir varias entradas al arreglo `$observers`.

### 3.2. Implementar el callback

En `local/auditoriaexterna/classes/observer.php`:

```php
<?php
namespace local_auditoriaexterna;

class observer {
    public static function post_created(\local_chuspasocial\event\post_created $event): void {
        $data = $event->get_data();
        $postid = $data['objectid'];
        $userid = $data['userid'];
        // Registrar o procesar los datos según la política del plugin externo.
    }
}
```

El nombre de clase, la ruta y el callback deben coincidir. El ejemplo es ilustrativo: **no forma parte de ChuspaSocial** y no requiere crear esos archivos para este issue.

### 3.3. Activar y comprobar

1. Instalar o actualizar el plugin externo para que Moodle registre su archivo `db/events.php` (según el proceso habitual de actualización de plugins).
2. Purgar las cachés de Moodle si es necesario.
3. Ejecutar la acción correspondiente en ChuspaSocial (por ejemplo, crear una publicación).
4. Comprobar que el callback externo recibe el evento y que el registro de Moodle muestra la actividad, según la configuración de registros del sitio.

**Precauciones:** el observador debe validar los datos que necesita, respetar permisos y privacidad, evitar trabajo pesado durante la solicitud y no asumir que el registro original aún existe (especialmente en `post_deleted`). No debe depender de claves `other` que el emisor no proporciona.

## 4. Observadores ya definidos por ChuspaSocial

El archivo `src/db/events.php` del propio plugin **escucha** actualmente dos eventos del núcleo de Moodle:

| Evento observado | Callback |
| --- | --- |
| `\core\event\user_graded` | `\local_chuspasocial\local\observer::user_graded` |
| `\core\event\course_module_created` | `\local_chuspasocial\local\observer::assign_created` |

Estos son **eventos entrantes** para ChuspaSocial y no deben confundirse con los cinco **eventos emitidos** que se documentan en este catálogo. No es necesario editar `src/db/events.php` para permitir que otro plugin escuche los eventos de ChuspaSocial.

## 5. Referencias en el código

- Definiciones de eventos: `src/classes/event/post_created.php`, `post_deleted.php`, `comment_created.php`, `reaction_added.php` y `listing_created.php`.
- Emisión de eventos: `src/classes/local/post_service.php`, `comment_service.php`, `reaction_service.php` y `listing_service.php`.
- Observadores propios del plugin: `src/db/events.php`.

Este documento corresponde al estado del código revisado para el issue **#1168**. Cualquier cambio futuro en los campos enviados por los servicios debe reflejarse aquí.
