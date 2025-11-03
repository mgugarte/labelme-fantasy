<?php
// === ESTILOS PADRE E HIJO ===
function astra_child_enqueue_styles() {
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('astra-child-style', get_stylesheet_directory_uri() . '/style.css', ['astra-parent-style']);
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');

// === ESTILOS Y JS PERSONALIZADOS ===
function custom_enqueue_scripts() {
    wp_enqueue_style('custom-style', get_stylesheet_directory_uri() . '/css/styles_form.css');
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/js/script_form.js', [], false, true);
}
add_action('wp_enqueue_scripts', 'custom_enqueue_scripts');

// === GUARDAR RESPUESTAS DE ENCUESTA ===
function guardar_respuesta_encuesta($pregunta, $respuesta) {
    global $wpdb;
    $tabla = $wpdb->prefix . "encuestas_respuestas";

    $wpdb->insert(
        $tabla,
        [
            'pregunta' => sanitize_text_field($pregunta),
            'respuesta' => sanitize_text_field($respuesta),
            'usuario_ip' => $_SERVER['REMOTE_ADDR']
        ],
        ['%s', '%s', '%s']
    );
}

// === REGLAS PERSONALIZADAS PARA JUGADORES ===
function labelme_jugadores_rewrite_rules() {
    add_rewrite_rule(
        '^jugadores/([^/]+)/?$',
        'index.php?jugador_slug=$matches[1]',
        'top'
    );
}
add_action('init', 'labelme_jugadores_rewrite_rules');

function labelme_query_vars($query_vars) {
    $query_vars[] = 'jugador_slug';
    return $query_vars;
}
add_filter('query_vars', 'labelme_query_vars');

function labelme_template_include($template) {
    $jugador_slug = get_query_var('jugador_slug');
    
    if ($jugador_slug) {
        $new_template = get_stylesheet_directory() . '/jugador-individual.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'labelme_template_include', 99);

// === GENERAR SITEMAP PERSONALIZADO DE JUGADORES ===
function labelme_sitemap_jugadores() {
    global $wpdb;

    $jugadores = $wpdb->get_results("
        SELECT nombre, team_name AS equipo 
        FROM jugadores_laliga 
        WHERE season = '2025'
        ORDER BY nombre
    ");

    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    // Página principal de jugadores
    echo '<url>';
    echo '<loc>' . esc_url(home_url('/jugadores/')) . '</loc>';
    echo '<changefreq>daily</changefreq>';
    echo '<priority>0.9</priority>';
    echo '</url>';

    // Cada jugador
    foreach ($jugadores as $jugador) {
        $slug = sanitize_title($jugador->nombre);
        echo '<url>';
        echo '<loc>' . esc_url(home_url('/jugadores/' . $slug . '/')) . '</loc>';
        echo '<changefreq>weekly</changefreq>';
        echo '<priority>0.7</priority>';
        echo '</url>';
    }

    echo '</urlset>';
    exit;
}

// === MOSTRAR /sitemap-jugadores.xml SIN ROMPER YOAST ===
add_action('template_redirect', function() {
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (rtrim($request_uri, '/') === '/sitemap-jugadores.xml') {
        status_header(200);
        header('Content-Type: application/xml; charset=utf-8');
        labelme_sitemap_jugadores();
        exit;
    }
});


// === AÑADIRLO AL ÍNDICE PRINCIPAL DE YOAST ===
add_filter('wpseo_sitemap_index', function($content) {
    $jugadores_url = esc_url(home_url('/sitemap-jugadores.xml'));
    $extra = "
    <sitemap>
        <loc>{$jugadores_url}</loc>
        <lastmod>" . date('c') . "</lastmod>
    </sitemap>";
    return str_replace('</sitemapindex>', $extra . '</sitemapindex>', $content);
});
