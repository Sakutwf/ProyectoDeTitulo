# Guía del código compartido

Esta guía identifica las funciones que concentran comportamiento reutilizable y explica dónde se usan. La regla de mantenimiento es extender estas piezas cuando aparezca un caso equivalente, en vez de copiar su lógica dentro de una vista o controlador.

## Frontend

### Impresión y PDF

| Pieza | Responsabilidad | Utilizada por |
| --- | --- | --- |
| `useDocumentPrint(readyToPrint)` | Comprueba que el documento esté listo, abre la impresión del navegador y avisa únicamente si esa operación falla. | `HistorialPdfView.vue` para hoja de vida; `DocumentoActividadPdfView.vue` para análisis de contexto e informe narrativo. |
| `document-print.css` | Define hoja carta vertical, conservación de colores, barra de acciones, estados y reglas comunes de impresión. | Se carga una vez desde `main.js`; las dos vistas PDF sólo conservan sus particularidades visuales. |

Los tres documentos comparten el mismo mecanismo. Para incorporar otro tipo se debe reutilizar `useDocumentPrint`, asignar `pdf-document` al contenedor imprimible y agregar únicamente las reglas específicas del nuevo formato.

### Imágenes

| Función | Responsabilidad | Utilizada por |
| --- | --- | --- |
| `optimizeImage(file, options)` | Valida el tipo, conserva proporción, limita dimensiones sin ampliar imágenes pequeñas, recodifica a WebP y elimina metadatos mediante canvas. | Portada, galería administrativa, galería de voluntarios, actividades de voluntarios, documentos, creación/edición de usuarios y hoja de vida. |
| `optimizeImages(files, options)` | Ejecuta `optimizeImage` sobre una colección. | Editor de portada para procesar varias imágenes. |

El backend vuelve a optimizar mediante `ImageOptimizer`; esta segunda validación es intencional porque nunca se debe confiar sólo en el navegador.

### Autorización y navegación

Todas estas funciones viven en `utils/auth.js` y trabajan sin modificar estado:

| Función | Responsabilidad | Consumidor principal |
| --- | --- | --- |
| `getUserRoleSlugs` | Extrae las claves de roles del usuario. | Store. |
| `hasRole`, `hasAnyRole` | Comprueban uno o varios roles. | Store y reglas derivadas. |
| `canManagePlatform` | Reconoce administrador o secretario de directiva. | Store. |
| `canAccessVolunteerProfile` | Comprueba rol y perfil de voluntario asociado. | Resolución de experiencia. |
| `requiresAccessSelection` | Detecta usuarios que pueden elegir perfil administrativo o voluntario. | Login, router y store. |
| `resolveAccessMode` | Resuelve la experiencia activa válida. | Store y funciones derivadas. |
| `isAdministratorExperience`, `isVolunteerExperience`, `isVolunteerOnly` | Exponen decisiones listas para menús y guardas. | Store, router y vistas. |
| `defaultRouteForUser` | Decide la ruta segura posterior al login o a una guarda. | Login, selección de acceso y router. |

### Interfaz común

| Pieza | Responsabilidad | Utilizada por |
| --- | --- | --- |
| `show_alerta` | Configura SweetAlert de forma uniforme y opcionalmente enfoca el campo inválido. | Formularios de usuarios y actividades, login, boletas, galería, hoja de vida y documentos. |
| `admin-layout.css` | Contiene el área principal, encabezado y espaciado compartidos del panel. | Se carga globalmente desde `main.js`. |
| `cruz-roja-theme.css` | Mantiene colores, tipografía y variables de diseño. | Todo el frontend. Los colores recurrentes deben añadirse aquí como variable antes de repetirse en vistas. |
| `ActivityVolunteerSelectors.vue` | Centraliza los modales de participantes y destinatarios: búsqueda, checkboxes, horas, selección inicial desde participantes, seleccionar/desmarcar todos y cierre seguro. | Creación y edición de actividades; cada vista sólo aporta sus textos y conserva el guardado particular. |

