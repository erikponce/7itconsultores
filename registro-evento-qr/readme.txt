=== Registro de Evento con QR ===
Contributors: Jules (Tu WordPress.org user ID aquí si lo publicas)
Donate link: https://example.com/ (Tu enlace de donación si tienes)
Tags: registration, event, qr code, shortcode, email, event management, rsvp
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: registro-evento-qr
Domain Path: /languages

Permite el registro de personas para un evento, genera un código QR de confirmación y lo envía por e-mail. Incluye panel de administración y página de validación para organizadores.

== Description ==

Este plugin de WordPress facilita la gestión de registros para eventos. Características principales:

*   **Formulario de Registro Sencillo**: Los usuarios pueden registrarse proporcionando Nombre, E-mail, Empresa, Puesto y Teléfono.
*   **Generación de Código QR**: Se genera un código QR único para cada asistente registrado.
*   **Confirmación por Email**: Los asistentes reciben un correo electrónico de confirmación con su código QR y los detalles del registro.
*   **Shortcode Fácil de Usar**: Integra el formulario de registro en cualquier página o entrada con el shortcode `[formulario_registro_evento]`.
*   **Panel de Administración Completo**:
    *   **Listado de Registros**: Visualiza, busca (próximamente) y gestiona todos los asistentes. Reenvía correos de confirmación o elimina registros individualmente o en masa.
    *   **Configuración Personalizable**: Adapta el plugin a tu evento:
        *   Sube el logo de tu evento.
        *   Personaliza el contenido del correo de confirmación usando un editor visual y placeholders (`{nombre_usuario}`, `{qr_code_image_tag}`, etc.).
    *   **Validación de QR en Admin**: Una sección dentro del panel de administración para validar QRs mediante escaneo con cámara o ingreso manual de la clave.
*   **Página de Validación para Organizadores (Frontend)**:
    *   Crea una página dedicada para que los organizadores validen los QRs de los asistentes el día del evento.
    *   Utiliza la plantilla de página "Página de Validación de QR" o el slug `validar-registro`.
    *   Acceso restringido a usuarios con permisos (por defecto, rol Editor o superior).
    *   Permite validación por escaneo de QR con la cámara del dispositivo o por ingreso manual de la clave del QR.
    *   Resultados de validación en tiempo real mediante AJAX.

== Installation ==

1.  **Requisitos Previos (Importante):**
    *   **Librería PHP QR Code**: Descarga la librería `phpqrcode` (puedes encontrarla buscando "PHP QR Code library" en SourceForge o GitHub). Crea una carpeta llamada `libs` dentro de `registro-evento-qr/includes/`, y dentro de `libs` crea otra carpeta llamada `phpqrcode`. Coloca el archivo `qrlib.php` y el resto de archivos de la librería `phpqrcode` dentro de `registro-evento-qr/includes/libs/phpqrcode/`. **El plugin NO funcionará sin esta librería para generar los QRs.**
    *   **Librería JavaScript HTML5 QR Code (Opcional, para escaneo con cámara)**: Para la funcionalidad de escaneo con cámara, descarga `html5-qrcode.min.js` (desde GitHub, por ejemplo, del repositorio `mebjas/html5-qrcode`).
        *   Para la validación en el **panel de administración**: Crea la carpeta `registro-evento-qr/admin/js/libs/` y coloca `html5-qrcode.min.js` allí.
        *   Para la validación en la **página pública de organizadores**: Crea la carpeta `registro-evento-qr/public/js/libs/` y coloca `html5-qrcode.min.js` allí.
        *   Si estas librerías no están presentes, el escaneo con cámara no funcionará, pero la validación manual seguirá operativa.
2.  **Instalación del Plugin:**
    *   Sube la carpeta completa `registro-evento-qr` (con las librerías ya incluidas como se describe arriba) al directorio `/wp-content/plugins/` de tu instalación de WordPress. Puedes hacerlo mediante FTP o subiendo el archivo ZIP desde "Plugins" > "Añadir nuevo" > "Subir Plugin".
    *   Activa el plugin a través del menú 'Plugins' en WordPress.
3.  **Configuración Inicial:**
    *   Ve a "Registro Evento" > "Configuración" en el panel de administración de WordPress.
    *   Sube el logo de tu evento y personaliza el cuerpo del correo de confirmación según tus necesidades. Guarda los cambios.
4.  **Mostrar el Formulario de Registro:**
    *   Crea una nueva página o edita una existente (ej. "Registro al Evento X").
    *   Inserta el shortcode `[formulario_registro_evento]` en el contenido de la página donde deseas que aparezca el formulario. Publica o actualiza la página.
5.  **Crear la Página de Validación para Organizadores (Frontend):**
    *   Crea una nueva página en WordPress. Asígnale un título (ej. "Validación de Entradas del Evento").
    *   **Importante**: El slug (parte de la URL) de esta página DEBE SER `validar-registro` para que los QRs generados apunten correctamente a ella. Puedes editar el slug en la barra lateral del editor de páginas, bajo "Enlace permanente".
    *   En los "Atributos de Página" (o "Resumen" > "Plantilla" en el editor de bloques), selecciona la plantilla llamada "Página de Validación de QR".
    *   Publica la página. El acceso a esta página está restringido a usuarios logueados con el rol de "Editor" o superior por defecto.

== Frequently Asked Questions ==

= ¿Cómo inserto el formulario de registro? =
Simplemente usa el shortcode `[formulario_registro_evento]` en el editor de contenido de cualquier página o entrada donde quieras que aparezca.

