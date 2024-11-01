# [WP-SSO](https://gitlab.com/wp-sso)

## wp-sso-client

cliente de sso, este proyecto se trabajo en el cliente del usuario. Por lo que no es necesario, compartir servidor, ni cokkies, ni base de datos.

### Instalacion

1. Modificar el wp-config.php, justo antes de "if ( !defined('ABSPATH') )" añadir define('WP_SSO_SERVER', 'http://wp_sso_server/'); http://wp_sso_server/ coresponde al sitio que hara de servidor donde este instalafo el plugin wp-sso-server

2. Activar el plugin, y listo

