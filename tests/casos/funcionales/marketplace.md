# Casos de Prueba Funcionales — Marketplace

## Objetivo

Documentar los casos de prueba manuales del Marketplace para verificar el cumplimiento de los requerimientos funcionales **RF-034 a RF-040**.

## Casos de prueba

### CP-MKT-001 — Ver los libros de mis materias

- **RF relacionado:** RF-034 — Ver los libros de mis materias.
- **Precondición:** El usuario inició sesión, está inscrito en una materia con libros publicados y existen anuncios asociados a otras materias.
- **Pasos:**
  1. Acceder al Marketplace.
  2. Revisar los libros disponibles.
  3. Comparar las materias asociadas a los anuncios con las materias en las que está inscrito el usuario.
- **Resultado esperado:** Se muestran únicamente los libros asociados a las materias en las que está inscrito el usuario.

### CP-MKT-002 — No hay libros disponibles para las materias del usuario

- **RF relacionado:** RF-034 — Ver los libros de mis materias.
- **Precondición:** El usuario inició sesión, pero no está inscrito en ninguna materia que tenga libros publicados.
- **Pasos:**
  1. Acceder al Marketplace.
  2. Revisar el contenido mostrado.
- **Resultado esperado:** El sistema muestra un mensaje indicando que no hay libros disponibles para las materias del usuario.

### CP-MKT-003 — Buscar un libro por título

- **RF relacionado:** RF-035 — Buscar libros por título o autor.
- **Precondición:** Existe al menos un libro publicado cuyo título se conoce.
- **Pasos:**
  1. Acceder al Marketplace.
  2. Introducir el título de un libro existente en el campo de búsqueda.
  3. Ejecutar la búsqueda.
- **Resultado esperado:** El sistema muestra el libro correspondiente al título buscado.

### CP-MKT-004 — Buscar libros por autor

- **RF relacionado:** RF-035 — Buscar libros por título o autor.
- **Precondición:** Existen dos o más libros publicados por el mismo autor.
- **Pasos:**
  1. Acceder al Marketplace.
  2. Introducir el nombre de un autor registrado.
  3. Ejecutar la búsqueda.
- **Resultado esperado:** El sistema muestra todos los libros publicados que coinciden con el autor buscado.

### CP-MKT-005 — Contactar al vendedor mediante la mensajería de Moodle

- **RF relacionado:** RF-036 — Contactar al vendedor por la mensajería de Moodle.
- **Precondición:** El comprador inició sesión y existe un anuncio publicado por otro usuario.
- **Pasos:**
  1. Acceder al Marketplace.
  2. Abrir el anuncio de otro usuario.
  3. Seleccionar la opción **Contactar**.
  4. Enviar un mensaje al vendedor.
  5. Verificar que el mensaje llegue a la bandeja de mensajería del vendedor.
- **Resultado esperado:** El sistema permite iniciar una conversación mediante la mensajería de Moodle y el vendedor recibe el mensaje enviado.

### CP-MKT-006 — Mostrar un contacto externo opcional

- **RF relacionado:** RF-037 — Mostrar un contacto externo opcional.
- **Precondición:** Existen dos anuncios: uno con un contacto externo registrado por el vendedor y otro sin contacto externo.
- **Pasos:**
  1. Acceder al Marketplace.
  2. Abrir el anuncio que contiene un contacto externo.
  3. Verificar que se muestre el contacto y la advertencia correspondiente.
  4. Abrir el anuncio que no contiene un contacto externo.
- **Resultado esperado:** El primer anuncio muestra el contacto externo y una advertencia que indica que la comunicación fuera de Moodle es responsabilidad de ambas partes. El segundo no muestra un contacto externo y ofrece únicamente la mensajería de Moodle.

### CP-MKT-007 — Marcar un libro como vendido

- **RF relacionado:** RF-038 — Marcar un libro como vendido.
- **Precondición:** El usuario inició sesión y tiene un anuncio propio publicado como disponible.
- **Pasos:**
  1. Acceder al anuncio propio.
  2. Seleccionar la opción para marcar el libro como vendido.
  3. Confirmar la operación si el sistema lo solicita.
  4. Revisar el estado del anuncio.
  5. Consultar los libros disponibles en el Marketplace.
