# API externa de ChuspaSocial

Este documento describe los servicios web externos (AJAX) que el plugin `local_chuspasocial` registra en `src/db/services.php`. Las plantillas Mustache y el JavaScript del plugin los consumen sin recargar la página.

## Convenciones generales

- Todos los servicios están marcados con `ajax => true`, por lo que se invocan desde JavaScript con el módulo `core/ajax`.
- El nombre de cada función es el valor que se pasa como `methodname`.
- Los tipos `PARAM_*` son los que Moodle usa para validar y limpiar cada parámetro.
- Los parámetros con valor por defecto son opcionales. Los demás son obligatorios.
- Cada servicio valida el contexto y comprueba una capacidad antes de ejecutarse. Si falta la capacidad, Moodle lanza `required_capability_exception`.
- Si un parámetro no es válido, el servicio lanza `invalid_parameter_exception`.
- Los servicios de tipo `read` solo consultan datos. Los de tipo `write` modifican datos.
- Las respuestas se devuelven en JSON.

Ejemplo de llamada desde JavaScript:

```javascript
import Ajax from 'core/ajax';

const [respuesta] = Ajax.call([{
    methodname: 'local_chuspasocial_toggle_reaction',
    args: {postid: 12},
}]);

respuesta.then((datos) => console.log(datos.reacted));
```

## Resumen de servicios

| Función | Tipo | Capacidad | Descripción |
| --- | --- | --- | --- |
| `local_chuspasocial_get_posts` | read | `local/chuspasocial:view` | Devuelve una página de publicaciones de un muro |
| `local_chuspasocial_add_comment` | write | `local/chuspasocial:comment` | Agrega un comentario a una publicación |
| `local_chuspasocial_toggle_reaction` | write | `local/chuspasocial:react` | Alterna la reacción del usuario a una publicación |
| `local_chuspasocial_get_listings` | read | `local/chuspasocial:view` | Devuelve anuncios de libros de mis materias o que coinciden con una búsqueda |
| `local_chuspasocial_toggle_follow` | write | `local/chuspasocial:follow` | Sigue o deja de seguir a un usuario o a un tag |
| `local_chuspasocial_report_item` | write | `local/chuspasocial:report` | Reporta una publicación, un comentario o un anuncio para moderación |

## Publicaciones

### local_chuspasocial_get_posts

Devuelve una página de publicaciones del muro general o del muro de una materia. Las publicaciones ocultas no se devuelven.

Tipo: `read`. Capacidad: `local/chuspasocial:view`, comprobada en el contexto del curso si `courseid` es mayor que 0 y en el contexto del sistema si es 0.

Parámetros de entrada:

| Parámetro | Tipo | Valor por defecto | Descripción |
| --- | --- | --- | --- |
| `courseid` | `PARAM_INT` | 0 | Id del curso. Con 0 se obtiene el muro general. |
| `page` | `PARAM_INT` | 0 | Número de página, empezando en 0. |
| `perpage` | `PARAM_INT` | 20 | Publicaciones por página, de 1 a 50. |

Lanza `invalid_parameter_exception` si `page` es menor que 0 o si `perpage` no está entre 1 y 50.

Estructura de la respuesta: un objeto con la clave `posts`, que es una lista. Cada elemento tiene estos campos:

| Campo | Tipo | Descripción |
| --- | --- | --- |
| `id` | `PARAM_INT` | Id de la publicación. |
| `userid` | `PARAM_INT` | Id del autor. |
| `content` | `PARAM_RAW` | Contenido de la publicación, ya formateado con `format_text`. |
| `contentformat` | `PARAM_INT` | Formato del contenido. |
| `timecreated` | `PARAM_INT` | Fecha de creación (timestamp). |
| `authorfullname` | `PARAM_TEXT` | Nombre completo del autor. |
| `authorpictureurl` | `PARAM_URL` | URL de la foto de perfil del autor. |
| `canedit` | `PARAM_BOOL` | Indica si el usuario actual puede editar la publicación (solo el autor). |
| `candelete` | `PARAM_BOOL` | Indica si el usuario actual puede eliminarla (el autor o un moderador). |
| `images` | lista | Imágenes de la publicación. Cada una tiene `url` (`PARAM_URL`) y `filename` (`PARAM_FILE`). |
| `tags` | lista | Etiquetas de la publicación. Cada una tiene `name` (`PARAM_TEXT`) y `url` (`PARAM_URL`), que apunta a `tag.php`. |
| `reactions` | `PARAM_INT` | Número de reacciones de la publicación. |
| `hasreacted` | `PARAM_BOOL` | Indica si el usuario actual ya reaccionó. |

Ejemplo de respuesta (valores ilustrativos):

