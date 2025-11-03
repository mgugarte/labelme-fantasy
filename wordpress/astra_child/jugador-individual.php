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
  SELECT nombre, apellido, edad, nacionalidad, altura, peso, foto, 
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
         baja, tipo_baja, razon_baja
  FROM jugadores_laliga
  WHERE REPLACE(REPLACE(LOWER(nombre), ' ', '-'), '.', '') = %s
  AND season = '2025'
  LIMIT 1
", $slug_jugador));

if (!$jugador) {
  wp_redirect(home_url('/jugadores/'));
  exit;
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
  
  <style>
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      background-color: #f5f6fa;
      color: #1e293b;
    }

    header {
      background-color: #0f172a;
      padding: 1rem 2rem;
    }

    header img { height: 40px; }

    .breadcrumb {
      background-color: #fff;
      padding: 1rem 2rem;
      font-size: 0.9rem;
      border-bottom: 1px solid #e5e7eb;
    }

    .breadcrumb a {
      color: #3b82f6;
      text-decoration: none;
    }

    .breadcrumb a:hover {
      text-decoration: underline;
    }

    .jugador-header {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      color: white;
      padding: 3rem 2rem;
      text-align: center;
    }

    .jugador-foto-grande {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      border: 5px solid white;
      object-fit: cover;
      margin-bottom: 1rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .jugador-header h1 {
      font-size: 2.5rem;
      margin: 0.5rem 0;
    }

    .jugador-equipo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-size: 1.2rem;
      margin-top: 0.5rem;
    }

    .team-logo-grande {
      width: 40px;
      height: 40px;
      object-fit: contain;
    }

    .contenedor-stats {
      max-width: 1200px;
      margin: -2rem auto 2rem;
      padding: 0 1rem;
    }

    .grid-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }

    .stat-label {
      font-size: 0.85rem;
      color: #64748b;
      text-transform: uppercase;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 700;
      color: #0f172a;
    }

    .stat-value.destacado {
      color: #3b82f6;
    }

    .badge-estado {
      display: inline-block;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 600;
      margin: 1rem 0;
    }

    .badge-baja {
      background-color: #fee2e2;
      color: #991b1b;
    }

    .badge-disponible {
      background-color: #d1fae5;
      color: #065f46;
    }

    .seccion-info {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .seccion-info h2 {
      color: #0f172a;
      border-bottom: 3px solid #3b82f6;
      padding-bottom: 0.5rem;
      margin-bottom: 1.5rem;
    }

    .btn-volver {
      display: inline-block;
      background-color: #3b82f6;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.2s;
    }

    .btn-volver:hover {
      background-color: #2563eb;
    }

    footer {
      text-align: center;
      padding: 2rem;
      margin-top: 3rem;
      background-color: #f1f5f9;
      color: #64748b;
    }

    @media (max-width: 768px) {
      .jugador-header h1 {
        font-size: 1.8rem;
      }
      
      .grid-stats {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      }
    }
  </style>
</head>
<body>

<header>
  <a href="/" style="display: flex; align-items: center;">
    <img src="http://labelme.es/wp-content/uploads/2025/05/labelMe_negro-removebg-preview.png" alt="LabelMe">
  </a>
</header>

<div class="breadcrumb">
  <a href="/">Inicio</a> / <a href="/jugadores/">Jugadores LaLiga</a> / <strong><?php echo esc_html($jugador->nombre); ?></strong>
</div>

<section class="jugador-header">
  <img src="<?php echo esc_url($jugador->foto); ?>" alt="<?php echo esc_attr($jugador->nombre); ?>" class="jugador-foto-grande">
  <h1><?php echo esc_html($jugador->nombre); ?></h1>
  <div class="jugador-equipo">
    <img src="<?php echo esc_url($jugador->team_logo); ?>" alt="<?php echo esc_attr($jugador->equipo); ?>" class="team-logo-grande">
    <span><?php echo esc_html($jugador->equipo); ?></span>
  </div>
  <p style="font-size: 1.1rem; margin-top: 0.5rem;">Posición: <strong><?php echo esc_html($jugador->posicion); ?></strong></p>
  
  <?php if ($jugador->baja == 1): ?>
    <div class="badge-estado badge-baja">
      ⚠️ BAJA - <?php echo esc_html($jugador->tipo_baja); ?>
      <?php if ($jugador->razon_baja): ?>
        <br><small><?php echo esc_html($jugador->razon_baja); ?></small>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="badge-estado badge-disponible">✓ Disponible</div>
  <?php endif; ?>
</section>

<div class="contenedor-stats">
  
  <div class="grid-stats">
    <div class="stat-card">
      <div class="stat-label">Rating</div>
      <div class="stat-value destacado"><?php echo esc_html($jugador->rating); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Minutos Jugados</div>
      <div class="stat-value"><?php echo esc_html($jugador->minutos); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Goles</div>
      <div class="stat-value"><?php echo esc_html($jugador->goles_total); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Asistencias</div>
      <div class="stat-value"><?php echo esc_html($jugador->goles_asistencias); ?></div>
    </div>
  </div>

  <div class="seccion-info">
    <h2>📊 Estadísticas Detalladas</h2>
    <div class="grid-stats">
      <div class="stat-card">
        <div class="stat-label">% Titularidad</div>
        <div class="stat-value destacado"><?php echo esc_html($jugador->porcentaje_titularidades); ?>%</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-label">% Participación</div>
        <div class="stat-value destacado"><?php echo esc_html($jugador->porcentaje_participacion); ?>%</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-label">Tarjetas Amarillas</div>
        <div class="stat-value"><?php echo esc_html($jugador->amarillas); ?></div>
      </div>
      
      <div class="stat-card">
        <div class="stat-label">Tarjetas Rojas</div>
        <div class="stat-value"><?php echo esc_html($jugador->rojas); ?></div>
      </div>
    </div>
  </div>
div class="seccion-info">
  <h2>👤 Información Personal</h2>
  <div class="grid-stats">
    <div class="stat-card">
      <div class="stat-label">Edad</div>
      <div class="stat-value"><?php echo esc_html($jugador->edad); ?> años</div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Nacionalidad</div>
      <div class="stat-value" style="font-size: 1.3rem;"><?php echo esc_html($jugador->nacionalidad); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Altura</div>
      <div class="stat-value"><?php echo esc_html($jugador->altura); ?> cm</div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Peso</div>
      <div class="stat-value"><?php echo esc_html($jugador->peso); ?> kg</div>
    </div>
  </div>
</div>

<div class="seccion-info">
  <h2>⚽ Estadísticas Ofensivas</h2>
  <div class="grid-stats">
    <div class="stat-card">
      <div class="stat-label">Disparos Totales</div>
      <div class="stat-value"><?php echo esc_html($jugador->shots_total); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Disparos a Puerta</div>
      <div class="stat-value destacado"><?php echo esc_html($jugador->shots_on); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Pases Clave</div>
      <div class="stat-value"><?php echo esc_html($jugador->passes_key); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Precisión Pases</div>
      <div class="stat-value destacado"><?php echo esc_html($jugador->passes_accuracy); ?>%</div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Regates Intentados</div>
      <div class="stat-value"><?php echo esc_html($jugador->dribbles_attempts); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Regates Exitosos</div>
      <div class="stat-value destacado"><?php echo esc_html($jugador->dribbles_success); ?></div>
    </div>
  </div>
</div>

<div class="seccion-info">
  <h2>🛡️ Estadísticas Defensivas</h2>
  <div class="grid-stats">
    <div class="stat-card">
      <div class="stat-label">Entradas Totales</div>
      <div class="stat-value"><?php echo esc_html($jugador->tackles_total); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Bloqueos</div>
      <div class="stat-value"><?php echo esc_html($jugador->tackles_blocks); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Intercepciones</div>
      <div class="stat-value destacado"><?php echo esc_html($jugador->tackles_interceptions); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Duelos Ganados</div>
      <div class="stat-value destacado"><?php echo esc_html($jugador->duels_won); ?> / <?php echo esc_html($jugador->duels_total); ?></div>
    </div>
  </div>
</div>

<div class="seccion-info">
  <h2>⚖️ Disciplina y Penales</h2>
  <div class="grid-stats">
    <div class="stat-card">
      <div class="stat-label">Faltas Recibidas</div>
      <div class="stat-value"><?php echo esc_html($jugador->fouls_drawn); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Faltas Cometidas</div>
      <div class="stat-value"><?php echo esc_html($jugador->fouls_committed); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Penales Anotados</div>
      <div class="stat-value destacado"><?php echo esc_html($jugador->penalty_scored); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Penales Fallados</div>
      <div class="stat-value"><?php echo esc_html($jugador->penalty_missed); ?></div>
    </div>
  </div>
</div>

<div class="seccion-info">
  <h2>🔄 Participación en Partidos</h2>
  <div class="grid-stats">
    <div class="stat-card">
      <div class="stat-label">Partidos Jugados</div>
      <div class="stat-value destacado"><?php echo esc_html($jugador->partidos); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Como Titular</div>
      <div class="stat-value"><?php echo esc_html($jugador->titular); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Como Suplente</div>
      <div class="stat-value"><?php echo esc_html($jugador->suplente); ?></div>
    </div>
    
    <div class="stat-card">
      <div class="stat-label">Sustituciones</div>
      <div class="stat-value">Entrada: <?php echo esc_html($jugador->sub_in); ?> | Salida: <?php echo esc_html($jugador->sub_out); ?></div>
    </div>
  </div>
</div>
  <div class="seccion-info">
    <h2>ℹ️ Análisis para Fantasy</h2>
    <p>
      <strong><?php echo esc_html($jugador->nombre); ?></strong> juega como 
      <strong><?php echo esc_html($jugador->posicion); ?></strong> en el 
      <strong><?php echo esc_html($jugador->equipo); ?></strong>.
    </p>
    
    <?php if ($jugador->porcentaje_titularidades >= 70): ?>
      <p>✅ Es un jugador <strong>titular habitual</strong> con <?php echo esc_html($jugador->porcentaje_titularidades); ?>% de titularidades.</p>
    <?php elseif ($jugador->porcentaje_titularidades >= 40): ?>
      <p>⚡ Es un jugador <strong>rotativo</strong> con <?php echo esc_html($jugador->porcentaje_titularidades); ?>% de titularidades.</p>
    <?php else: ?>
      <p>⚠️ Es principalmente <strong>suplente</strong> con solo <?php echo esc_html($jugador->porcentaje_titularidades); ?>% de titularidades.</p>
    <?php endif; ?>

    <?php if ($jugador->rating >= 7.0): ?>
      <p>🌟 Tiene un <strong>excelente rendimiento</strong> con un rating de <?php echo esc_html($jugador->rating); ?>.</p>
    <?php elseif ($jugador->rating >= 6.5): ?>
      <p>👍 Mantiene un <strong>buen nivel</strong> con un rating de <?php echo esc_html($jugador->rating); ?>.</p>
    <?php endif; ?>

    <?php if ($jugador->goles_total > 0 || $jugador->goles_asistencias > 0): ?>
      <p>⚽ Contribuye en ataque con <strong><?php echo esc_html($jugador->goles_total); ?> goles</strong> 
      y <strong><?php echo esc_html($jugador->goles_asistencias); ?> asistencias</strong> en la temporada.</p>
    <?php endif; ?>
  </div>

  <div style="text-align: center; margin: 2rem 0;">
    <a href="/jugadores" class="btn-volver">← Volver al listado completo</a>
  </div>

</div>

<footer>
  &copy; 2025 LabelMe. Estadísticas Fantasy LaLiga - Datos actualizados
</footer>

<script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/cc1120e60af0020f9479833b/script.js"></script>
</body>
</html>