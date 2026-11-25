<?php
/**
 * Plugin Name: Bezares Chat Widget
 * Description: Muestra el icono de chat de chat.bezares.com. No guarda nada en la base de datos salvo 4 ajustes.
 * Version: 1.1.0
 * Author: Bezares
 * Requires PHP: 7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

const BZCW_DEFAULT_SERVER = 'https://chat.bezares.com';

/**
 * Dominios a los que el plugin puede conectarse. Por defecto solo chat.bezares.com.
 * Para permitir otro (p. ej. un servidor de pruebas), define en wp-config.php:
 *   define('BZCW_ALLOWED_HOSTS', 'chat.bezares.com,chat-pruebas.bezares.com');
 */
function bzcw_allowed_hosts()
{
    $list = defined('BZCW_ALLOWED_HOSTS') ? (string) BZCW_ALLOWED_HOSTS : 'chat.bezares.com';
    return array_filter(array_map('strtolower', array_map('trim', explode(',', $list))));
}

/** Devuelve la URL del servidor normalizada (https + dominio permitido) o '' si no es válida. */
function bzcw_valid_server($url)
{
    $p = wp_parse_url(trim((string) $url));
    if (!$p || strtolower($p['scheme'] ?? '') !== 'https' || empty($p['host']) || isset($p['user']) || isset($p['pass'])
        || !in_array(strtolower($p['host']), bzcw_allowed_hosts(), true)) {
        return '';
    }
    return 'https://' . strtolower($p['host']) . (isset($p['port']) && (int) $p['port'] !== 443 ? ':' . (int) $p['port'] : '');
}

function bzcw_opts()
{
    $o = wp_parse_args(get_option('bzcw', array()), array(
        'server'   => BZCW_DEFAULT_SERVER,
        'position' => 'bottom-right',
        'offset'   => 20,
        'devices'  => array('desktop', 'tablet', 'mobile'),
    ));
    // Defensa adicional: aunque alguien escriba la opción directamente en la BD, solo se usan servidores permitidos.
    $o['server'] = bzcw_valid_server($o['server']) ?: BZCW_DEFAULT_SERVER;
    return $o;
}

// Inserta el script del widget en el pie de página.
add_action('wp_footer', function () {
    $o = bzcw_opts();
    if (empty($o['devices']) || !bzcw_valid_server($o['server'])) {
        return;
    }
    printf(
        '<script src="%s/widget.js" data-position="%s" data-offset="%d" data-devices="%s" defer></script>' . "\n",
        esc_url(untrailingslashit($o['server'])),
        esc_attr($o['position']),
        (int) $o['offset'],
        esc_attr(implode(',', (array) $o['devices']))
    );
});

// Ajustes → Bezares Chat
add_action('admin_menu', function () {
    add_options_page('Bezares Chat', 'Bezares Chat', 'manage_options', 'bzcw', 'bzcw_page');
});

add_action('admin_init', function () {
    register_setting('bzcw', 'bzcw', array('sanitize_callback' => function ($in) {
        $pos = array('bottom-right', 'bottom-left', 'top-right', 'top-left');
        $server = bzcw_valid_server(isset($in['server']) ? $in['server'] : '');
        if (!$server) {
            add_settings_error('bzcw', 'bzcw_server', 'Servidor no permitido: debe ser https:// y uno de estos dominios: ' . implode(', ', bzcw_allowed_hosts()) . '. Se mantiene ' . BZCW_DEFAULT_SERVER . '.');
            $server = BZCW_DEFAULT_SERVER;
        }
        return array(
            'server'   => $server,
            'position' => isset($in['position']) && in_array($in['position'], $pos, true) ? $in['position'] : 'bottom-right',
            'offset'   => max(0, min(200, (int) (isset($in['offset']) ? $in['offset'] : 20))),
            'devices'  => array_values(array_intersect(array('desktop', 'tablet', 'mobile'), (array) (isset($in['devices']) ? $in['devices'] : array()))),
        );
    }));
});

function bzcw_page()
{
    $o = bzcw_opts();
    $pos = array('bottom-right' => 'Inferior derecha', 'bottom-left' => 'Inferior izquierda', 'top-right' => 'Superior derecha', 'top-left' => 'Superior izquierda');
    $dev = array('desktop' => 'Ordenadores (sobremesa y portátiles)', 'tablet' => 'Tablets', 'mobile' => 'Móviles');
    ?>
    <div class="wrap">
        <h1>Bezares Chat</h1>
        <p>Mensajes, horario, WhatsApp, usuarios y estadísticas se gestionan en <a href="<?php echo esc_url(untrailingslashit($o['server']) . '/admin/'); ?>" target="_blank" rel="noopener"><?php echo esc_html($o['server']); ?>/admin</a>.</p>
        <form method="post" action="options.php">
            <?php settings_errors('bzcw'); settings_fields('bzcw'); ?>
            <table class="form-table" role="presentation">
                <tr><th scope="row">Servidor de chat</th><td><input type="url" class="regular-text" name="bzcw[server]" pattern="https://.*" required value="<?php echo esc_attr($o['server']); ?>"><p class="description">Solo <code>https://</code> y dominios permitidos: <?php echo esc_html(implode(', ', bzcw_allowed_hosts())); ?></p></td></tr>
                <tr><th scope="row">Esquina del icono</th><td><select name="bzcw[position]">
                    <?php foreach ($pos as $k => $l) : ?>
                        <option value="<?php echo esc_attr($k); ?>" <?php selected($o['position'], $k); ?>><?php echo esc_html($l); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
                <tr><th scope="row">Separación del borde (px)</th><td><input type="number" min="0" max="200" class="small-text" name="bzcw[offset]" value="<?php echo (int) $o['offset']; ?>"></td></tr>
                <tr><th scope="row">Mostrar en</th><td>
                    <?php foreach ($dev as $k => $l) : ?>
                        <label style="display:block"><input type="checkbox" name="bzcw[devices][]" value="<?php echo esc_attr($k); ?>" <?php checked(in_array($k, (array) $o['devices'], true)); ?>> <?php echo esc_html($l); ?></label>
                    <?php endforeach; ?>
                    <p class="description">El navegador no distingue un portátil de un sobremesa: ambos son "Ordenadores".</p>
                </td></tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

register_uninstall_hook(__FILE__, 'bzcw_uninstall');
function bzcw_uninstall()
{
    delete_option('bzcw');
}