- **Resultado esperado:** El anuncio cambia al estado vendido y deja de aparecer entre los libros disponibles.

### CP-MKT-008 — Editar y eliminar un anuncio propio

- **RF relacionado:** RF-039 — Editar o eliminar un anuncio propio.
- **Precondición:** El usuario inició sesión y tiene un anuncio propio publicado.
- **Pasos:**
  1. Acceder al anuncio propio.
  2. Seleccionar la opción para editarlo.
  3. Modificar uno o más datos del anuncio y guardar los cambios.
  4. Verificar que los datos modificados se hayan guardado y que el propietario siga siendo el mismo.
  5. Seleccionar la opción para eliminar el anuncio.
  6. Confirmar la eliminación.
  7. Revisar el Marketplace para comprobar que el anuncio ya no esté disponible.
- **Resultado esperado:** Los cambios se guardan correctamente sin modificar el propietario del anuncio. Después de confirmar la eliminación, el anuncio se elimina y deja de mostrarse en el Marketplace.

### CP-MKT-009 — Subir fotografías respetando el límite permitido

- **RF relacionado:** RF-040 — Subir fotos del libro.
- **Precondición:** El usuario inició sesión y puede crear o editar un anuncio. Se conoce el límite máximo de fotografías permitido por anuncio.
- **Pasos:**
  1. Acceder al formulario para crear o editar un anuncio.
  2. Seleccionar una cantidad de fotografías que no supere el límite permitido.
  3. Guardar el anuncio.
  4. Abrir el detalle del anuncio y verificar que se muestren las fotografías.
  5. Intentar agregar fotografías hasta superar el límite permitido.
- **Resultado esperado:** El sistema guarda y muestra las fotografías cuando no se supera el límite. Al intentar excederlo, rechaza las fotografías adicionales y muestra un mensaje que indica el límite permitido.

### CP-MKT-010 — Buscar libros por coincidencia parcial de título

- **RF relacionado:** RF-035 — Buscar libros por título o autor.
- **Precondición:** El usuario inició sesión y existe un libro publicado con el título "Introducción a la Física".
- **Pasos:**
  1. Acceder al Marketplace.
  2. Buscar la palabra clave `Física`.
  3. Verificar los resultados obtenidos.
  4. Repetir la búsqueda con `física` en minúsculas.
  5. Repetir la búsqueda con el texto parcial `Fís`.
  6. Buscar una palabra clave que no coincida con ningún título.
- **Resultado esperado:**
  - El libro "Introducción a la Física" aparece al buscar `Física`, `física` y `Fís`.
  - La búsqueda permite coincidencias parciales del título.
  - Las mayúsculas y minúsculas no afectan los resultados.
  - Una búsqueda sin coincidencias no devuelve libros incorrectos.
- **Criterio de éxito:** Todas las búsquedas con coincidencias parciales devuelven los títulos correspondientes, independientemente de las mayúsculas o minúsculas.

## Registro de ejecución

| ID del caso | Resultado obtenido | Estado | Observaciones |
| --- | --- | --- | --- |
| CP-MKT-001 | Pendiente de ejecución | Pendiente | |
| CP-MKT-002 | Pendiente de ejecución | Pendiente | |
| CP-MKT-003 | Pendiente de ejecución | Pendiente | |
| CP-MKT-004 | Pendiente de ejecución | Pendiente | |
| CP-MKT-005 | Pendiente de ejecución | Pendiente | |
| CP-MKT-006 | Pendiente de ejecución | Pendiente | |
| CP-MKT-007 | Pendiente de ejecución | Pendiente | |
| CP-MKT-008 | Pendiente de ejecución | Pendiente | |
| CP-MKT-009 | Pendiente de ejecución | Pendiente | |
| CP-MKT-010 | Pendiente de ejecución | Pendiente | |
