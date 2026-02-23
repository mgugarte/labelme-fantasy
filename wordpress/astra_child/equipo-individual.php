<?php
/*
Template Name: Equipo Individual LaLiga
*/

$equipo_slug = get_query_var('equipo_slug');
if (!$equipo_slug) {
    wp_redirect(home_url('/jugadores/'));
    exit;
}

global $wpdb;

// Obtener TODOS los equipos y buscar el match correcto
$todos_equipos = $wpdb->get_results("
    SELECT DISTINCT team_name, team_logo 
    FROM jugadores_laliga 
    WHERE season = '2025'
");

// Buscar el equipo cuyo slug coincida
$equipo = null;
foreach ($todos_equipos as $eq) {
    if (labelme_sanitize_slug($eq->team_name) === $equipo_slug) {
        $equipo = $eq;
        break;
    }
}

// Si no se encuentra, redirigir
if (!$equipo) {
    wp_redirect(home_url('/jugadores/'));
    exit;
}

// Función para obtener badge de estado según tipo de baja
function obtener_badge_estado_equipo($baja, $tipo_baja) {
  if ($baja != 1) {
    return '<span class="badge-disponible">Disponible</span>';
  }

  $tipo_lower = strtolower($tipo_baja);

  // Duda - naranja
  if (stripos($tipo_lower, 'questionable') !== false || stripos($tipo_lower, 'duda') !== false || stripos($tipo_lower, 'doubt') !== false) {
    return '<span class="badge-duda">' . esc_html($tipo_baja) . '</span>';
  }
  // Sin Convocatoria / No convocado - rojo
  elseif (stripos($tipo_lower, 'coach') !== false ||
          stripos($tipo_lower, 'not in squad') !== false ||
          stripos($tipo_lower, 'sin convocatoria') !== false ||
          stripos($tipo_lower, 'no convocado') !== false) {
    return '<span class="badge-baja">' . esc_html($tipo_baja) . '</span>';
  }
  // Otros casos (lesión, sanción, etc.) - mostrar tipo_baja
  else {
    return '<span class="badge-baja">' . esc_html($tipo_baja) . '</span>';
  }
}

// Ahora obtener jugadores usando el team_name exacto
$jugadores = $wpdb->get_results($wpdb->prepare("
    SELECT id, nombre, foto, posicion, rating, minutos, goles_total, goles_asistencias,
           porcentaje_titularidades, recomendacion_fantasy, baja, tipo_baja, razon_baja
    FROM jugadores_laliga
    WHERE team_name = %s
    AND season = '2025'
    ORDER BY CAST(rating AS DECIMAL(10,2)) DESC
", $equipo->team_name));

// Calcular estadísticas del equipo
$total_jugadores = count($jugadores);
$total_goles = array_sum(array_column($jugadores, 'goles_total'));
$total_asistencias = array_sum(array_column($jugadores, 'goles_asistencias'));
$rating_promedio = $total_jugadores > 0 ? round(array_sum(array_column($jugadores, 'rating')) / $total_jugadores, 2) : 0;

// Generar título y descripción SEO
$titulo_seo = esc_html($equipo->team_name) . " - Plantilla y Estadísticas LaLiga Fantasy 2025 | LabelMe";
$descripcion_seo = "Plantilla completa de " . esc_html($equipo->team_name) . " para Fantasy LaLiga 2025: " . $total_jugadores . " jugadores, " . $total_goles . " goles, " . $total_asistencias . " asistencias. Estadísticas actualizadas.";

// Obtener todos los equipos para la barra superior
$equipos_barra = $wpdb->get_results("
    SELECT DISTINCT team_name, team_logo 
    FROM jugadores_laliga 
    WHERE season = '2025' 
    ORDER BY team_name
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">  <title><?php echo $titulo_seo; ?></title>
  <meta name="description" content="<?php echo $descripcion_seo; ?>">
  
  <!-- Open Graph -->
  <meta property="og:title" content="<?php echo $titulo_seo; ?>">
  <meta property="og:description" content="<?php echo $descripcion_seo; ?>">
  <meta property="og:image" content="<?php echo esc_url($equipo->team_logo); ?>">
  <meta property="og:type" content="website">
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="canonical" href="<?php echo home_url('/equipos/' . $equipo_slug . '/'); ?>">
  <?php wp_head(); ?>  
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-JMQXMFQLBE"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-JMQXMFQLBE');
  </script>
  <!-- Schema.org para SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Inicio",
      "item": "<?php echo home_url('/'); ?>"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Jugadores LaLiga",
      "item": "<?php echo home_url('/jugadores/'); ?>"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "<?php echo esc_js($equipo->team_name); ?>",
      "item": "<?php echo home_url('/equipos/' . $equipo_slug . '/'); ?>"
    }
  ]
}
</script>

<!-- Información del equipo para Google -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SportsTeam",
  "name": "<?php echo esc_js($equipo->team_name); ?>",
  "sport": "Soccer",
  "logo": "<?php echo esc_url($equipo->team_logo); ?>",
  "memberOf": {
    "@type": "SportsOrganization",
    "name": "LaLiga"
  },
  "url": "<?php echo home_url('/equipos/' . $equipo_slug . '/'); ?>"
}
</script>
</head>
<body>

