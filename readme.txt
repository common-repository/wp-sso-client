=== wp-sso-client ===
Donate link: https://paypal.me/ferromariano
Tags: sso, Single Sign-on, authentication, one login, my sso
Requires at least: 4.9.2
Tested up to: 4.9.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
Cliente de sso para wordpress, este plugin convierte a tu wordpress en el servidor de login de otro wordpress, sin DBs, sin cookies. [Servidor wp-sso-server](https://wordpress.org/plugins/wp-sso-server/)
 
== Description ==
 
Servidor de sso para wordpress, este plugin convierte a tu wordpress en el servidor de login de otro wordpress, sin DBs, sin cookies. [Servidor wp-sso-server](https://wordpress.org/plugins/wp-sso-server/)

= Documentacion completa =

https://gitlab.com/wp-sso/wp-sso-client
 
== Installation ==

Editar wp-config.php, agregar

```php
define('WP_SSO_SERVER',  'home de sitio donde esta instlado wp-sso-server');
define('WP_SSO_SITE_ID', 'site id que te da el wp-sso-server');
define('WP_SSO_TOKEN',   'token secret que te da el wp-sso-server');
```

Despues se instala como cualquier plugin

= From your WordPress dashboard =
1. Visit `Plugins > Add New`.
2. Search for `wp-sso-client`. Find and Install `wp-sso-client`.
3. Activate the plugin from your Plugins page.

== Frequently Asked Questions ==
 
=== ¿ Afectas a las URL ? ===
NO

=== ¿ Requiere compartir servidor ? ===
NO

=== ¿ Requiere compartir DBS ? ===
NO

=== ¿ como lo hace ? ===
El cliente incluye un jsonp el cual le entrega la sobre el usuario, el login y un token. Si el usuario no esta registrado en el WP cliente pero esta logueado en el WP servidor, este pide información al servidor, servidor a servidor, enviando el token. Con la información devuelta comprueba si el usuario esta registrado un usuario en el WP cliente, ( si no está lo registra ) y lo loguea

  
== Changelog ==
 
= 1.0 =
* Inicio del plugin