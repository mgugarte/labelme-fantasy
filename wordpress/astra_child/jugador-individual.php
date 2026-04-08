<?php
/*
Template Name: Jugador Individual LaLiga
*/

// Obtener el slug del jugador desde la URL
$slug_jugador = get_query_var('jugador_slug');

if (!$slug_jugador) {
  wp_redirect(home_url('/jugadores/'));
  exit;
}

global $wpdb;

// Buscar jugador por slug (nombre convertido a URL-friendly)
$jugador = $wpdb->get_row($wpdb->prepare("
  SELECT id, nombre, apellido, edad, nacionalidad, altura, peso, foto, 
         team_name as equipo, team_logo, posicion, 
         minutos, partidos, titular, suplente, rating,
         porcentaje_titularidades, porcentaje_participacion,
         goles_total, goles_asistencias, 
         shots_total, shots_on,
         passes_total, passes_key, passes_accuracy,
         tackles_total, tackles_blocks, tackles_interceptions,
         duels_total, duels_won,
         dribbles_attempts, dribbles_success,
         fouls_drawn, fouls_committed,
         penalty_won, penalty_commited, penalty_scored, penalty_missed,
         amarillas, rojas, sancionado,
         sub_in, sub_out, sub_bench,
         recomendacion_fantasy,
         baja, tipo_baja, razon_baja
  FROM jugadores_laliga
  WHERE REPLACE(REPLACE(LOWER(nombre), ' ', '-'), '.', '') = %s
  AND season = '2025'
  LIMIT 1
", $slug_jugador));

if (!$jugador) {
  status_header(404);
  wp_redirect(home_url('/jugadores/'));
  exit;
}

// Función para obtener badge de estado según razón de baja
function obtener_badge_estado_jugador($baja, $tipo_baja) {
  if ($baja != 1) {
    return ['class' => 'badge-disponible', 'icon' => '✓', 'text' => 'DISPONIBLE'];
  }

  $razon = strtolower($tipo_baja);

  // SANCIONADO: suspensiones, tarjetas, etc.
  if (stripos($razon, 'suspended') !== false ||
      stripos($razon, 'yellow card') !== false ||
      stripos($razon, 'red card') !== false ||
      stripos($razon, 'accumulation') !== false ||
      stripos($razon, 'sancion') !== false ||
      stripos($razon, 'tarjeta') !== false) {
    return ['class' => 'badge-suspension', 'icon' => '🟥', 'text' => 'SANCIONADO'];
  }

  // LESIONADO: lesiones físicas
  if (stripos($razon, 'injur') !== false ||
      stripos($razon, 'lesion') !== false ||
      stripos($razon, 'lesión') !== false ||
      stripos($razon, 'knee') !== false ||
      stripos($razon, 'ankle') !== false ||
      stripos($razon, 'muscle') !== false ||
      stripos($razon, 'hamstring') !== false ||
      stripos($razon, 'calf') !== false ||
      stripos($razon, 'thigh') !== false ||
      stripos($razon, 'back') !== false ||
      stripos($razon, 'groin') !== false ||
      stripos($razon, 'achilles') !== false ||
      stripos($razon, 'fracture') !== false ||
      stripos($razon, 'surgery') !== false ||
      stripos($razon, 'illness') !== false ||
      stripos($razon, 'knock') !== false ||
      stripos($razon, 'broke') !== false ||
      stripos($razon, 'broken') !== false ||
      stripos($razon, 'jumper') !== false ||
      stripos($razon, 'nose') !== false) {
    return ['class' => 'badge-baja', 'icon' => '🏥', 'text' => 'LESIONADO'];
  }

  // OTROS: resto de casos (inactive, rest, coach, international, etc.)
  return ['class' => 'badge-tecnica', 'icon' => '⚠️', 'text' => 'OTROS'];
}

// Generar título SEO optimizado
$titulo_seo = esc_html($jugador->nombre) . " - Estadísticas LaLiga Fantasy 2025 | LabelMe";
$descripcion_seo = "Estadísticas completas de " . esc_html($jugador->nombre) . " (" . esc_html($jugador->equipo) . "): " . $jugador->goles_total . " goles, " . $jugador->goles_asistencias . " asistencias, rating " . $jugador->rating . ". Datos actualizados Fantasy LaLiga 2025.";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $titulo_seo; ?></title>
  <meta name="description" content="<?php echo $descripcion_seo; ?>">
  
  <!-- Open Graph para redes sociales -->
  <meta property="og:title" content="<?php echo $titulo_seo; ?>">
  <meta property="og:description" content="<?php echo $descripcion_seo; ?>">
  <meta property="og:image" content="<?php echo esc_url($jugador->foto); ?>">
  <meta property="og:type" content="profile">
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/style_labelme.css" />
  <link rel="canonical" href="<?php echo home_url('/jugadores/' . $slug_jugador . '/'); ?>">
  <?php wp_head(); ?>  
  <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-JMQXMFQLBE"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-JMQXMFQLBE');
</script>
  <!-- Schema.org Breadcrumbs -->
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
      "name": "<?php echo esc_js($jugador->nombre); ?>",
      "item": "<?php echo home_url('/jugadores/' . $slug_jugador . '/'); ?>"
    }
  ]
}
</script>

