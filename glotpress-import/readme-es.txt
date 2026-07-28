=== Registry - Gift Registry for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, gift registry, wishlist, wedding, baby shower
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Permite que los clientes creen listas de regalos compartibles para bodas, baby showers y eventos, con seguimiento de compras para que los invitados nunca compren lo mismo dos veces.

== Description ==

Registry añade listas de regalos a tu tienda WooCommerce. Un cliente con sesión iniciada crea una lista con nombre para un evento (boda, baby shower, cumpleaños, estreno de casa u otro), elige los productos que quiere de la tienda y obtiene un enlace compartible para enviar a amigos y familiares.

Los invitados abren ese enlace, ven qué artículos aún se necesitan y compran un regalo. Las cantidades compradas se cuentan a partir de pedidos reales de WooCommerce, así que todo lo ya comprado se marca como completamente adquirido y dos personas no compran lo mismo dos veces.

Código fuente y seguimiento de incidencias en GitHub: https://github.com/wppoland/registry

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/registry/docs/
* <strong>Página del plugin</strong> - https://plogins.com/es/registry/
* <strong>Código fuente</strong> - https://github.com/wppoland/registry
* <strong>Informes de errores y peticiones de funciones</strong> - https://github.com/wppoland/registry/issues
* <strong>Debates y preguntas</strong> - https://github.com/wppoland/registry/discussions


= Features =

* Los clientes crean y gestionan sus listas en Mi cuenta → Listas de regalos.
* Cada lista tiene un tipo de evento (boda, baby shower, cumpleaños, estreno de casa, otro) y una fecha del evento.
* Control «Añadir a la lista de regalos» en páginas de producto individual, con cantidad deseada por artículo.
* Página pública de solo lectura con su propio enlace permanente, pensada para compartir.
* Las cantidades compradas se leen de pedidos pagados de WooCommerce, así que los recuentos restantes se mantienen al día sin actualizaciones manuales.
* Compra directa opcional desde la página compartida; si está desactivada, los invitados van a la página del producto.
* Cada acción comprueba la propiedad de la lista, así un cliente nunca puede editar la lista de otro.

== Installation ==

1. Sube el plugin a `/wp-content/plugins/registry` o instálalo desde Plugins → Añadir nuevo.
2. Actívalo. WooCommerce debe estar instalado y activo.
3. Las listas están activadas por defecto. Entra en WooCommerce → Listas de regalos para desactivarlas o elegir si los invitados pueden comprar directamente desde la página compartida.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Sí. WooCommerce debe estar instalado y activo.

= Who can create a registry? =

Cualquier cliente con sesión iniciada, desde Mi cuenta → Listas de regalos.

= How does purchase tracking work? =

Cuando se compra un regalo a través de una lista, la lista a la que pertenece se guarda en la línea del pedido. Cuando ese pedido pasa a en proceso o completado, la cantidad se suma al recuento de compras de la lista y la página pública la resta de lo que aún hace falta. Cada pedido solo se cuenta una vez.

= Can guests buy directly from the shared page? =

Sí, si «Permitir compra directa» está activado en los ajustes. Si no, el botón de compra envía a los invitados a la página del producto.

= Do guests need an account to view a shared registry? =

No. La página pública de la lista es de solo lectura para cualquiera con el enlace; solo crear y editar listas requiere un cliente con sesión iniciada.


= Does this plugin work on WordPress Multisite? =

Sí. Este plugin es compatible con WordPress Multisite. Actívalo en toda la red o en sitios concretos; cada sitio conserva sus propios ajustes y datos.

== Screenshots ==

1. En la tienda.
2. Ajustes en el escritorio de WordPress.
3. En un dispositivo móvil.
== External Services ==

Registry no se conecta a ningún servicio externo. No realiza peticiones de red salientes ni envía datos fuera de tu sitio. Las listas se almacenan en WordPress como un tipo de contenido personalizado `gift_registry` con meta de entrada `_registry_*` (tipo de evento, fecha del evento, artículos elegidos y recuentos de compra), y los ajustes del plugin están en las opciones `registry_settings` y `registry_db_version`. El seguimiento de compras lee tus propios pedidos de WooCommerce y registra `_registry_id`, `_registry_purchased` y `_registry_counted` en las líneas de pedido relevantes; todo permanece en tu base de datos.

== Translations ==

Registry incluye traducciones al polaco, al alemán y al español para la interfaz del plugin. El dominio de texto es `registry`, por lo que los paquetes de idioma de WordPress.org también pueden sobrescribir o ampliar estas traducciones incluidas.

== Changelog ==

= 1.0.2 =
* Se añadieron traducciones incluidas al polaco, al alemán y al español para la interfaz del plugin.

= 1.0.1 =
* Primera versión estable.

= 0.1.4 =
* Acciones `registry/purchase_recorded` y `registry/thankyou_purchase` tras contar regalos de lista pagados.
* Filtros `registry/theme`, `registry/theme_vars` y acción `registry/public_hero` para temas de lista PRO.

= 0.1.3 =
* Filtros `registry/can_manage`, `registry/can_delete`, `registry/user_registries` y `registry/is_owner` para listas compartidas.
* Acción `registry/account/single_registry`, filtro `registry/account/notices` y meta `_registry_contributors` para listas con copropietarios.

= 0.1.2 =
* Filtro `registry/max_registries_limit`.
* Comprobaciones de límite de listas y avisos en Mi cuenta.

= 0.1.0 =
* Versión inicial.