<header class="header-modern">
  <div class="header-container">
    <a href="/" class="logo-link">
      <img src="http://labelme.es/wp-content/uploads/2025/05/labelMe_negro-removebg-preview.png" alt="LabelMe" class="logo-img">
    </a>
    
    <button class="menu-toggle" id="menuToggle" aria-label="Menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
    
    <nav class="nav-main" id="navMain">
      <a href="/" class="nav-link">Inicio</a>
      <a href="/jugadores/" class="nav-link">Jugadores</a>
      <a href="/comparador/" class="nav-link nav-link-cta">Comparador</a>
    </nav>
  </div>
</header>

<!-- BARRA SUPERIOR DE EQUIPOS -->
<div class="equipos-barra">
  <div class="equipos-barra-inner">
    <?php foreach ($equipos_barra as $eq): 
      $slug = labelme_sanitize_slug($eq->team_name);
      $es_activo = ($slug === $equipo_slug) ? 'activo' : '';
    ?>
      <a href="/equipos/<?php echo $slug; ?>/" title="<?php echo esc_attr($eq->team_name); ?>">
        <img src="<?php echo esc_url($eq->team_logo); ?>" 
             alt="<?php echo esc_attr($eq->team_name); ?>"
             class="equipo-logo-barra <?php echo $es_activo; ?>">
      </a>
    <?php endforeach; ?>
  </div>
</div>

<section class="equipo-header">
  <img src="<?php echo esc_url($equipo->team_logo); ?>" alt="<?php echo esc_attr($equipo->team_name); ?>" class="equipo-logo-grande">
  <h1><?php echo esc_html($equipo->team_name); ?></h1>
  <p>Plantilla LaLiga Fantasy 2024-25</p>
</section>

<!-- STATS DEL EQUIPO - 4 COLUMNAS -->
<div class="stats-equipo">
  <div class="stat-card">
    <div class="stat-label">Jugadores</div>
    <div class="stat-value"><?php echo $total_jugadores; ?></div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Goles Totales</div>
    <div class="stat-value"><?php echo $total_goles; ?></div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Asistencias</div>
    <div class="stat-value"><?php echo $total_asistencias; ?></div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Rating Promedio</div>
    <div class="stat-value"><?php echo $rating_promedio; ?></div>
  </div>
</div>