<!-- Información del jugador -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Person",
  "name": "<?php echo esc_js($jugador->nombre); ?>",
  "image": "<?php echo esc_url($jugador->foto); ?>",
  "jobTitle": "<?php echo esc_js($jugador->posicion); ?>",
  "affiliation": {
    "@type": "SportsTeam",
    "name": "<?php echo esc_js($jugador->equipo); ?>",
    "logo": "<?php echo esc_url($jugador->team_logo); ?>"
  },
  "nationality": "<?php echo esc_js($jugador->nacionalidad); ?>",
  "birthDate": "<?php echo ($jugador->edad ? date('Y') - $jugador->edad : ''); ?>",  
  "height": "<?php echo esc_js($jugador->altura); ?> cm",
  "weight": "<?php echo esc_js($jugador->peso); ?> kg"
}
</script>
</head>
<body>

<header class="header-modern">
  <div class="header-container">
    <a href="/" class="logo-link">
      <img src="http://labelme.es/wp-content/uploads/2026/02/logo_beta.png" alt="LabelMe" class="logo-img">
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

<?php
// OBTENER EQUIPOS PARA LA BARRA SUPERIOR
$equipos_barra = $wpdb->get_results("
  SELECT DISTINCT team_name, team_logo 
  FROM jugadores_laliga 
  WHERE season = '2025' 
  ORDER BY team_name
");
?>

<!-- BARRA SUPERIOR DE EQUIPOS -->
<div class="equipos-barra">
  <div class="equipos-barra-inner">
    <?php foreach ($equipos_barra as $equipo): ?>
        <a href="/equipos/<?php echo labelme_sanitize_slug($equipo->team_name); ?>/" 
           title="<?php echo esc_attr($equipo->team_name); ?>"
           class="equipo-logo-barra">
            <img src="<?php echo esc_url($equipo->team_logo); ?>" 
                 alt="<?php echo esc_attr($equipo->team_name); ?>"
                 width="40" height="40">
        </a>
    <?php endforeach; ?>
  </div>
</div>

<section class="jugador-header">
  <div class="jugador-info-principal">
    <?php
    // Determinar la corona según la recomendación fantasy
    $recomendacion_valor = (int)$jugador->recomendacion_fantasy;
    $corona_img = '';
    $corona_alt = '';

    // Verificar si está lesionado - PRIORIDAD sobre recomendación
    $esta_lesionado = false;
    if ($jugador->baja == 1) {
      $razon = strtolower($jugador->tipo_baja . ' ' . $jugador->razon_baja);
      if (stripos($razon, 'injur') !== false ||
          stripos($razon, 'lesion') !== false ||
          stripos($razon, 'lesión') !== false ||
          stripos($razon, 'knee') !== false ||
          stripos($razon, 'ankle') !== false ||
          stripos($razon, 'muscle') !== false ||
          stripos($razon, 'hamstring') !== false ||
          stripos($razon, 'calf') !== false ||
          stripos($razon, 'thigh') !== false ||
          stripos($razon, 'back') !== false ||
          stripos($razon, 'groin') !== false ||
          stripos($razon, 'achilles') !== false ||
          stripos($razon, 'fracture') !== false ||
          stripos($razon, 'surgery') !== false ||
          stripos($razon, 'illness') !== false ||
          stripos($razon, 'knock') !== false ||
          stripos($razon, 'broke') !== false ||
          stripos($razon, 'broken') !== false ||
          stripos($razon, 'hip') !== false ||
          stripos($razon, 'shoulder') !== false ||
          stripos($razon, 'arm') !== false ||
          stripos($razon, 'leg') !== false ||
          stripos($razon, 'foot') !== false ||
          stripos($razon, 'head') !== false) {
        $esta_lesionado = true;
      }
    }

    // Si está lesionado, siempre mostrar Nurse
    if ($esta_lesionado) {
      $corona_img = 'Nurse.png';
      $corona_alt = 'Jugador Lesionado';
    } elseif ($recomendacion_valor >= 90) {
      // Oro
      $corona_img = 'King.png';
      $corona_alt = 'Jugador Oro';
    } elseif ($recomendacion_valor >= 70) {
      // Plata
      $corona_img = 'Prince.png';
      $corona_alt = 'Jugador Plata';
    } elseif ($recomendacion_valor >= 40) {
      // Bronce
      $corona_img = 'Gorra.png';
      $corona_alt = 'Jugador Bronce';
    } elseif ($recomendacion_valor < 20) {
      // Riesgo Alto (0-19%)
      $corona_img = 'Potato.png';
      $corona_alt = 'Riesgo Alto (0-19%)';
    } else {
      // Riesgo Medio (20-39%)
      $corona_img = 'Bufon.png';
      $corona_alt = 'Riesgo Medio (20-39%)';
    }
    ?>
    <div class="jugador-foto-container">
      <img src="<?php echo esc_url($jugador->foto); ?>" alt="<?php echo esc_attr($jugador->nombre); ?>" class="jugador-foto-grande">
      <?php if ($corona_img): ?>
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/coronas/<?php echo $corona_img; ?>"
             alt="<?php echo esc_attr($corona_alt); ?>"
             class="jugador-corona"
             title="<?php echo esc_attr($corona_alt); ?>">
      <?php endif; ?>
    </div>

    <div class="jugador-datos">
      <h1><?php echo esc_html($jugador->nombre); ?></h1>

      <div class="equipo">
        <img src="<?php echo esc_url($jugador->team_logo); ?>" alt="<?php echo esc_attr($jugador->equipo); ?>">
        <span><?php echo esc_html($jugador->equipo); ?></span>
      </div>

      <p>Posición: <strong><?php echo esc_html($jugador->posicion); ?></strong></p>

      <div class="jugador-estado-medalla">
        <?php
        $badge_info = obtener_badge_estado_jugador($jugador->baja, $jugador->razon_baja);
        ?>
        <div class="badge-estado <?php echo $badge_info['class']; ?>">
          <?php echo $badge_info['icon']; ?> <?php echo $badge_info['text']; ?>
          <?php if ($jugador->baja == 1): ?>
            - <?php echo esc_html($jugador->tipo_baja); ?>
            <?php if ($jugador->razon_baja): ?>
              <br><small><?php echo esc_html($jugador->razon_baja); ?></small>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <!-- Medalla de recomendación fantasy -->
        <div class="medalla-recomendacion">
          <?php
          $recomendacion = (int)$jugador->recomendacion_fantasy;

          // Determinar estilo de medalla
          if ($recomendacion >= 90) {
              $backgroundColor = 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%)';
              $medalStyle = 'box-shadow: 0 4px 12px rgba(251, 191, 36, 0.5); border: 3px solid #fef3c7;';
              $nivel = 'Oro (90-100%)';
          } elseif ($recomendacion >= 70) {
              $backgroundColor = 'linear-gradient(135deg, #e5e7eb 0%, #9ca3af 50%, #6b7280 100%)';
              $medalStyle = 'box-shadow: 0 4px 12px rgba(156, 163, 175, 0.5); border: 3px solid #f3f4f6;';
              $nivel = 'Plata (70-89%)';
          } elseif ($recomendacion >= 40) {
              $backgroundColor = 'linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%)';
              $medalStyle = 'box-shadow: 0 4px 12px rgba(245, 158, 11, 0.5); border: 3px solid #fef3c7;';
              $nivel = 'Bronce (40-69%)';
          } else {
              $backgroundColor = 'linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%)';
              $medalStyle = 'box-shadow: 0 4px 12px rgba(239, 68, 68, 0.5); border: 3px solid #fee2e2;';
              $nivel = 'Riesgo (0-39%)';
          }
          ?>
          <div class="medalla-circulo" style="background: <?php echo $backgroundColor; ?>; <?php echo $medalStyle; ?>" title="Recomendación Fantasy: <?php echo esc_attr($nivel); ?>">
              <?php echo $recomendacion; ?>%
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="seccion-info">
  <h2>💰 Valor de Mercado por App</h2>
  <div id="valores-mercado-container">
    <div class="loading-skeleton" style="height: 120px; border-radius: 12px;"></div>
  </div>
