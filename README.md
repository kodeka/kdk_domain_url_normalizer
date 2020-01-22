# KDK Domain URL Normalizer (v1.1.0)

A simple plugin to rewrite multiple domains or subdomains to a single domain in the site's HTML output.

The plugin can also normalize the domain protocol (http:// to https:// and vice versa) as well as additional ports (e.g. example.com:8080 to www.example.com and vice versa).

Some practical uses:

- Normalize the domain after migrating WordPress from a test domain to the production one.
- Allow the WordPress admin to be served from a different subdomain (e.g. when WordPress is served over a CDN and you want to avoid any caching of static data etc.). This would require passing on multiple domains/subdomains in your wp-config.php file like so:
```
// Map multiple domains in WordPress
define('KDK_WP_PRIMARY_DOMAIN', 'www.domain.tld');
define('KDK_WP_SECONDARY_DOMAINS', serialize(array(
    'www2.domain.tld',
    'admin.domain.tld',
    'whatever.domain.tld'
)));
define('KDK_WP_DOMAIN_PROTOCOL', 'https');

if (in_array($_SERVER['HTTP_HOST'], unserialize(KDK_WP_SECONDARY_DOMAINS))) {
    define('WP_HOME', KDK_WP_DOMAIN_PROTOCOL.'://'.$_SERVER['HTTP_HOST']);
    define('WP_SITEURL', KDK_WP_DOMAIN_PROTOCOL.'://'.$_SERVER['HTTP_HOST']);
} else {
    define('WP_HOME', KDK_WP_DOMAIN_PROTOCOL.'://'.KDK_WP_PRIMARY_DOMAIN);
    define('WP_SITEURL', KDK_WP_DOMAIN_PROTOCOL.'://'.KDK_WP_PRIMARY_DOMAIN);
}

// Share cookies across subdomains
define('COOKIE_DOMAIN', '.domain.tld');
```
- When you want to switch your site from HTTP to HTTPS and you want all internal resources to be properly linked through HTTPS as well.

Last Update: January 2020


## To Do
- Add option for caching (home/inner pages)
- Add option for executing in the backend
- Add new *.ini based language file


## License & Credits

Licensed under the GNU/GPL license (https://www.gnu.org/copyleft/gpl.html).

Copyright (c) 2018 - 2020 Kodeka OÜ. All rights reserved.
