=== Registry - Gift Registry for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, gift registry, wishlist, wedding, baby shower
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requiere complementos: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Permita que los clientes creen registros de regalos compartibles para bodas, baby showers y eventos, con seguimiento de compras para que los invitados nunca realicen compras dobles.

== Description ==

Registro añade registros de regalos a su tienda WooCommerce. Un cliente que ha iniciado sesión crea un registro con nombre para un evento (boda, baby shower, cumpleaños, inauguración de una casa u otro), elige los productos que desea de la tienda y obtiene un enlace que se puede compartir para enviar a amigos y familiares.

Los invitados abren ese enlace, ven qué artículos aún se necesitan y compran un regalo. Las cantidades compradas se cuentan a partir de pedidos reales de WooCommerce, por lo que todo lo que ya se haya comprado se marca como completamente comprado y dos personas no compran lo mismo dos veces.

Rastreador de fuentes y problemas en vivo en GitHub: https://github.com/wppoland/registry

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/registry/docs/
* <strong>Página de complementos</strong> - https://plogins.com/es/registry/
* <strong>Código fuente</strong> - https://github.com/wppoland/registry
* <strong>Informes de errores y solicitudes de funciones</strong> - https://github.com/wppoland/registry/issues
* <strong>Discusiones y preguntas</strong> - https://github.com/wppoland/registry/discussions


= Features =

* Los clientes crean y administran sus registros en Mi cuenta → Registros de regalo.
* Cada registro tiene un tipo de evento (boda, baby shower, cumpleaños, inauguración de casa, otros) y una fecha del evento.
* Un control "Añadir al registro de regalos" en páginas de productos individuales, con una cantidad deseada por artículo.
* Una página de registro pública de solo lectura con su propio enlace permanente, creada para compartir.
* Las cantidades compradas se leen de los pedidos pagados de WooCommerce, por lo que los recuentos restantes se mantienen actualizados sin actualizaciones manuales.
* Compra directa opcional desde la página compartida; Si está desactivado, los invitados son enviados a la página del producto.
* Cada acción verifica la propiedad del registro, por lo que un cliente nunca podrá editar el registro de otro.

== Installation ==

1. Cargue el complemento en `/wp-content/plugins/registry`, o instálelo a través de Complementos → Añadir nuevo.
2. Actívalo. WooCommerce debe estar instalado y activo.
3. Los registros están activados de forma predeterminada. Visita WooCommerce → Registros de regalos para desactivarlos o elegir si los invitados pueden comprar directamente desde la página compartida.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Sí. WooCommerce debe estar instalado y activo.

= Who can create a registry? =

Cualquier cliente que haya iniciado sesión, desde el área Mi cuenta → Registros de regalo.

= How does purchase tracking work? =

Cuando se compra un regalo a través de un registro, el registro al que pertenece se almacena en la línea de pedido del pedido. Cuando ese pedido llega a procesarse o completarse, la cantidad se añade al recuento de compras del registro y la página pública la resta de lo que aún se necesita. Cada pedido sólo se cuenta una vez.

= Can guests buy directly from the shared page? =

Sí, si "Permitir compra directa" está habilitado en la configuración. De lo contrario, el botón de compra envía a los invitados a la página del producto.

= Do guests need an account to view a shared registry? =

No. La página de registro público es de sólo lectura para cualquier persona que tenga el enlace; solo para crear y editar registros se requiere que un cliente haya iniciado sesión.


= Does this plugin work on WordPress Multisite? =

Sí. Este complemento es compatible con WordPress Multisite. Activarlo en red o activarlo en sitios individuales; Cada sitio mantiene su propia configuración y datos.

== Screenshots ==

1. En el escaparate.
2. Configuración en el administrador de WordPress.
3. En un dispositivo móvil.
== External Services ==

El registro no se conecta a ningún servicio externo. No realiza solicitudes de red salientes ni envía datos fuera de tu sitio. Los registros se almacenan en WordPress como un tipo de publicación personalizada `gift_registry` con meta de publicación `_registry_*` (tipo de evento, fecha del evento, artículos elegidos y recuentos comprados), y la configuración del complemento se encuentra en las opciones `registry_settings` y `registry_db_version`. El seguimiento de compras lee sus propios pedidos de WooCommerce y registra `_registry_id`, `_registry_purchased` y `_registry_counted` en las líneas de pedido relevantes; todo queda en tu base de datos.

== Changelog ==

= 1.0.1 =
* Primera versión estable.

= 0.1.4 =
* Las acciones `registry/purchase_recorded` y `registry/thankyou_purchase` después de que se cuenten los obsequios de registro pagados.
* Filtros `registry/theme`, `registry/theme_vars` y acción `registry/public_hero` para temas de registro PRO.

= 0.1.3 =
* Añade filtros de registro/can_manage, registro/can_delete, registro/user_registries y registro/is_owner para registros compartidos.
* Añade acción de registro/cuenta/single_registry, filtro de registro/cuenta/avisos y metaayudante _registry_contributors para listas de copropiedad.

= 0.1.2 =
* Añadir filtro de registro/max_registries_limit.
* Añade comprobaciones y avisos de límites de registro en Mi cuenta.

= 0.1.0 =
* Lanzamiento inicial.