</div>

<!-- ANÁLISIS FANTASY (BASADO EN ÚLTIMOS 5 PARTIDOS) -->
<div class="seccion-info" id="analisis-fantasy-container">
  <h2>Análisis Fantasy para <?php echo esc_html($jugador->nombre); ?> (Últimos 5 Partidos)</h2>
  <div class="loading-skeleton" style="height: 300px; border-radius: 12px;"></div>
</div>

<!-- TABLA DE ESTADÍSTICAS COMPLETA -->
<div class="seccion-info">
  <h2>Estadísticas Completas</h2>

  <!-- Estadísticas de últimos 5 partidos -->
  <div id="stats-ultimos-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2.5rem; border-radius: 16px; margin-bottom: 2rem; box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);">
    <h3 style="margin: 0 0 2rem 0; color: white; font-size: 1.3rem; font-weight: 700; text-align: center; text-transform: uppercase; letter-spacing: 1px;">⚡ Forma Reciente (Últimos 5 Partidos)</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem;">
      <div style="text-align: center;">
        <div class="loading-skeleton" style="height: 80px; border-radius: 12px;"></div>
      </div>
    </div>
  </div>

  <!-- Estadísticas en formato moderno de cards -->

  <!-- GENERAL -->
  <div style="margin-bottom: 2.5rem;">
    <h3 style="color: #1e293b; font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
      <span style="display: inline-block; width: 4px; height: 24px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 2px;"></span>
      📊 Estadísticas Generales
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.25rem;">
      <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #3b82f6;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">⭐ Rating</div>
        <div style="font-size: 2rem; font-weight: 900; color: #1e40af;"><?php echo esc_html($jugador->rating); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">⏱️ Minutos</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->minutos); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🏟️ Partidos</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->partidos); ?></div>
      </div>
      <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">✅ Titular</div>
        <div style="font-size: 2rem; font-weight: 900; color: #065f46;"><?php echo esc_html($jugador->titular); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🔄 Suplente</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->suplente); ?></div>
      </div>
      <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">📈 % Titular</div>
        <div style="font-size: 2rem; font-weight: 900; color: #065f46;"><?php echo esc_html($jugador->porcentaje_titularidades); ?>%</div>
      </div>
    </div>
  </div>

  <!-- OFENSIVAS -->
  <div style="margin-bottom: 2.5rem;">
    <h3 style="color: #1e293b; font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
      <span style="display: inline-block; width: 4px; height: 24px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 2px;"></span>
      ⚽ Estadísticas Ofensivas
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.25rem;">
      <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #10b981; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">⚽ Goles</div>
        <div style="font-size: 2rem; font-weight: 900; color: #065f46;"><?php echo esc_html($jugador->goles_total); ?></div>
      </div>
      <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🎯 Asistencias</div>
        <div style="font-size: 2rem; font-weight: 900; color: #1e40af;"><?php echo esc_html($jugador->goles_asistencias); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🎯 Disparos</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->shots_total); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🎯 A Puerta</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->shots_on); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🔑 Pases Clave</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->passes_key); ?></div>
      </div>
      <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">📊 Precisión</div>
        <div style="font-size: 2rem; font-weight: 900; color: #92400e;"><?php echo esc_html($jugador->passes_accuracy); ?>%</div>
      </div>
    </div>
  </div>

  <!-- DEFENSIVAS -->
  <div style="margin-bottom: 2.5rem;">
    <h3 style="color: #1e293b; font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
      <span style="display: inline-block; width: 4px; height: 24px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 2px;"></span>
      🛡️ Estadísticas Defensivas
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.25rem;">
      <div style="background: linear-gradient(135deg, #f5f3ff, #ede9fe); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🦵 Entradas</div>
        <div style="font-size: 2rem; font-weight: 900; color: #6b21a8;"><?php echo esc_html($jugador->tackles_total); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🚫 Bloqueos</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->tackles_blocks); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">✋ Intercepciones</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->tackles_interceptions); ?></div>
      </div>
      <div style="background: linear-gradient(135deg, #f5f3ff, #ede9fe); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">💪 Duelos +</div>
        <div style="font-size: 2rem; font-weight: 900; color: #6b21a8;"><?php echo esc_html($jugador->duels_won); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">⚔️ Duelos Tot</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->duels_total); ?></div>
      </div>
      <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">📊 % Duelos</div>
        <div style="font-size: 2rem; font-weight: 900; color: #92400e;">
          <?php echo $jugador->duels_total > 0 ? round(($jugador->duels_won / $jugador->duels_total) * 100) : 0; ?>%
        </div>
      </div>
    </div>
  </div>

  <!-- DISCIPLINA Y OTROS -->
  <div>
    <h3 style="color: #1e293b; font-size: 1.2rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
      <span style="display: inline-block; width: 4px; height: 24px; background: linear-gradient(135deg, #ef4444, #dc2626); border-radius: 2px;"></span>
      ⚠️ Disciplina y Otros
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.25rem;">
      <div style="background: <?php echo $jugador->amarillas >= 5 ? 'linear-gradient(135deg, #fef3c7, #fde68a)' : '#f8fafc'; ?>; padding: 1.5rem; border-radius: 12px; border-left: 4px solid <?php echo $jugador->amarillas >= 5 ? '#f59e0b' : '#64748b'; ?>;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🟨 Amarillas</div>
        <div style="font-size: 2rem; font-weight: 900; color: <?php echo $jugador->amarillas >= 5 ? '#92400e' : '#334155'; ?>;"><?php echo esc_html($jugador->amarillas); ?></div>
      </div>
      <div style="background: <?php echo $jugador->rojas > 0 ? 'linear-gradient(135deg, #fee2e2, #fecaca)' : '#f8fafc'; ?>; padding: 1.5rem; border-radius: 12px; border-left: 4px solid <?php echo $jugador->rojas > 0 ? '#ef4444' : '#64748b'; ?>;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">🟥 Rojas</div>
        <div style="font-size: 2rem; font-weight: 900; color: <?php echo $jugador->rojas > 0 ? '#991b1b' : '#334155'; ?>;"><?php echo esc_html($jugador->rojas); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">❌ Faltas Com.</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->fouls_committed); ?></div>
      </div>
      <div style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border-left: 4px solid #64748b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">✅ Faltas Rec.</div>
        <div style="font-size: 2rem; font-weight: 900; color: #334155;"><?php echo esc_html($jugador->fouls_drawn); ?></div>
      </div>
      <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">💨 Regates</div>
        <div style="font-size: 2rem; font-weight: 900; color: #92400e;"><?php echo esc_html($jugador->dribbles_success); ?></div>
      </div>
      <div style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe); padding: 1.5rem; border-radius: 12px; border-left: 4px solid #6366f1;">
        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.5rem; font-weight: 600;">👤 Físico</div>
        <div style="font-size: 0.95rem; font-weight: 700; color: #4338ca; line-height: 1.4;">
          <?php echo esc_html($jugador->edad); ?> años<br>
          <?php echo esc_html($jugador->altura); ?>cm · <?php echo esc_html($jugador->peso); ?>kg
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ÚLTIMOS 5 PARTIDOS -->
<div class="seccion-info">
  <h2>Últimos Partidos</h2>
  <div id="ultimos-partidos-container">
    <div class="loading-skeleton" style="height: 200px;"></div>
  </div>