### Formatos de presentación

| Función | Responsabilidad | Utilizada por |
| --- | --- | --- |
| `formatDate` | Convierte fechas de la API a formato local chileno sin desplazamiento por zona horaria. | Actividades, documentos, gestión de boletas, boletas del voluntario y galería. |
| `formatDateRange` | Une fecha inicial y final manteniendo el mismo tratamiento. | Documentos y galería del voluntario. |
| `formatCurrency` | Presenta montos enteros en pesos chilenos. | Gestión de boletas y boletas del voluntario. |

Estas funciones viven en `utils/formatters.js`. Los formatos deliberadamente distintos, por ejemplo los que muestran `Sin registro` o fuerzan ceros iniciales, permanecen en su vista para no cambiar su presentación.

### Paginación de API

`fetchAllPages(httpClient, url, params)` vive en `utils/apiPagination.js`. Consulta la primera página de un endpoint Laravel, obtiene `last_page`, descarga las restantes y devuelve una sola colección. La utilizan las vistas de actividades, boletas y galería del voluntario; cada vista conserva su propio manejo de carga y errores.

## Backend

### Servicios compartidos

| Clase / método público | Responsabilidad | Utilizada por |
| --- | --- | --- |
| `ImageOptimizer::store` | Reduce a un máximo de 1920 px, convierte a WebP, elimina metadatos, valida el peso final y almacena el archivo. | `AlbumController`, `ActividadController`, `PortadaController`, `UserController`, `HojaVidaAnualController` y `LifeSheetApprovalService`. |
| `NotificationCampaignService::availableVolunteers` | Entrega voluntarios con correo válido para los modales de destinatarios. | `ActividadController`. |
| `NotificationCampaignService::sendActivity` | Autoriza y crea avisos de actividad nueva o modificada, evitando duplicados mediante huella. | `ActividadController`. |
| `notifyReceiptSubmitted`, `notifyReceiptStatus` | Notifican ingreso y cambio de estado de boletas. | `ActividadController`. |
| `notifyLifeSheetRequest`, `notifyLifeSheetDecision` | Notifican solicitudes y decisiones de antecedentes. | Controladores y servicio de hoja de vida. |
| `LifeSheetApprovalService::capture` | Detecta cambios del voluntario y deja archivos/registros pendientes. | `HojaVidaAnualController`. |
| `LifeSheetApprovalService::approve`, `reject` | Aplica o rechaza una solicitud y conserva trazabilidad. | `SolicitudHojaVidaController`. |

### Envío de correos

1. `NotificationCampaignService` crea la campaña y sus destinatarios dentro de una transacción.
2. Despacha un `EnviarCorreoCampana` por destinatario.
3. El job envía `NotificacionSistemaMail`, guarda éxito o error y recalcula el estado de la campaña.
4. `notificacion-sistema.blade.php` es la única plantilla de correo.

### Controladores y modelos

Los métodos públicos de los controladores corresponden directamente a las rutas de `backend/routes/api.php`; los métodos privados validan, normalizan o sincronizan datos sólo para su controlador. Las relaciones y atributos calculados pertenecen a los modelos. No se trasladan a esta guía porque sus nombres y rutas ya constituyen su documentación ejecutable y duplicarlos haría más difícil mantenerlos sincronizados.

## Criterios para cambios futuros

- No copiar valores de color recurrentes: usar o agregar una variable `--cr-*`.
- No copiar reglas de layout administrativo o de impresión en una vista.
- No implementar otra rutina de compresión fuera de `imageOptimization.js` y `ImageOptimizer.php`.
- No abrir `window.print()` directamente desde una vista PDF.
- No decidir roles manualmente en componentes: usar el store y `utils/auth.js`.
- Mantener en los componentes sólo estilos particulares, incluidos sus media queries; esto protege el comportamiento responsive existente.