<div class="contenedor-jugadores">
  <div class="seccion-titulo">
    <h2>Plantilla Completa</h2>
  </div>

  <?php if ($jugadores): ?>
    <div class="tabla-jugadores-wrapper">
      <table class="tabla-jugadores">
        <thead>
          <tr>
            <th>Jugador</th>
            <th>Posición</th>
            <th>Rating</th>
            <th>Minutos</th>
            <th>Goles</th>
            <th>Asist.</th>
            <th>% Titular</th>
            <th>Recomendación Fantasy</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($jugadores as $j): 
          $slug_j = labelme_sanitize_slug($j->nombre);
          $url_j = home_url('/jugadores/' . $slug_j . '/');
        ?>
          <tr>
            <td>
              <a href="<?php echo esc_url($url_j); ?>" class="jugador-link">
                <img src="<?php echo esc_url($j->foto); ?>" class="jugador-img" alt="<?php echo esc_attr($j->nombre); ?>">
                <?php echo esc_html($j->nombre); ?>
              </a>
            </td>
            <td><?php echo esc_html($j->posicion); ?></td>
            <td><strong><?php echo esc_html($j->rating); ?></strong></td>
            <td><?php echo esc_html($j->minutos); ?></td>
            <td><?php echo esc_html($j->goles_total); ?></td>
            <td><?php echo esc_html($j->goles_asistencias); ?></td>
            <td><?php echo esc_html($j->porcentaje_titularidades); ?>%</td>
            <td>
              <?php
              $recomendacion = (int)$j->recomendacion_fantasy;

              // Determinar estilo de medalla
              if ($recomendacion >= 90) {
                  $backgroundColor = 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%)';
                  $medalStyle = 'box-shadow: 0 2px 8px rgba(251, 191, 36, 0.4); border: 2px solid #fef3c7;';
                  $nivel = 'Oro (90-100%)';
              } elseif ($recomendacion >= 70) {
                  $backgroundColor = 'linear-gradient(135deg, #e5e7eb 0%, #9ca3af 50%, #6b7280 100%)';
                  $medalStyle = 'box-shadow: 0 2px 8px rgba(156, 163, 175, 0.4); border: 2px solid #f3f4f6;';
                  $nivel = 'Plata (70-89%)';
              } elseif ($recomendacion >= 40) {
                  $backgroundColor = 'linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%)';
                  $medalStyle = 'box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4); border: 2px solid #fef3c7;';
                  $nivel = 'Bronce (40-69%)';
              } else {
                  $backgroundColor = 'linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%)';
                  $medalStyle = 'box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4); border: 2px solid #fee2e2;';
                  $nivel = 'Riesgo (0-39%)';
              }
              ?>
              <div style="
                  display: inline-flex;
                  align-items: center;
                  justify-content: center;
                  width: 45px;
                  height: 45px;
                  border-radius: 50%;
                  background: <?php echo $backgroundColor; ?>;
                  <?php echo $medalStyle; ?>
                  font-weight: 800;
                  font-size: 0.9rem;
                  color: white;
                  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
                  cursor: help;
              " title="<?php echo esc_attr($nivel); ?>">
                  <?php echo $recomendacion; ?>%
              </div>
            </td>
            <td>
              <?php echo obtener_badge_estado_equipo($j->baja, $j->tipo_baja); ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p style="text-align: center; padding: 2rem;">No hay jugadores disponibles para este equipo.</p>
  <?php endif; ?>

  <div style="text-align: center; margin-top: 2rem;">
    <a href="/jugadores/" class="btn-volver">← Volver al listado completo</a>
  </div>
</div>

<footer>
  &copy; 2025 LabelMe. Estadísticas Fantasy LaLiga - Datos actualizados
</footer>

<script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/cc1120e60af0020f9479833b/script.js"></script>
<button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
  🌙
</button>

<script>
// ========================================
// MENÚ HAMBURGUESA
// ========================================
document.addEventListener('DOMContentLoaded', function() {
  const menuToggle = document.getElementById('menuToggle');
  const navMain = document.getElementById('navMain');

  if (menuToggle && navMain) {
    menuToggle.addEventListener('click', function() {
      navMain.classList.toggle('active');
      menuToggle.classList.toggle('active');
    });

    // Cerrar menú al hacer click fuera
    document.addEventListener('click', function(event) {
      const isClickInside = menuToggle.contains(event.target) || navMain.contains(event.target);
      if (!isClickInside && navMain.classList.contains('active')) {
        navMain.classList.remove('active');
        menuToggle.classList.remove('active');
      }
    });

    // Cerrar menú al hacer click en un enlace
    const navLinks = navMain.querySelectorAll('a');
    navLinks.forEach(link => {
      link.addEventListener('click', function() {
        navMain.classList.remove('active');
        menuToggle.classList.remove('active');
      });
    });
  }
});

// ========================================
// TEMA DARK/LIGHT
// ========================================
function toggleTheme() {
  document.body.classList.toggle('dark-mode');
  const isDark = document.body.classList.contains('dark-mode');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
  document.querySelector('.theme-toggle').textContent = isDark ? '☀️' : '🌙';
}

// Cargar tema guardado
if (localStorage.getItem('theme') === 'dark') {
  document.body.classList.add('dark-mode');
  document.querySelector('.theme-toggle').textContent = '☀️';
}
</script>
</body>
</html>