</div>

<!-- GRÁFICO DE EVOLUCIÓN -->
<div class="seccion-info">
  <h2>Evolución del Rating</h2>
  <div style="position: relative; height: 300px;">
    <canvas id="ratingChart"></canvas>
  </div>
</div>

<!-- CARGAR CHART.JS (añadir en el <head> o antes del </body>) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ID del jugador (ya disponible en jugador-individual.php)
const playerId = <?php echo $jugador->id; ?>;

// ========================================
// CARGAR ÚLTIMOS 5 PARTIDOS
// ========================================
async function cargarUltimosPartidos() {
  try {
    const response = await fetch(`/wp-json/labelme/v1/jugador-ultimos-partidos?player_id=${playerId}&limit=5`);
    const partidos = await response.json();
    
    const container = document.getElementById('ultimos-partidos-container');
    
    if (!partidos || partidos.length === 0) {
      container.innerHTML = '<p style="text-align: center; color: #64748b;">No hay datos de partidos disponibles</p>';
      return;
    }
    
    // Generar HTML de los últimos partidos
    let html = '<div style="display: grid; gap: 1rem;">';
    
    partidos.forEach(partido => {
      const fecha = new Date(partido.fixture_date).toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
      const esTitular = partido.substitute == 0 && partido.minutes > 0;
      const jugó = partido.minutes > 0;
      
      // Determinar color del rating
      let ratingColor = '#64748b';
      if (partido.rating >= 7.5) ratingColor = '#10b981';
      else if (partido.rating >= 7.0) ratingColor = '#22c55e';
      else if (partido.rating >= 6.5) ratingColor = '#f59e0b';
      else if (partido.rating < 6.5 && partido.rating > 0) ratingColor = '#ef4444';
      
      html += `
        <div style="background: white; padding: 1.5rem; border-radius: 12px; border-left: 4px solid ${jugó ? (esTitular ? '#10b981' : '#f59e0b') : '#ef4444'}; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1rem;">
          <!-- Header: Fecha, Rival y Badge en una línea compacta -->
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
            <div style="flex: 1; min-width: 200px;">
              <div style="font-size: 0.75rem; color: #94a3b8; margin-bottom: 0.25rem; font-weight: 600;">${fecha}</div>
              <div style="font-size: 1.1rem; font-weight: 700; color: #1e293b;">vs ${partido.opponent_team_name}</div>
            </div>
            <div>
              ${esTitular
                ? '<span style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.2);">✓ TITULAR</span>'
                : jugó
                  ? '<span style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.2);">↑ SUPLENTE</span>'
                  : '<span style="background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.2);">✗ NO JUGÓ</span>'
              }
            </div>
          </div>

          <!-- Stats en grid compacto -->
          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
            <div style="text-align: center; background: ${ratingColor === '#10b981' ? 'linear-gradient(135deg, #ecfdf5, #d1fae5)' : ratingColor === '#22c55e' ? 'linear-gradient(135deg, #f0fdf4, #dcfce7)' : ratingColor === '#f59e0b' ? 'linear-gradient(135deg, #fffbeb, #fef3c7)' : 'linear-gradient(135deg, #fef2f2, #fee2e2)'}; padding: 0.5rem; border-radius: 8px;">
              <div style="font-size: 0.6rem; color: #64748b; margin-bottom: 0.2rem; font-weight: 700; text-transform: uppercase;">Rating</div>
              <div style="font-size: 1.5rem; font-weight: 900; color: ${ratingColor}; line-height: 1;">
                ${jugó ? (partido.rating || '-') : '-'}
              </div>
            </div>

            <div style="text-align: center; background: #f8fafc; padding: 0.5rem; border-radius: 8px;">
              <div style="font-size: 0.6rem; color: #64748b; margin-bottom: 0.2rem; font-weight: 700; text-transform: uppercase;">Min</div>
              <div style="font-size: 1.5rem; font-weight: 900; color: #475569; line-height: 1;">
                ${partido.minutes}'
              </div>
            </div>

            <div style="text-align: center; background: linear-gradient(135deg, #ecfdf5, #d1fae5); padding: 0.5rem; border-radius: 8px;">
              <div style="font-size: 0.6rem; color: #64748b; margin-bottom: 0.2rem; font-weight: 700; text-transform: uppercase;">Goles</div>
              <div style="font-size: 1.5rem; font-weight: 900; color: #059669; line-height: 1;">
                ${partido.goals || 0}
              </div>
            </div>

            <div style="text-align: center; background: linear-gradient(135deg, #eff6ff, #dbeafe); padding: 0.5rem; border-radius: 8px;">
              <div style="font-size: 0.6rem; color: #64748b; margin-bottom: 0.2rem; font-weight: 700; text-transform: uppercase;">Asist</div>
              <div style="font-size: 1.5rem; font-weight: 900; color: #2563eb; line-height: 1;">
                ${partido.assists || 0}
              </div>
            </div>
          </div>
          ${(partido.yellow_cards > 0 || partido.red_cards > 0)
            ? `<div style="text-align: center; background: #fef2f2; padding: 0.5rem; border-radius: 8px; margin-top: 0.5rem; display: inline-block;">
                <span style="font-size: 1.2rem;">
                  ${partido.yellow_cards > 0 ? '🟨'.repeat(partido.yellow_cards) : ''}
                  ${partido.red_cards > 0 ? '🟥'.repeat(partido.red_cards) : ''}
                </span>
               </div>`
            : ''
          }
        </div>
      `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
  } catch (error) {
    console.error('Error cargando últimos partidos:', error);
    document.getElementById('ultimos-partidos-container').innerHTML = 
      '<p style="text-align: center; color: #ef4444;">Error al cargar datos</p>';
  }
}

// ========================================
// CARGAR STATS DE ÚLTIMOS 5 PARTIDOS
// ========================================
async function cargarStatsUltimos5() {
  try {
    const response = await fetch(`/wp-json/labelme/v1/jugador-ultimos-partidos?player_id=${playerId}&limit=5`);
    const partidos = await response.json();

    const container = document.getElementById('stats-ultimos-5').querySelector('div[style*="grid"]');

    if (!partidos || partidos.length === 0) {
      container.innerHTML = '<p style="text-align: center; color: #64748b;">No hay datos disponibles</p>';
      return;
    }

    // Calcular estadísticas - solo contar partidos donde el jugador estuvo en la convocatoria
    const partidosJugados = partidos.filter(p => p.minutes > 0 || p.substitute !== null);
    const titulares = partidos.filter(p => p.substitute == 0 && p.minutes > 0).length;
    const titularidadPct = partidosJugados.length > 0
      ? Math.round((titulares / partidosJugados.length) * 100)
      : 0;

    const ratingsValidos = partidos.filter(p => p.rating > 0).map(p => parseFloat(p.rating));
    const ratingPromedio = ratingsValidos.length > 0
      ? (ratingsValidos.reduce((a, b) => a + b, 0) / ratingsValidos.length).toFixed(1)
      : 0;

    const golesUltimos5 = partidos.reduce((sum, p) => sum + (parseInt(p.goals) || 0), 0);
    const asistUltimos5 = partidos.reduce((sum, p) => sum + (parseInt(p.assists) || 0), 0);

    // Calcular minutos promedio solo de partidos donde realmente jugó
    const partidosConMinutos = partidos.filter(p => p.minutes > 0);
    const minutosPromedio = partidosConMinutos.length > 0
      ? Math.round(partidosConMinutos.reduce((sum, p) => sum + parseInt(p.minutes), 0) / partidosConMinutos.length)
      : 0;

    const ratingColor = ratingPromedio >= 7.0 ? '#34d399' : (ratingPromedio >= 6.5 ? '#fbbf24' : '#f87171');
    const titularidadColor = titularidadPct >= 80 ? '#34d399' : (titularidadPct >= 50 ? '#fbbf24' : '#f87171');

    container.innerHTML = `
      <div style="text-align: center; background: rgba(255,255,255,0.95); padding: 1.25rem 1rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s;">
        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Rating Promedio</div>
        <div style="font-size: 2.5rem; font-weight: 900; color: ${ratingColor}; line-height: 1;">${ratingPromedio}</div>
      </div>

      <div style="text-align: center; background: rgba(255,255,255,0.95); padding: 1.25rem 1rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s;">
        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">% Titular</div>
        <div style="font-size: 2.5rem; font-weight: 900; color: ${titularidadColor}; line-height: 1; display: flex; align-items: center; justify-content: center;">${titularidadPct}%</div>
      </div>

      <div style="text-align: center; background: rgba(255,255,255,0.95); padding: 1.25rem 1rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s;">
        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Goles</div>
        <div style="font-size: 2.5rem; font-weight: 900; color: #10b981; line-height: 1;">${golesUltimos5}</div>
      </div>

      <div style="text-align: center; background: rgba(255,255,255,0.95); padding: 1.25rem 1rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s;">
        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Asistencias</div>
        <div style="font-size: 2.5rem; font-weight: 900; color: #3b82f6; line-height: 1;">${asistUltimos5}</div>
      </div>

      <div style="text-align: center; background: rgba(255,255,255,0.95); padding: 1.25rem 1rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s;">
        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Min. Promedio</div>
        <div style="font-size: 2.5rem; font-weight: 900; color: #8b5cf6; line-height: 1;">${minutosPromedio}'</div>
      </div>
    `;

  } catch (error) {
    console.error('Error cargando stats últimos 5:', error);
  }
}

// ========================================
// CARGAR GRÁFICO DE EVOLUCIÓN
// ========================================
async function cargarGraficoRating() {
  try {
    const response = await fetch(`/wp-json/labelme/v1/jugador-ultimos-partidos?player_id=${playerId}&limit=10`);
    const partidos = await response.json();

    if (!partidos || partidos.length === 0) {
      document.querySelector('#ratingChart').parentElement.innerHTML =
        '<p style="text-align: center; color: #64748b; padding: 2rem;">No hay suficientes datos para mostrar evolución</p>';
      return;
    }

    // Filtrar solo partidos con rating válido y ordenar por fecha
    const partidosValidos = partidos
      .filter(p => p.rating && parseFloat(p.rating) > 0)
      .sort((a, b) => new Date(a.fixture_date) - new Date(b.fixture_date));

    if (partidosValidos.length === 0) {
      document.querySelector('#ratingChart').parentElement.innerHTML =
        '<p style="text-align: center; color: #64748b; padding: 2rem;">No hay suficientes datos para mostrar evolución</p>';
      return;
    }

    // Preparar datos para Chart.js
    const labels = partidosValidos.map(p => {
      const fecha = new Date(p.fixture_date);
      return fecha.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
    });

    const ratings = partidosValidos.map(p => parseFloat(p.rating));
    const rivales = partidosValidos.map(p => p.opponent_team_name);

    // Crear gráfico
    const ctx = document.getElementById('ratingChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Rating',
          data: ratings,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointRadius: 6,
          pointHoverRadius: 8,
          pointBackgroundColor: '#3b82f6',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointHoverBackgroundColor: '#2563eb',
          pointHoverBorderColor: '#fff',
          pointHoverBorderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              title: function(context) {
                const index = context[0].dataIndex;
                return `vs ${rivales[index]}`;
              },
              label: function(context) {
                return `Rating: ${context.parsed.y.toFixed(1)}`;
              }
            },
            backgroundColor: 'rgba(15, 23, 42, 0.95)',
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            padding: 12,
            displayColors: false,
            borderColor: '#3b82f6',
            borderWidth: 2
          }
        },
        scales: {
          y: {
            min: Math.max(5.0, Math.min(...ratings) - 0.5),
            max: Math.min(10.0, Math.max(...ratings) + 0.5),
            ticks: {
              stepSize: 0.5,
              callback: function(value) {
                return value.toFixed(1);
              },
              font: {
                size: 12,
                weight: '600'
              },
              color: '#64748b'
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)',
              drawBorder: false
            },
            title: {
              display: true,
              text: 'Rating',
              font: {
                size: 13,
                weight: '600'
              },
              color: '#475569'
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 11,
                weight: '600'
              },
              color: '#64748b'
            }
          }
        }
      }
    });

  } catch (error) {
    console.error('Error cargando gráfico:', error);
    document.querySelector('#ratingChart').parentElement.innerHTML =
      '<p style="text-align: center; color: #ef4444; padding: 2rem;">Error al cargar el gráfico de evolución</p>';
  }
}

