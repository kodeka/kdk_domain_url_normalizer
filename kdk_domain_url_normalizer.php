<?php
/*
 * Plugin Name: Domain URL Normalizer (by Kodeka)
 * Plugin URI: https://kodeka.io
 * Description: A simple plugin to rewrite multiple domains or subdomains to a single domain in the site's HTML output. The plugin can also normalize the domain protocol (http:// to https:// and vice versa) as well as additional ports (e.g. example.com:8080 to www.example.com). Comes handy especially when WordPress is served over a CDN and you want the WP admin to be served from a different subdomain to avoid caching. Or when you want to serve your site over HTTPS and you want all internal resources to be properly linked through HTTPS as well.
 * Version: 1.1.0
 * Author: Kodeka
 * Author URI: https://kodeka.io
 * License: GNU/GPL v2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function kdk_domain_url_normalizer_callback($buffer)
{
    // Prevent execution
    if (is_admin() || strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false || defined('STDIN')) {
        //return $buffer;
    }

    $options = get_option('kdk_domain_url_normalizer_options');
    $enabled = isset($options['enabled']) && $options['enabled'] ? true : false;
    $urls = isset($options['urls']) && trim($options['urls']) != '' ? explode(PHP_EOL, $options['urls']) : null;
    $domain = isset($options['domain']) ? trim($options['domain']) : null;

    if ($enabled && is_array($urls) && count($urls) && $domain) {
        $domainFragments = explode('://', $domain);
        foreach ($urls as $url) {
            $url = trim($url);
            if ($url) {
                $buffer = str_ireplace('http://'.$url, $domain, $buffer);
                $buffer = str_ireplace('https://'.$url, $domain, $buffer);
                $buffer = str_ireplace('http:\/\/'.$url, $domainFragments[0].':\/\/'.$domainFragments[1], $buffer);
                $buffer = str_ireplace('https:\/\/'.$url, $domainFragments[0].':\/\/'.$domainFragments[1], $buffer);
                $buffer = str_ireplace('"'.$url, '"'.$domainFragments[1], $buffer);
                $buffer = str_ireplace('\''.$url, '\''.$domainFragments[1], $buffer);
                $buffer = str_ireplace('"//'.$url, '"//'.$domainFragments[1], $buffer);
                $buffer = str_ireplace('\'//'.$url, '\'//'.$domainFragments[1], $buffer);
            }
        }
    }

    // Set caching headers
    $cacheInSeconds_Frontpage = '120';
    $cacheInSeconds_Inner = '600';

    if (!is_user_logged_in()) {
        header_remove("Cache-Control");
        if (is_front_page()) {
            header('Cache-Control: public, max-age='.$cacheInSeconds_Frontpage.', stale-while-revalidate='.($cacheInSeconds_Frontpage*2).', stale-if-error='.($cacheInSeconds_Frontpage*3));
        } else {
            header('Cache-Control: public, max-age='.$cacheInSeconds_Inner.', stale-while-revalidate='.($cacheInSeconds_Inner*2).', stale-if-error='.($cacheInSeconds_Inner*3));
        }
    }

    return $buffer;
}

function kdk_domain_url_normalizer_start()
{
    ob_start('kdk_domain_url_normalizer_callback');
}

function kdk_domain_url_normalizer_end()
{
    if (ob_get_length()) {
        ob_end_flush();
    }
}

//add_action('wp_head', 'kdk_domain_url_normalizer_start', 1);
//add_action('wp_footer', 'kdk_domain_url_normalizer_end', 1);
add_action('after_setup_theme', 'kdk_domain_url_normalizer_start', 999998);
add_action('shutdown', 'kdk_domain_url_normalizer_end', 999999);

function kdk_domain_url_normalizer_settings_menu()
{
    add_options_page(
        'Domain URL Normalizer (by Kodeka)',
        'Domain URL Normalizer (by Kodeka)',
        'manage_options',
        'kdk-domain-url-normalizer',
        'kdk_domain_url_normalizer_create_admin_page'
    );
}

function kdk_domain_url_normalizer_create_admin_page()
{
    require WP_PLUGIN_DIR.'/kdk_domain_url_normalizer/admin/settings.php';
}

function kdk_domain_url_normalizer_print_section_info()
{
}

function kdk_domain_url_normalizer_field_enabled()
{
    $options = get_option('kdk_domain_url_normalizer_options');
    $enabled = false;
    if (isset($options['enabled']) && $options['enabled']) {
        $enabled = true;
    }
    if ($enabled) {
        $checked1 = '';
        $checked2 = 'checked="checked"';
    } else {
        $checked1 = 'checked="checked"';
        $checked2 = '';
    }
    print('
        <label>
        '.__('No', 'kdk_domain_url_normalizer').'
        <input type="radio" name="kdk_domain_url_normalizer_options[enabled]" value="0" '.$checked1.' />
        </label>
        <label>
        '.__('Yes', 'kdk_domain_url_normalizer').'
        <input type="radio" name="kdk_domain_url_normalizer_options[enabled]" value="1" '.$checked2.' /></label>
    ');
}

function kdk_domain_url_normalizer_field_match_urls()
{
    $options = get_option('kdk_domain_url_normalizer_options');
    $value = isset($options['urls']) ? $options['urls'] : '';
    print('<textarea name="kdk_domain_url_normalizer_options[urls]" cols="30" rows="6" placeholder="'.__('Example (domain per line):&#10;example.com&#10;www2.example.com&#10;www2.example.com:8080', 'kdk_domain_url_normalizer').'">'.$value.'</textarea>');
}

function kdk_domain_url_normalizer_field_replace_url()
{
    $options = get_option('kdk_domain_url_normalizer_options');
    $value = isset($options['domain']) ? $options['domain'] : '';
    print('<input type="text" name="kdk_domain_url_normalizer_options[domain]" value="'.htmlspecialchars($value).'" placeholder="'.__('Example: http://www.example.com', 'kdk_domain_url_normalizer').'" style="width:300px;" />');
}

function kdk_domain_url_normalizer_settings_page()
{
    register_setting(
        'kdk_domain_url_normalizer_option_group',
        'kdk_domain_url_normalizer_options'
    );

    add_settings_section(
        'setting_section_id',
        __('Options', 'kdk_domain_url_normalizer'),
        'kdk_domain_url_normalizer_print_section_info',
        'kdk-domain-url-normalizer-admin'
    );

    add_settings_field(
        'enabled',
        __('Enabled', 'kdk_domain_url_normalizer'),
        'kdk_domain_url_normalizer_field_enabled',
        'kdk-domain-url-normalizer-admin',
        'setting_section_id'
    );

    add_settings_field(
        'urls',
        __('Domain URLs to match (do not include http:// or https:// - add each domain per line)', 'kdk_domain_url_normalizer'),
        'kdk_domain_url_normalizer_field_match_urls',
        'kdk-domain-url-normalizer-admin',
        'setting_section_id'
    );

    add_settings_field(
        'domain',
        __('Domain URL to replace with (MUST include http:// or https:// - no trailing slash)', 'kdk_domain_url_normalizer'),
        'kdk_domain_url_normalizer_field_replace_url',
        'kdk-domain-url-normalizer-admin',
        'setting_section_id'
    );
}

add_action('admin_menu', 'kdk_domain_url_normalizer_settings_menu');
add_action('admin_init', 'kdk_domain_url_normalizer_settings_page');
add_action('plugins_loaded', 'kdk_domain_url_normalizer_load_textdomain');

function kdk_domain_url_normalizer_load_textdomain()
{
    load_plugin_textdomain('kdk_domain_url_normalizer', false, dirname(plugin_basename(__FILE__)).'/lang/');
}