```json
{
    "posts": [
        {
            "id": 15,
            "userid": 4,
            "content": "<p>Hola a todos</p>",
            "contentformat": 1,
            "timecreated": 1762900000,
            "authorfullname": "Ana Pérez",
            "authorpictureurl": "https://ejemplo.edu/pluginfile.php/5/user/icon/f2",
            "canedit": true,
            "candelete": true,
            "images": [],
            "tags": [
                {"name": "examen", "url": "https://ejemplo.edu/local/chuspasocial/tag.php?tag=examen"}
            ],
            "reactions": 3,
            "hasreacted": false
        }
    ]
}
```

## Comentarios

### local_chuspasocial_add_comment

Agrega un comentario a una publicación.

Tipo: `write`. Capacidad: `local/chuspasocial:comment`, comprobada en el contexto del curso de la publicación o en el del sistema si es del muro general.

Parámetros de entrada:

| Parámetro | Tipo | Obligatorio | Descripción |
| --- | --- | --- | --- |
| `postid` | `PARAM_INT` | Sí | Id de la publicación. |
| `content` | `PARAM_RAW_TRIMMED` | Sí | Contenido del comentario. |

Errores:

- Si la publicación no existe, la consulta falla con `dml_missing_record_exception`.
- Si la publicación está oculta, lanza `invalid_parameter_exception`.

Estructura de la respuesta: un objeto con el comentario creado.

| Campo | Tipo | Descripción |
| --- | --- | --- |
| `id` | `PARAM_INT` | Id del comentario. |
| `postid` | `PARAM_INT` | Id de la publicación. |
| `userid` | `PARAM_INT` | Id del autor del comentario. |
| `content` | `PARAM_RAW` | Contenido del comentario. |
| `contentformat` | `PARAM_INT` | Formato del contenido. |
| `timecreated` | `PARAM_INT` | Fecha de creación (timestamp). |

Ejemplo de respuesta (valores ilustrativos):

```json
{
    "id": 8,
    "postid": 15,
    "userid": 4,
    "content": "Gracias por compartirlo",
    "contentformat": 2,
    "timecreated": 1762900500
}
```

## Reacciones

### local_chuspasocial_toggle_reaction

Alterna la reacción del usuario actual a una publicación: la crea si no existe y la quita si ya existe.

Tipo: `write`. Capacidad: `local/chuspasocial:react`, comprobada en el contexto del curso de la publicación o en el del sistema si es del muro general.

Parámetros de entrada:

| Parámetro | Tipo | Valor por defecto | Descripción |
| --- | --- | --- | --- |
| `postid` | `PARAM_INT` | Obligatorio | Id de la publicación. |
| `reactiontype` | `PARAM_RAW` | `like` | Tipo de reacción: `like`, `celebrate` o `insightful`. |

Errores:

- Si `reactiontype` no es uno de los tres tipos reconocidos, lanza `invalid_parameter_exception`.
- El servicio actual solo procesa `like`. Con `celebrate` o `insightful` lanza `moodle_exception` con el código `notavailable`.
- Si la publicación no existe, la consulta falla con `dml_missing_record_exception`.
- Si la publicación está oculta, lanza `invalid_parameter_exception`.

Estructura de la respuesta:

| Campo | Tipo | Descripción |
| --- | --- | --- |
| `reacted` | `PARAM_BOOL` | Indica si el usuario actual reaccionó a la publicación. |

Ejemplo de respuesta:

```json
{
    "reacted": true
}
```

## Mercado

### local_chuspasocial_get_listings

Devuelve los anuncios de libros de mis materias o los que coinciden con una búsqueda por título o autor.

Tipo: `read`. Capacidad: `local/chuspasocial:view`, comprobada en el contexto del sistema.

Parámetros de entrada:

| Parámetro | Tipo | Valor por defecto | Descripción |
| --- | --- | --- | --- |
| `search` | `PARAM_TEXT` | cadena vacía | Texto a buscar en el título o el autor. Vacío para obtener los anuncios de mis materias. |
| `page` | `PARAM_INT` | 0 | Número de página, empezando en 0. |
| `perpage` | `PARAM_INT` | `DEFAULT_LIMIT` de la clase | Anuncios por página, de 1 a 50. |
| `categoryid` | `PARAM_INT` | 0 | Id de la categoría de cursos. Con 0 no se filtra. |
| `limit` | `PARAM_INT` | `DEFAULT_LIMIT` de la clase | Máximo de anuncios a devolver, de 1 a 50. |
| `offset` | `PARAM_INT` | 0 | Cantidad de anuncios coincidentes que se omiten. |

Reglas de paginación:

- Si `limit` y `offset` tienen sus valores por defecto, se usan `page` y `perpage`.
- Si alguno de los dos cambia, se usan `limit` y `offset`.

Lanza `invalid_parameter_exception` en estos casos:

- `page` es menor que 0.
- `perpage` o `limit` no están entre 1 y 50.
- `categoryid` u `offset` son menores que 0.
- `page` es demasiado grande para el `perpage` indicado.

Estructura de la respuesta: un objeto con la clave `listings`, que es una lista. Cada elemento tiene estos campos:

| Campo | Tipo | Descripción |
| --- | --- | --- |
| `id` | `PARAM_INT` | Id del anuncio. |
| `userid` | `PARAM_INT` | Id del vendedor. |
| `title` | `PARAM_TEXT` | Título del libro. |
| `author` | `PARAM_TEXT` | Autor del libro. |
| `description` | `PARAM_RAW` | Descripción ya formateada. |
| `descriptionformat` | `PARAM_INT` | Formato de la descripción. |
| `price` | `PARAM_FLOAT` | Precio del libro. |
| `externalcontact` | `PARAM_TEXT` | Contacto externo del vendedor, vacío si no lo agregó. |
| `status` | `PARAM_ALPHA` | Estado del anuncio: `available` o `sold`. |
| `timecreated` | `PARAM_INT` | Fecha de creación (timestamp). |

Ejemplo de respuesta (valores ilustrativos):

```json
{
    "listings": [
        {
            "id": 3,
            "userid": 7,
            "title": "Estructuras de datos",
            "author": "Mark Allen Weiss",
            "description": "<p>Buen estado</p>",
            "descriptionformat": 1,
            "price": 120.5,
            "externalcontact": "",
            "status": "available",
            "timecreated": 1762800000
        }
    ]
}
```

## Seguimiento

### local_chuspasocial_toggle_follow

Sigue a un usuario o a un tag si el usuario actual todavía no lo sigue, y deja de seguirlo en caso contrario.

Tipo: `write`. Capacidad: `local/chuspasocial:follow`, comprobada en el contexto del sistema.

Parámetros de entrada:

| Parámetro | Tipo | Obligatorio | Descripción |
| --- | --- | --- | --- |
| `component` | `PARAM_ALPHA` | Sí | Tipo de elemento a seguir: `user` o `tag`. |
| `itemid` | `PARAM_INT` | Sí | Id del usuario o del tag. |

Errores:

- Si `component` no es `user` ni `tag`, lanza `invalid_parameter_exception`.
- Si se intenta seguir al propio usuario, lanza `invalid_parameter_exception`.
- Si el usuario no existe, está eliminado o suspendido, la consulta falla con `dml_missing_record_exception`.
- Si el tag no existe al intentar seguirlo, la consulta falla con `dml_missing_record_exception`.

Estructura de la respuesta:

| Campo | Tipo | Descripción |
| --- | --- | --- |
| `status` | `PARAM_BOOL` | Indica si la operación se realizó. |
| `action` | `PARAM_ALPHA` | Resultado de la operación: `followed` o `unfollowed`. |
| `message` | `PARAM_TEXT` | Texto que describe el nuevo estado. |

Ejemplo de respuesta (el texto de `message` depende del idioma):

```json
{
    "status": true,
    "action": "followed",
    "message": "Siguiendo"
}
```

## Reportes

### local_chuspasocial_report_item

Reporta una publicación, un comentario o un anuncio de libro para que lo revise un moderador.

Tipo: `write`. Capacidad: `local/chuspasocial:report`, comprobada en el contexto del sistema.

Parámetros de entrada:

| Parámetro | Tipo | Obligatorio | Descripción |
| --- | --- | --- | --- |
| `itemtype` | `PARAM_ALPHA` | Sí | Tipo de elemento: `post`, `comment` o `listing`. |
| `itemid` | `PARAM_INT` | Sí | Id del elemento reportado. |
| `reason` | `PARAM_RAW_TRIMMED` | Sí | Motivo del reporte. |

Errores:

- Si `itemtype` no es uno de los tres valores permitidos, lanza `invalid_parameter_exception`.
- Si `itemid` es 0 o negativo, lanza `invalid_parameter_exception`.
- Si el elemento no existe, lanza `invalid_parameter_exception`.
- Si el usuario no puede ver el elemento, lanza `moodle_exception` con el código `nopermissions`. Un comentario hereda la visibilidad de su publicación. Un anuncio debe estar vinculado a un curso al que el usuario tenga acceso.

Estructura de la respuesta:

| Campo | Tipo | Descripción |
| --- | --- | --- |
| `status` | `PARAM_BOOL` | Indica si el reporte fue creado. |

Ejemplo de respuesta:

```json
{
    "status": true
}
```