// ========================================
// CARGAR ANÁLISIS FANTASY (ÚLTIMOS 5 PARTIDOS)
// ========================================
async function cargarAnalisisFantasy() {
  try {
    const response = await fetch(`/wp-json/labelme/v1/jugador-ultimos-partidos?player_id=${playerId}&limit=5`);
    const partidos = await response.json();

    const container = document.getElementById('analisis-fantasy-container');

    if (!partidos || partidos.length === 0) {
      container.innerHTML = '<h2>Análisis Fantasy</h2><p style="text-align: center; color: #64748b;">No hay suficientes datos para generar análisis</p>';
      return;
    }

    // Calcular estadísticas de últimos 5 partidos
    const partidosJugados = partidos.filter(p => p.minutes > 0 || p.substitute !== null);
    const titulares = partidos.filter(p => p.substitute == 0 && p.minutes > 0).length;
    const titularidadPct = partidosJugados.length > 0
      ? Math.round((titulares / partidosJugados.length) * 100)
      : 0;

    const ratingsValidos = partidos.filter(p => p.rating > 0).map(p => parseFloat(p.rating));
    const ratingPromedio = ratingsValidos.length > 0
      ? (ratingsValidos.reduce((a, b) => a + b, 0) / ratingsValidos.length).toFixed(1)
      : 0;

    const golesUltimos5 = partidos.reduce((sum, p) => sum + (parseInt(p.goals) || 0), 0);
    const asistUltimos5 = partidos.reduce((sum, p) => sum + (parseInt(p.assists) || 0), 0);

    // Calcular minutos promedio solo de partidos donde realmente jugó
    const partidosConMinutos = partidos.filter(p => p.minutes > 0);
    const minutosPromedio = partidosConMinutos.length > 0
      ? Math.round(partidosConMinutos.reduce((sum, p) => sum + parseInt(p.minutes), 0) / partidosConMinutos.length)
      : 0;

    // Criterios de evaluación (ajustados para 5 partidos)
    const es_titular = titularidadPct >= 60; // 3+ titularidades en últimos 5
    const buen_rating = parseFloat(ratingPromedio) >= 7.0;
    const productivo = (golesUltimos5 + asistUltimos5) >= 2; // Al menos 2 G+A en últimos 5
    const minutos_suficientes = minutosPromedio >= 60; // Promedio de 60+ minutos

    // Determinar recomendación
    let recomendacion = 'MEDIA';
    if (es_titular && buen_rating && minutos_suficientes) {
      recomendacion = 'ALTA';
    } else if (!es_titular && (!buen_rating || minutosPromedio < 45)) {
      recomendacion = 'BAJA';
    }

    // Colores y estilos
    const bgColor = recomendacion === 'ALTA' ? '#d1fae5' : (recomendacion === 'MEDIA' ? '#fef9c3' : '#fecaca');
    const textColor = recomendacion === 'ALTA' ? '#065f46' : (recomendacion === 'MEDIA' ? '#854d0e' : '#991b1b');
    const icon = recomendacion === 'ALTA' ? '✅' : (recomendacion === 'MEDIA' ? '⚠️' : '❌');
    const titulo = recomendacion === 'ALTA' ? 'RECOMENDACIÓN ALTA' : (recomendacion === 'MEDIA' ? 'RECOMENDACIÓN MEDIA' : 'RIESGO ALTO');

    // Renderizar análisis
    container.innerHTML = `
      <h2>Análisis Fantasy (Últimos 5 Partidos)</h2>

      <div style="background: ${bgColor}; padding: 2rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid ${recomendacion === 'ALTA' ? '#86efac' : (recomendacion === 'MEDIA' ? '#fde68a' : '#fca5a5')};">
        <h3 style="margin: 0 0 1.5rem 0; color: ${textColor}; font-size: 1.2rem;">
          ${icon} ${titulo}
        </h3>
        <ul style="list-style: none; padding: 0; margin: 0;">
          ${es_titular
            ? `<li style="margin-bottom: 0.75rem;">✅ <strong>Titular habitual</strong> - ${titularidadPct}% de titularidades en últimos 5 partidos</li>`
            : `<li style="margin-bottom: 0.75rem;">⚠️ <strong>Rotación frecuente</strong> - Solo ${titularidadPct}% de titularidades en últimos 5 partidos</li>`
          }

          ${buen_rating
            ? `<li style="margin-bottom: 0.75rem;">✅ <strong>Buen rendimiento reciente</strong> - Rating promedio de ${ratingPromedio}</li>`
            : `<li style="margin-bottom: 0.75rem;">⚠️ <strong>Rendimiento irregular</strong> - Rating promedio de ${ratingPromedio}</li>`
          }

          ${productivo
            ? `<li style="margin-bottom: 0.75rem;">✅ <strong>Productivo en ataque</strong> - ${golesUltimos5} goles + ${asistUltimos5} asistencias en últimos 5 partidos</li>`
            : `<li style="margin-bottom: 0.75rem;">⚠️ <strong>Poca producción ofensiva</strong> - ${golesUltimos5} goles + ${asistUltimos5} asistencias en últimos 5 partidos</li>`
          }

          ${minutos_suficientes
            ? `<li style="margin-bottom: 0.75rem;">✅ <strong>Regularidad de minutos</strong> - Promedio de ${minutosPromedio} minutos por partido</li>`
            : `<li style="margin-bottom: 0.75rem;">⚠️ <strong>Pocos minutos</strong> - Promedio de ${minutosPromedio} minutos por partido</li>`
          }
        </ul>
      </div>

      <h3 style="margin-top: 2rem; color: #334155;">💡 Recomendaciones para tu Fantasy:</h3>
      <p style="color: #475569; line-height: 1.6;">
        ${recomendacion === 'ALTA'
          ? `Basado en su <strong>forma reciente</strong>, este jugador es una <strong>excelente opción</strong> para tu equipo fantasy.
             Su alta titularidad (${titularidadPct}%), buen rating (${ratingPromedio})
             y regularidad de minutos lo convierten en una apuesta segura.`
          : recomendacion === 'MEDIA'
            ? `Este jugador puede ser una <strong>opción válida</strong> dependiendo de tu presupuesto y necesidades.
               Aunque su forma reciente muestra aspectos positivos, también presenta ciertos riesgos que debes considerar.`
            : `⚠️ Este jugador presenta <strong>alto riesgo</strong> para fantasy según su forma reciente.
               Su baja titularidad o irregularidad en el rendimiento de los últimos 5 partidos pueden afectar tu puntuación semanal.`
        }
      </p>
    `;

  } catch (error) {
    console.error('Error cargando análisis fantasy:', error);
    document.getElementById('analisis-fantasy-container').innerHTML =
      '<h2>Análisis Fantasy</h2><p style="text-align: center; color: #ef4444;">Error al cargar el análisis</p>';
  }
}

