<?php
// === ESTILOS PADRE E HIJO ===
function astra_child_enqueue_styles() {
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('astra-child-style', get_stylesheet_directory_uri() . '/style.css', ['astra-parent-style']);
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');

// === ESTILOS Y JS PERSONALIZADOS ===
function custom_enqueue_scripts() {
    // Estilo global consolidado
    wp_enqueue_style('labelme-global', get_stylesheet_directory_uri() . '/css/labelme_global.css', [], '3.0.2');

    // Scripts existentes
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/js/script_form.js', [], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'custom_enqueue_scripts');

// === FUNCIÓN MEJORADA PARA SANITIZAR SLUGS ===
function labelme_sanitize_slug($nombre) {
    // Mapeo de caracteres especiales a sus equivalentes ASCII
    $caracteres_especiales = array(
        // Nórdicos
        'å' => 'a', 'Å' => 'a',
        'ä' => 'a', 'Ä' => 'a',
        'ö' => 'o', 'Ö' => 'o',
        'ø' => 'o', 'Ø' => 'o',
        'æ' => 'ae', 'Æ' => 'ae',
        // Alemanes
        'ü' => 'u', 'Ü' => 'u',
        'ß' => 'ss',
        // Franceses
        'é' => 'e', 'É' => 'e',
        'è' => 'e', 'È' => 'e',
        'ê' => 'e', 'Ê' => 'e',
        'ë' => 'e', 'Ë' => 'e',
        'à' => 'a', 'À' => 'a',
        'â' => 'a', 'Â' => 'a',
        'ô' => 'o', 'Ô' => 'o',
        'û' => 'u', 'Û' => 'u',
        'ù' => 'u', 'Ù' => 'u',
        'ç' => 'c', 'Ç' => 'c',
        'ï' => 'i', 'Ï' => 'i',
        'î' => 'i', 'Î' => 'i',
        // Españoles
        'á' => 'a', 'Á' => 'a',
        'í' => 'i', 'Í' => 'i',
        'ó' => 'o', 'Ó' => 'o',
        'ú' => 'u', 'Ú' => 'u',
        'ñ' => 'n', 'Ñ' => 'n',
        // Portugueses
        'ã' => 'a', 'Ã' => 'a',
        'õ' => 'o', 'Õ' => 'o',
        // Otros
        'ł' => 'l', 'Ł' => 'l',
        'ć' => 'c', 'Ć' => 'c',
        'ś' => 's', 'Ś' => 's',
        'ź' => 'z', 'Ź' => 'z',
        'ż' => 'z', 'Ż' => 'z',
        'č' => 'c', 'Č' => 'c',
        'š' => 's', 'Š' => 's',
        'ž' => 'z', 'Ž' => 'z',
        'đ' => 'd', 'Đ' => 'd',
    );
    
    // Reemplazar caracteres especiales
    $nombre_limpio = strtr($nombre, $caracteres_especiales);
    
    // Usar sanitize_title de WordPress que ya maneja espacios, mayúsculas, etc.
    return sanitize_title($nombre_limpio);
}

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

// ========================================
// SISTEMA DE SITEMAPS PROPIO (REEMPLAZA YOAST)
// ========================================

// Registrar rewrite rules para sitemaps
add_action('init', function() {
    add_rewrite_rule('^sitemap\.xml$', 'index.php?labelme_sitemap=main', 'top');
    add_rewrite_rule('^sitemap-jugadores\.xml$', 'index.php?labelme_sitemap=jugadores', 'top');
    add_rewrite_rule('^sitemap-equipos\.xml$', 'index.php?labelme_sitemap=equipos', 'top');
});

// Añadir query vars
add_filter('query_vars', function($vars) {
    $vars[] = 'labelme_sitemap';
    return $vars;
});

// Manejar sitemaps
add_action('template_redirect', function() {
    $sitemap = get_query_var('labelme_sitemap');
    
    if ($sitemap === 'main') {
        labelme_sitemap_index();
    } elseif ($sitemap === 'jugadores') {
        labelme_sitemap_jugadores();
    } elseif ($sitemap === 'equipos') {
        labelme_sitemap_equipos();
    }
});

// SITEMAP INDEX PRINCIPAL
function labelme_sitemap_index() {
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    
    // Sitemap de jugadores
    echo '<sitemap>';
    echo '<loc>' . esc_url(home_url('/sitemap-jugadores.xml')) . '</loc>';
    echo '<lastmod>' . date('c') . '</lastmod>';
    echo '</sitemap>';
    
    // Sitemap de equipos
    echo '<sitemap>';
    echo '<loc>' . esc_url(home_url('/sitemap-equipos.xml')) . '</loc>';
    echo '<lastmod>' . date('c') . '</lastmod>';
    echo '</sitemap>';
    
    echo '</sitemapindex>';
    exit;
}

// SITEMAP DE JUGADORES
function labelme_sitemap_jugadores() {
    global $wpdb;

    $jugadores = $wpdb->get_results("
        SELECT nombre, team_name AS equipo 
        FROM jugadores_laliga 
        WHERE season = '2025'
        AND minutos > 0
        ORDER BY CAST(rating AS DECIMAL(10,2)) DESC
        LIMIT 450
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
        $slug = labelme_sanitize_slug($jugador->nombre);
        echo '<url>';
        echo '<loc>' . esc_url(home_url('/jugadores/' . $slug . '/')) . '</loc>';
        echo '<changefreq>weekly</changefreq>';
        echo '<priority>0.8</priority>';
        echo '</url>';
    }

    echo '</urlset>';
    exit;
}

// === REGLAS DE REWRITE PARA EQUIPOS ===
function labelme_equipos_rewrite_rules() {
    add_rewrite_rule(
        '^equipos/([^/]+)/?$',
        'index.php?equipo_slug=$matches[1]',
        'top'
    );
}
add_action('init', 'labelme_equipos_rewrite_rules');

function labelme_add_query_vars($vars) {
    $vars[] = 'equipo_slug';
    return $vars;
}
add_filter('query_vars', 'labelme_add_query_vars');

function labelme_template_include_equipo($template) {
    if (get_query_var('equipo_slug')) {
        $new_template = get_stylesheet_directory() . '/equipo-individual.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'labelme_template_include_equipo', 99);

// SITEMAP DE EQUIPOS
function labelme_sitemap_equipos() {
    global $wpdb;

    $equipos = $wpdb->get_results("
        SELECT DISTINCT team_name 
        FROM jugadores_laliga 
        WHERE season = '2025'
        ORDER BY team_name
    ");

    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach ($equipos as $equipo) {
        $slug = sanitize_title($equipo->team_name);
        echo '<url>';
        echo '<loc>' . esc_url(home_url('/equipos/' . $slug . '/')) . '</loc>';
        echo '<changefreq>weekly</changefreq>';
        echo '<priority>0.7</priority>';
        echo '</url>';
    }

    echo '</urlset>';
    exit;
}

// ========================================
// REDIRECCIONES PARA URLs ANTIGUAS
// ========================================
add_action('template_redirect', function() {
    $uri = $_SERVER['REQUEST_URI'];
    
    // Comparador antiguo
    if (strpos($uri, '/comparador-de-jugadores') !== false) {
        wp_redirect(home_url('/comparador/'), 301);
        exit;
    }
    
    // Posts antiguos de blog
    if (preg_match('#^/\d{4}/\d{2}/\d{2}/#', $uri)) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
});

// === API REST: NOTICIAS ===
add_action('rest_api_init', function () {
    register_rest_route('labelme/v1', '/noticias', [
        'methods' => 'GET',
        'callback' => 'labelme_obtener_noticias',
        'permission_callback' => '__return_true',
    ]);
});

function labelme_obtener_noticias($request) {
    $feeds = [
        'Mundo Deportivo' => 'https://www.mundodeportivo.com/feed/rss/futbol/laliga',
        'Marca' => 'https://e00-marca.uecdn.es/rss/futbol/primera-division.xml',
        'AS' => 'https://as.com/rss/futbol/primera.xml',
    ];

    $limit = intval($request->get_param('limit')) ?: 6;
    $filtro = sanitize_text_field($request->get_param('filtro'));
    $noticias = [];

    foreach ($feeds as $fuente => $url) {
        $resp = wp_remote_get($url, ['timeout' => 10]);
        if (is_wp_error($resp)) continue;

        $xml = simplexml_load_string(wp_remote_retrieve_body($resp), 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) continue;

        foreach ($xml->channel->item as $item) {
            $titulo = (string)$item->title;
            $descripcion = (string)$item->description;

            if ($filtro && stripos($titulo . $descripcion, $filtro) === false) continue;

            $image = '';
            if (!empty($item->enclosure['url'])) {
                $image = (string)$item->enclosure['url'];
            } elseif (!empty($item->children('media', true)->content)) {
                $media = $item->children('media', true)->content;
                $image = (string)$media->attributes()->url;
            } elseif (preg_match('/<img[^>]+src="([^">]+)"/', $descripcion, $match)) {
                $image = $match[1];
            }

            // Guardar timestamp para ordenar correctamente
            $timestamp = strtotime((string)$item->pubDate);

            $noticias[] = [
                'fuente' => $fuente,
                'title' => (string)$item->title,
                'link' => (string)$item->link,
                'description' => wp_trim_words(wp_strip_all_tags($descripcion), 25, '...'),
                'image' => $image ?: '',
                'pubDate' => date_i18n('j M Y', $timestamp),
                'timestamp' => $timestamp,
            ];
        }
    }

    // Ordenar por timestamp descendente (más reciente primero)
    usort($noticias, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });

    // Remover timestamp antes de retornar
    $noticias = array_map(function($noticia) {
        unset($noticia['timestamp']);
        return $noticia;
    }, $noticias);

    return array_slice($noticias, 0, $limit);
}

// === API REST: BUSCAR JUGADORES ===
add_action('rest_api_init', function () {
    register_rest_route('labelme/v1', '/buscar-jugadores', array(
        'methods' => 'GET',
        'callback' => 'labelme_buscar_jugadores',
        'permission_callback' => '__return_true'
    ));
});

function labelme_buscar_jugadores($request) {
    global $wpdb;

    $busqueda = $request->get_param('q');
    if (empty($busqueda)) return [];

    // Buscar jugadores usando nombre_1 y apellido
    $jugadores = $wpdb->get_results($wpdb->prepare("
        SELECT
            nombre,
            foto,
            team_name,
            team_logo,
            posicion,
            'jugador' as tipo
        FROM jugadores_laliga
        WHERE season = '2025'
        AND (
            CONCAT(nombre_1, ' ', apellido) LIKE %s
            OR nombre LIKE %s
        )
        LIMIT 5
    ", '%' . $wpdb->esc_like($busqueda) . '%', '%' . $wpdb->esc_like($busqueda) . '%'));

    // Buscar equipos
    $equipos = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT
            team_name as nombre,
            team_logo as foto,
            team_name,
            team_logo,
            'equipo' as tipo,
            '' as posicion
        FROM jugadores_laliga
        WHERE season = '2025'
        AND team_name LIKE %s
        LIMIT 3
    ", '%' . $wpdb->esc_like($busqueda) . '%'));

    // Combinar resultados: equipos primero, luego jugadores
    return array_merge($equipos, $jugadores);
}

// === API REST: JUGADOR COMPLETO ===
add_action('rest_api_init', function () {
    register_rest_route('labelme/v1', '/jugador-completo', array(
        'methods' => 'GET',
        'callback' => 'labelme_jugador_completo',
        'permission_callback' => '__return_true'
    ));
});

function labelme_jugador_completo($request) {
    global $wpdb;
    
    $nombre = $request->get_param('nombre');
    if (empty($nombre)) return new WP_Error('no_nombre', 'Nombre requerido', array('status' => 400));
    
    $jugador = $wpdb->get_row($wpdb->prepare("
        SELECT * 
        FROM jugadores_laliga 
        WHERE nombre = %s AND season = '2025'
        LIMIT 1
    ", $nombre), ARRAY_A);
    
    return $jugador ?: new WP_Error('not_found', 'Jugador no encontrado', array('status' => 404));
}

// === API REST: ÚLTIMOS PARTIDOS DEL JUGADOR ===
add_action('rest_api_init', function () {
    register_rest_route('labelme/v1', '/jugador-ultimos-partidos', array(
        'methods' => 'GET',
        'callback' => 'labelme_jugador_ultimos_partidos',
        'permission_callback' => '__return_true'
    ));
});

function labelme_jugador_ultimos_partidos($request) {
    global $wpdb;
    
    $player_id = $request->get_param('player_id');
    if (empty($player_id)) {
        return new WP_Error('no_player', 'Player ID requerido', array('status' => 400));
    }
    
    $limit = intval($request->get_param('limit')) ?: 5;
    
    $partidos = $wpdb->get_results($wpdb->prepare("
        SELECT 
            fixture_id,
            fixture_date,
            opponent_team_name,
            minutes,
            rating,
            goals,
            assists,
            substitute,
            yellow_cards,
            red_cards,
            position
        FROM jugadores_fixtures_laliga
        WHERE player_id = %s AND season = 2025
        ORDER BY fixture_date DESC
        LIMIT %d
    ", $player_id, $limit), ARRAY_A);
    
    return $partidos;
}

// === API REST: EVOLUCIÓN DE RATING ===
add_action('rest_api_init', function () {
    register_rest_route('labelme/v1', '/jugador-evolucion-rating', array(
        'methods' => 'GET',
        'callback' => 'labelme_jugador_evolucion_rating',
        'permission_callback' => '__return_true'
    ));
});

function labelme_jugador_evolucion_rating($request) {
    global $wpdb;
    
    $player_id = $request->get_param('player_id');
    if (empty($player_id)) {
        return new WP_Error('no_player', 'Player ID requerido', array('status' => 400));
    }
    
    $limit = intval($request->get_param('limit')) ?: 10;
    
    $evolucion = $wpdb->get_results($wpdb->prepare("
        SELECT 
            fixture_date as fecha,
            rating,
            opponent_team_name as rival
        FROM jugadores_fixtures_laliga
        WHERE player_id = %s 
          AND season = 2025
          AND minutes > 0
        ORDER BY fixture_date ASC
        LIMIT %d
    ", $player_id, $limit), ARRAY_A);
    
    return $evolucion;
}

// === API REST: RECOMENDACIÓN FANTASY ===
add_action('rest_api_init', function () {
    register_rest_route('labelme/v1', '/jugador-recomendacion-fantasy', array(
        'methods' => 'GET',
        'callback' => 'labelme_jugador_recomendacion_fantasy',
        'permission_callback' => '__return_true'
    ));
});

function labelme_jugador_recomendacion_fantasy($request) {
    global $wpdb;
    
    $player_id = $request->get_param('player_id');
    if (empty($player_id)) {
        return new WP_Error('no_player', 'Player ID requerido', array('status' => 400));
    }
    
    // Obtener datos del jugador (lesiones)
    $jugador = $wpdb->get_row($wpdb->prepare("
        SELECT baja, rating, porcentaje_titularidades
        FROM jugadores_laliga
        WHERE id = %s AND season = 2025
    ", $player_id));
    
    if (!$jugador) {
        return array('probabilidad' => 0, 'nivel' => 'Desconocido');
    }
    
    // Si está lesionado → 0%
    if ($jugador->baja == 1) {
        return array(
            'probabilidad' => 0,
            'nivel' => 'Lesionado',
            'color' => '#ef4444',
            'icono' => '🚑'
        );
    }
    
    // Obtener últimos 5 partidos
    $ultimos = $wpdb->get_results($wpdb->prepare("
        SELECT substitute, rating, minutes
        FROM jugadores_fixtures_laliga
        WHERE player_id = %s AND season = 2025
        ORDER BY fixture_date DESC
        LIMIT 5
    ", $player_id));
    
    if (empty($ultimos)) {
        return array(
            'probabilidad' => 50,
            'nivel' => 'Sin datos',
            'color' => '#94a3b8',
            'icono' => '❓'
        );
    }
    
    // ALGORITMO DE CÁLCULO
    $score = 0;
    $peso_total = 0;
    
    // 1. Últimos 5 partidos (peso decreciente: 5, 4, 3, 2, 1)
    foreach ($ultimos as $index => $partido) {
        $peso = 5 - $index; // Más reciente = más peso
        
        // Titular = +10 puntos, Suplente = -5 puntos
        if ($partido->substitute == 0 && $partido->minutes > 0) {
            $score += 10 * $peso;
        } else {
            $score -= 5 * $peso;
        }
        
        // Rating bonus (>7.0 = buen rendimiento)
        $rating = floatval($partido->rating);
        if ($rating >= 7.5) {
            $score += 3 * $peso;
        } elseif ($rating >= 7.0) {
            $score += 1 * $peso;
        } elseif ($rating < 6.5 && $rating > 0) {
            $score -= 2 * $peso;
        }
        
        // Minutos jugados
        if ($partido->minutes >= 80) {
            $score += 2 * $peso;
        } elseif ($partido->minutes < 30 && $partido->minutes > 0) {
            $score -= 1 * $peso;
        }
        
        $peso_total += $peso;
    }
    
    // 2. Porcentaje histórico de titularidades (peso 30%)
    $historico = floatval($jugador->porcentaje_titularidades);
    $score += ($historico / 100) * 30;
    $peso_total += 30;
    
    // 3. Rating promedio (peso 20%)
    $rating_promedio = floatval($jugador->rating);
    if ($rating_promedio >= 7.5) {
        $score += 20;
    } elseif ($rating_promedio >= 7.0) {
        $score += 15;
    } elseif ($rating_promedio >= 6.5) {
        $score += 10;
    }
    $peso_total += 20;
    
    // Normalizar a escala 0-100
    $probabilidad = round(($score / $peso_total) * 100);
    $probabilidad = max(0, min(100, $probabilidad)); // Clamp entre 0-100
    
    // Determinar nivel y color
    if ($probabilidad >= 80) {
        $nivel = 'Muy Alta';
        $color = '#10b981';
        $icono = '🟢';
    } elseif ($probabilidad >= 60) {
        $nivel = 'Alta';
        $color = '#22c55e';
        $icono = '🟢';
    } elseif ($probabilidad >= 40) {
        $nivel = 'Media';
        $color = '#f59e0b';
        $icono = '🟡';
    } elseif ($probabilidad >= 20) {
        $nivel = 'Baja';
        $color = '#f97316';
        $icono = '🟠';
    } else {
        $nivel = 'Muy Baja';
        $color = '#ef4444';
        $icono = '🔴';
    }
    
    return array(
        'probabilidad' => $probabilidad,
        'nivel' => $nivel,
        'color' => $color,
        'icono' => $icono,
        'ultimos_partidos' => count($ultimos),
        'titularidades_historicas' => $historico
    );
}