= ¿Dónde configuro el logo y el correo de confirmación? =
En el panel de administración de WordPress, navega a "Registro Evento" > "Configuración". Allí encontrarás las opciones para subir tu logo y editar la plantilla del correo.

= ¿Quién puede acceder a la página de validación de QR de los organizadores? =
Por defecto, solo los usuarios logueados con el rol de "Editor" o "Administrador" pueden acceder a la página de validación de QR creada con la plantilla "Página de Validación de QR" (con slug `validar-registro`). Si necesitas que otros roles (ej. un rol personalizado "Organizador") tengan acceso, deberás usar un plugin de gestión de roles y capacidades para otorgarles la capacidad `edit_others_posts`, o modificar el chequeo de capacidad en el código del plugin (esto último no se recomienda para usuarios básicos ya que se perdería en actualizaciones).

= ¿Qué pasa si no instalo las librerías QR (phpqrcode, html5-qrcode.min.js)? =
*   **Sin `phpqrcode`**: La generación de imágenes QR fallará. Los usuarios se registrarán, pero no se generará el QR ni se enviará en el email. El plugin no será funcional para su propósito principal. **Esta librería es esencial y debe estar en `registro-evento-qr/includes/libs/phpqrcode/qrlib.php`**.
*   **Sin `html5-qrcode.min.js`**: La funcionalidad de *escaneo* de códigos QR con la cámara del dispositivo no estará disponible en las páginas de validación (tanto en el admin como en la pública). Sin embargo, la validación manual ingresando la clave del QR seguirá funcionando. Debes colocarla en `registro-evento-qr/admin/js/libs/` y/o `registro-evento-qr/public/js/libs/`.

= ¿Cómo puedo traducir el plugin? =
El plugin está preparado para la traducción y utiliza el text domain `registro-evento-qr`. Puedes usar un plugin como Loco Translate o Poedit para crear tus propios archivos de traducción (`.po` y `.mo`) a partir del archivo `registro-evento-qr/languages/registro-evento-qr.pot`. Coloca tus archivos de traducción en `wp-content/languages/plugins/`.

= ¿Los datos de los registrados están seguros? =
El plugin sigue las prácticas estándar de WordPress para la seguridad de datos, incluyendo el uso de nonces para la protección contra CSRF, sanitización de entradas y escapado de salidas. Las consultas a la base de datos se realizan usando métodos preparados. Se recomienda mantener WordPress y todos los plugins actualizados.

= ¿Se requiere alguna configuración especial del servidor? =
*   **PHP Sessions:** El plugin utiliza sesiones PHP para mostrar mensajes de estado en el formulario de registro. La mayoría de los servidores tienen esto habilitado por defecto.
*   **Permisos de Escritura:** El directorio `wp-content/uploads/registro-evento-qr/` (que el plugin intentará crear) debe tener permisos de escritura por el servidor web para que se puedan guardar las imágenes QR generadas.

== Screenshots ==

1.  **Interfaz del Formulario de Registro Público.** (Visita tu página con el shortcode para ver esto)
2.  **Panel de Configuración del Evento en Admin.** (`Registro Evento` > `Configuración`)
3.  **Listado de Registros en Admin.** (`Registro Evento`)
4.  **Página de Validación de QR para Organizadores (Frontend).** (La página que creaste con slug `validar-registro` y la plantilla `Página de Validación de QR`)
5.  **Página de Validación de QR en Admin.** (`Registro Evento` > `Validar QR`)

== Changelog ==

= 1.0.0 - YYYY-MM-DD (Reemplaza con la fecha actual) =
*   Lanzamiento inicial del plugin.
*   Funcionalidades: Registro de usuarios, generación de QR, envío de email de confirmación, panel de administración para configuración y listado de registros, página de validación de QR para admin y frontend (plantilla de página).
*   Acciones individuales y masivas en el listado de registros.
*   Internacionalización preparada.

== Upgrade Notice ==

= 1.0.0 =
Este es el primer lanzamiento. Asegúrate de seguir las instrucciones de instalación, especialmente en lo referente a las librerías QR (`phpqrcode` es esencial) y la creación de la página de validación con el slug `validar-registro`.

== Developer Notes ==

*   **Dependencias Clave:**
    *   `phpqrcode` (para PHP): Se espera en `includes/libs/phpqrcode/qrlib.php`. Es crucial para la generación de imágenes QR.
    *   `html5-qrcode.min.js` (para JS): Se espera en `admin/js/libs/` y `public/js/libs/`. Necesaria para el escaneo con cámara.
*   **Seguridad:** Se han implementado nonces, sanitización, escapado y control de capacidades. Las consultas son preparadas.
*   **Internacionalización:** Text domain `registro-evento-qr`. Archivo `.pot` en `/languages/`.
*   **Slug Página Validación:** La página de validación frontend debe tener el slug `validar-registro` y usar la plantilla "Página de Validación de QR".
*   **Sesiones PHP:** Se utiliza `$_SESSION` para mensajes flash en el formulario de registro.
*   **Permisos de Escritura:** El directorio `wp-content/uploads/registro-evento-qr/` debe tener permisos de escritura.
*   **Pruebas:** Se recomienda probar exhaustivamente en un entorno de staging, especialmente las funciones de email y generación/escaneo de QR.
*   **Placeholders de Email:** `{nombre_usuario}`, `{email_usuario}`, `{empresa_usuario}`, `{puesto_usuario}`, `{telefono_usuario}`, `{qr_code_image_tag}`, `{qr_code_url}`, `{event_logo_tag}`, `{detalles_evento}`.