// EJECUTAR AL CARGAR LA PÁGINA
// ========================================
// CARGAR VALORES DE MERCADO POR APP
// ========================================
async function cargarValoresMercado() {
  const container = document.getElementById('valores-mercado-container');
  const themeUrl = '<?php echo get_stylesheet_directory_uri(); ?>';
  try {
    const response = await fetch(`/wp-json/labelme/v1/jugador-valores-mercado?player_id=${playerId}`);
    const datos = await response.json();

    const apps = [
      { key: 'mister',        nombre: 'Mister',        color: '#c9002e', bg: 'linear-gradient(135deg,#fff1f2,#ffe4e6)', border: '#c9002e', logo: `${themeUrl}/img/apps/mister.webp` },
      { key: 'biwenger',      nombre: 'Biwenger',      color: '#1b3a6b', bg: 'linear-gradient(135deg,#eff6ff,#dbeafe)', border: '#1b3a6b', logo: `${themeUrl}/img/apps/biwenger.webp` },
      { key: 'comunio',       nombre: 'Comunio',       color: '#ff6b00', bg: 'linear-gradient(135deg,#fff7ed,#ffedd5)', border: '#ff6b00', logo: `${themeUrl}/img/apps/comunio.webp` },
      { key: 'laliga_fantasy', nombre: 'LaLiga Fantasy', color: '#ee4023', bg: 'linear-gradient(135deg,#fff5f5,#fee2e2)', border: '#ee4023', logo: `${themeUrl}/img/apps/laliga_fantasy.webp` },
    ];

    function formatValue(v) {
      if (!v) return '—';
      if (v >= 1000000) return (v / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
      if (v >= 1000)    return (v / 1000).toFixed(0) + 'K';
      return v.toString();
    }

    function formatChange(c) {
      if (!c) return '';
      const sign = c > 0 ? '+' : '';
      const formatted = formatValue(Math.abs(c));
      const color = c > 0 ? '#10b981' : '#ef4444';
      const arrow = c > 0 ? '▲' : '▼';
      return `<span style="font-size:0.75rem;color:${color};font-weight:700;margin-top:0.25rem;display:block;">${arrow} ${sign}${c > 0 ? '' : '-'}${formatted}</span>`;
    }

    let sinDatos = true;
    let html = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;">';

    apps.forEach(app => {
      const info = datos[app.key];
      if (info && info.value) sinDatos = false;
      const valor = info ? formatValue(info.value) : '—';
      const cambio = info ? formatChange(info.change) : '';
      html += `
        <div style="background:${app.bg};padding:1.25rem;border-radius:12px;border-left:4px solid ${app.border};text-align:center;">
          <div style="display:flex;align-items:center;justify-content:center;gap:0.4rem;margin-bottom:0.5rem;">
            <img src="${app.logo}" alt="${app.nombre}" style="width:22px;height:22px;object-fit:contain;border-radius:4px;">
            <span style="font-size:0.75rem;color:#475569;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">${app.nombre}</span>
          </div>
          <div style="font-size:1.75rem;font-weight:900;color:${app.color};line-height:1.1;">${valor}</div>
          ${cambio}
        </div>`;
    });

    html += '</div>';

    if (sinDatos) {
      container.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:1rem;">Sin datos de valores de mercado disponibles</p>';
    } else {
      container.innerHTML = html;
    }
  } catch (e) {
    container.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:1rem;">No se pudieron cargar los valores de mercado</p>';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  cargarValoresMercado();
  cargarStatsUltimos5();
  cargarAnalisisFantasy();
  cargarUltimosPartidos();
  cargarGraficoRating();
});
</script>

<style>
/* Estilos para loading skeleton */
.loading-skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s ease-in-out infinite;
  border-radius: 8px;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Responsive para móvil */
@media (max-width: 768px) {
  #ultimos-partidos-container > div > div {
    padding: 0.75rem !important;
  }
  
  #ultimos-partidos-container > div > div > div {
    flex-direction: column !important;
    align-items: flex-start !important;
  }
  
  #ultimos-partidos-container > div > div > div > div:last-child {
    width: 100%;
    justify-content: space-between;
    margin-top: 0.75rem;
  }
}
</style>
  <div style="text-align: center; margin: 2rem 0;">
    <a href="/jugadores" class="btn-volver">← Volver al listado completo</a>
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