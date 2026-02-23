<?php
/*
Template Name: Comparador de Jugadores
*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">  
  <!-- SEO Optimizado -->
  <title>Comparador de Jugadores LaLiga Fantasy 2025 | LabelMe</title>
  <meta name="description" content="Compara hasta 3 jugadores de LaLiga lado a lado. Estadísticas detalladas de goles, asistencias, rating, minutos y más para tomar las mejores decisiones en tu Fantasy.">
  <meta name="keywords" content="comparador jugadores laliga, fantasy laliga, estadísticas jugadores, comparar futbolistas">
  
  <!-- Open Graph -->
  <meta property="og:title" content="Comparador de Jugadores LaLiga Fantasy 2025 | LabelMe">
  <meta property="og:description" content="Compara estadísticas de jugadores de LaLiga para optimizar tu equipo Fantasy. Goles, asistencias, rating y mucho más.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo home_url('/comparador/'); ?>">
  <meta property="og:image" content="http://labelme.es/wp-content/uploads/2025/05/labelMe_negro-removebg-preview.png">
  
  <!-- Canonical URL -->
  <link rel="canonical" href="<?php echo home_url('/comparador/'); ?>">
  
  <?php wp_head(); ?>
    <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-JMQXMFQLBE"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-JMQXMFQLBE');
  </script>
  <!-- Schema.org para el comparador -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebApplication",
    "name": "Comparador de Jugadores LaLiga Fantasy",
    "description": "Herramienta para comparar estadísticas de jugadores de LaLiga Fantasy",
    "url": "<?php echo home_url('/comparador/'); ?>",
    "applicationCategory": "SportsApplication",
    "offers": {
      "@type": "Offer",
      "price": "0",
      "priceCurrency": "EUR"
    }
  }
  </script>
  
  <!-- Breadcrumb Schema -->
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
        "name": "Comparador de Jugadores",
        "item": "<?php echo home_url('/comparador/'); ?>"
      }
    ]
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

<?php
// OBTENER EQUIPOS PARA LA BARRA SUPERIOR
global $wpdb;
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

<section style="max-width: 1200px; margin: 2rem auto; padding: 0 1rem;">
  <h1 style="text-align: center; font-size: 2.5rem; color: #0f172a; margin-bottom: 1rem;">
    Comparador de Jugadores LaLiga
  </h1>
  <p style="text-align: center; color: #64748b; margin-bottom: 2rem; max-width: 700px; margin-left: auto; margin-right: auto;">
    Selecciona hasta 3 jugadores para comparar sus estadísticas y tomar mejores decisiones en tu Fantasy LaLiga 2025
  </p>

  <!-- Selectores de jugadores -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div>
      <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #0f172a;">Jugador 1</label>
      <input type="text" id="jugador1" class="buscador-comparador" placeholder="🔍 Buscar jugador...">
      <div id="sugerencias1" class="sugerencias-lista"></div>
      <div id="selected1" class="jugador-seleccionado"></div>
    </div>
    <div>
      <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #0f172a;">Jugador 2</label>
      <input type="text" id="jugador2" class="buscador-comparador" placeholder="🔍 Buscar jugador...">
      <div id="sugerencias2" class="sugerencias-lista"></div>
      <div id="selected2" class="jugador-seleccionado"></div>
    </div>
    <div>
      <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #0f172a;">Jugador 3 (opcional)</label>
      <input type="text" id="jugador3" class="buscador-comparador" placeholder="🔍 Buscar jugador...">
      <div id="sugerencias3" class="sugerencias-lista"></div>
      <div id="selected3" class="jugador-seleccionado"></div>
    </div>
  </div>

  <div style="text-align: center; margin-bottom: 2rem;">
    <button id="btnComparar" class="btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem; border-radius: 8px; background: #3b82f6; color: white; font-weight: 700; cursor: pointer; border: none; transition: all 0.2s;">
      Comparar Jugadores
    </button>
  </div>

  <!-- Tabla de comparación -->
  <div id="resultadoComparacion"></div>
</section>

<!-- Sección informativa para SEO -->
<section style="max-width: 1200px; margin: 3rem auto; padding: 0 1rem;">
  <div style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
    <h2 style="color: #0f172a; font-size: 1.5rem; margin-bottom: 1rem; font-weight: 700;">
      Comparador de Jugadores Fantasy LaLiga
    </h2>

    <p style="line-height: 1.7; color: #475569; margin-bottom: 0;">
      Compara hasta 3 jugadores de <strong>LaLiga Fantasy 2025</strong> con estadísticas detalladas: rating, goles, asistencias, minutos, titularidad, recomendación fantasy y forma reciente. Toma mejores decisiones para tu equipo con datos actualizados.
    </p>
  </div>
</section>

<footer>
  &copy; 2025 LabelMe. Estadísticas Fantasy LaLiga
</footer>

<script>
// Variables globales
let jugadoresSeleccionados = {1: null, 2: null, 3: null};

// Función para generar slug (igual que en PHP)
function generarSlug(nombre) {
  return nombre.toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/\./g, '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

// Configurar buscadores
[1, 2, 3].forEach(num => {
  const input = document.getElementById(`jugador${num}`);
  const sugerencias = document.getElementById(`sugerencias${num}`);
  let timeoutId;

  input.addEventListener('input', function() {
    clearTimeout(timeoutId);
    const busqueda = this.value.trim();
    
    if (busqueda.length < 2) {
      sugerencias.style.display = 'none';
      return;
    }
    
    timeoutId = setTimeout(async () => {
      try {
        const response = await fetch(`/wp-json/labelme/v1/buscar-jugadores?q=${encodeURIComponent(busqueda)}`);
        const datos = await response.json();
        
        if (!datos || datos.length === 0) {
          sugerencias.style.display = 'none';
          return;
        }
        
        sugerencias.innerHTML = datos.map(j => `
          <div class="sugerencia-item" data-nombre="${j.nombre}" data-num="${num}">
            <img src="${j.foto}" alt="${j.nombre}">
            <div>
              <strong style="display: block; color: #1e293b; font-size: 0.95rem;">${j.nombre}</strong>
              <small style="color: #475569; font-size: 0.85rem;">${j.team_name} · ${j.posicion}</small>
            </div>
          </div>
        `).join('');
        
        sugerencias.style.display = 'block';
      } catch (error) {
        console.error('Error:', error);
      }
    }, 300);
  });

  sugerencias.addEventListener('click', async (e) => {
    const item = e.target.closest('.sugerencia-item');
    if (!item) return;

    const nombre = item.dataset.nombre;
    const numJugador = item.dataset.num;

    // Obtener datos completos del jugador
    const response = await fetch(`/wp-json/labelme/v1/jugador-completo?nombre=${encodeURIComponent(nombre)}`);
    const jugador = await response.json();

    jugadoresSeleccionados[numJugador] = jugador;

    const selected = document.getElementById(`selected${numJugador}`);
    selected.innerHTML = `
      <div style="display: flex; align-items: center; gap: 0.5rem; background: #f1f5f9; padding: 0.5rem; border-radius: 8px; margin-top: 0.5rem;">
        <img src="${jugador.foto}" style="width: 40px; height: 40px; border-radius: 50%;">
        <div style="flex: 1;">
          <strong style="display: block; color: #0f172a;">${jugador.nombre}</strong>
          <small style="color: #64748b;">${jugador.team_name}</small>
        </div>
        <button onclick="eliminarJugador(${numJugador})" style="background: #ef4444; color: white; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer;">✕</button>
      </div>
    `;
    
    input.value = '';
    sugerencias.style.display = 'none';
  });
});

function eliminarJugador(num) {
  jugadoresSeleccionados[num] = null;
  document.getElementById(`selected${num}`).innerHTML = '';
}

document.getElementById('btnComparar').addEventListener('click', async () => {
  const jugadores = Object.values(jugadoresSeleccionados).filter(j => j !== null);

  if (jugadores.length < 2) {
    alert('⚠️ Selecciona al menos 2 jugadores para comparar');
    return;
  }

  // Mostrar indicador de carga
  const btn = document.getElementById('btnComparar');
  const textoOriginal = btn.innerHTML;
  btn.innerHTML = '⏳ Cargando comparación...';
  btn.disabled = true;
  btn.style.opacity = '0.7';
  btn.style.cursor = 'not-allowed';

  document.getElementById('resultadoComparacion').innerHTML = `
    <div style="text-align: center; padding: 3rem;">
      <div style="display: inline-block; width: 50px; height: 50px; border: 5px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite;"></div>
      <p style="margin-top: 1rem; color: #64748b; font-weight: 600;">Cargando datos de comparación...</p>
    </div>
  `;

  try {
    await mostrarComparacion(jugadores);
  } finally {
    // Restaurar botón
    btn.innerHTML = textoOriginal;
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor = 'pointer';
  }
});

async function mostrarComparacion(jugadores) {
  // Detectar si todos son de la misma posición
  const posiciones = jugadores.map(j => j.posicion);
  const todosIguales = posiciones.every(p => p === posiciones[0]);
  const posicion = posiciones[0];

  // Estadísticas base para TODOS
  let estadisticas = [
    {label: '⭐ Rating', key: 'rating', mejor: 'max'},
    {label: '⚽ Goles', key: 'goles_total', mejor: 'max'},
    {label: '🎯 Asistencias', key: 'goles_asistencias', mejor: 'max'},
    {label: '⏱️ Minutos', key: 'minutos', mejor: 'max'},
    {label: '📊 % Titular', key: 'porcentaje_titularidades', mejor: 'max'},
    {label: '🟨 Amarillas', key: 'amarillas', mejor: 'min'},
    {label: '🟥 Rojas', key: 'rojas', mejor: 'min'}
  ];

  // Añadir estadísticas específicas según posición (código existente...)
  if (todosIguales) {
    if (posicion === 'Portero') {
      estadisticas.splice(1, 2);
      estadisticas.push(
        {label: '🧤 Salvadas', key: 'saves_total', mejor: 'max'},
        {label: '🥅 Concedidos', key: 'goals_conceded', mejor: 'min'},
        {label: '🚫 Porterías a cero', key: 'clean_sheet', mejor: 'max'}
      );
    } else if (posicion === 'Defensa') {
      estadisticas.push(
        {label: '🛡️ Entradas', key: 'tackles_total', mejor: 'max'},
        {label: '🔒 Intercepciones', key: 'tackles_interceptions', mejor: 'max'},
        {label: '⚔️ Duelos ganados', key: 'duels_won', mejor: 'max'}
      );
    } else if (posicion === 'Centrocampista') {
      estadisticas.push(
        {label: '📝 Pases totales', key: 'passes_total', mejor: 'max'},
        {label: '🔑 Pases clave', key: 'passes_key', mejor: 'max'},
        {label: '🎯 Precisión pases', key: 'passes_accuracy', mejor: 'max'},
        {label: '⚔️ Duelos ganados', key: 'duels_won', mejor: 'max'}
      );
    } else if (posicion === 'Delantero') {
      estadisticas.push(
        {label: '🎯 Disparos', key: 'shots_total', mejor: 'max'},
        {label: '🎯 A puerta', key: 'shots_on', mejor: 'max'},
        {label: '💨 Regates exitosos', key: 'dribbles_success', mejor: 'max'}
      );
    }
  } else {
    estadisticas.push(
      {label: '🎯 Disparos a puerta', key: 'shots_on', mejor: 'max'},
      {label: '🔑 Pases clave', key: 'passes_key', mejor: 'max'},
      {label: '🛡️ Entradas', key: 'tackles_total', mejor: 'max'}
    );
  }

  // ========================================
  // NUEVA SECCIÓN: RECOMENDACIÓN FANTASY Y STATS RECIENTES
  // ========================================

  // Obtener recomendaciones fantasy (desde la BD) y últimos partidos para cada jugador
  const probabilidades = jugadores.map((jugador) => {
    const recomendacion = parseInt(jugador.recomendacion_fantasy) || 0;

    // Determinar nivel según recomendación
    let nivel, color, icono;
    if (recomendacion >= 90) {
      nivel = 'Oro (90-100%)';
      color = '#f59e0b';
      icono = '🥇';
    } else if (recomendacion >= 70) {
      nivel = 'Plata (70-89%)';
      color = '#9ca3af';
      icono = '🥈';
    } else if (recomendacion >= 40) {
      nivel = 'Bronce (40-69%)';
      color = '#d97706';
      icono = '🥉';
    } else {
      nivel = 'Riesgo (0-39%)';
      color = '#ef4444';
      icono = '🔴';
    }

    return { probabilidad: recomendacion, nivel, color, icono };
  });

  const ultimosPartidos = await Promise.all(
    Promise.all(
      jugadores.map(async (jugador) => {
        try {
          const response = await fetch(`/wp-json/labelme/v1/jugador-ultimos-partidos?player_id=${jugador.id}&limit=5`);
          const partidos = await response.json();

          // Calcular estadísticas de últimos 5 partidos
          if (!partidos || partidos.length === 0) {
            return { titularidadPct: 0, ratingPromedio: 0 };
          }

          const titulares = partidos.filter(p => p.substitute == 0 && p.minutes > 0).length;
          const titularidadPct = Math.round((titulares / partidos.length) * 100);

          const ratingsValidos = partidos.filter(p => p.rating > 0).map(p => parseFloat(p.rating));
          const ratingPromedio = ratingsValidos.length > 0
            ? (ratingsValidos.reduce((a, b) => a + b, 0) / ratingsValidos.length).toFixed(1)
            : 0;

          return { titularidadPct, ratingPromedio };
        } catch (error) {
          console.error('Error obteniendo últimos partidos:', error);
          return { titularidadPct: 0, ratingPromedio: 0 };
        }
      })
    )
  ]);

  // Generar HTML
  let html = '<div class="comparador-info">';
  if (todosIguales) {
    html += `<p>📊 Comparación especializada para <strong>${posicion}s</strong></p>`;
  } else {
    html += '<p>📊 Comparación general (posiciones mixtas)</p>';
  }
  html += '</div>';

  // ========================================
  // NUEVA FILA: RECOMENDACIÓN FANTASY
  // ========================================
  html += '<div style="background: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">';
  html += '<h3 style="margin: 0 0 1.25rem 0; color: #0f172a; font-size: 1.25rem; text-align: center;">🎯 Recomendación Fantasy</h3>';
  html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">';

  jugadores.forEach((j, index) => {
    const prob = probabilidades[index];
    const slug = generarSlug(j.nombre);
    html += `
      <a href="/jugadores/${slug}/" style="text-decoration: none; color: inherit; display: block;">
        <div style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); padding: 1.25rem; border-radius: 12px; border: 2px solid #e2e8f0; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.12)';" onmouseout="this.style.transform=''; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
          <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 2px solid #e5e7eb;">
            <img src="${j.foto}" style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid #e5e7eb;">
            <div>
              <div style="font-weight: 700; color: #0f172a; font-size: 1rem;">${j.nombre}</div>
              <div style="font-size: 0.85rem; color: #64748b;">${j.team_name}</div>
            </div>
          </div>

          <div style="text-align: center;">
            <div style="font-size: 3rem; font-weight: 800; color: ${prob.color}; margin-bottom: 0.5rem;">
              ${prob.probabilidad}%
            </div>
            <div style="background: ${prob.color}; color: white; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem; font-weight: 600; display: inline-block;">
              ${prob.icono} ${prob.nivel}
            </div>
            ${prob.nivel === 'Lesionado'
              ? '<div style="margin-top: 0.75rem; font-size: 0.8rem; color: #ef4444; font-weight: 600;">⚠️ Actualmente de baja</div>'
              : ''
            }
          </div>
        </div>
      </a>
    `;
  });

  html += '</div></div>';

  // ========================================
  // NUEVA SECCIÓN: FORMA RECIENTE (ÚLTIMOS 5 PARTIDOS)
  // ========================================
  html += '<div style="background: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">';
  html += '<h3 style="margin: 0 0 1.25rem 0; color: #0f172a; font-size: 1.25rem; text-align: center;">⚡ Forma Reciente (Últimos 5 Partidos)</h3>';
  html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">';

  jugadores.forEach((j, index) => {
    const stats = ultimosPartidos[index];
    const ratingColor = stats.ratingPromedio >= 7.0 ? '#10b981' : (stats.ratingPromedio >= 6.5 ? '#f59e0b' : '#ef4444');
    const titularidadColor = stats.titularidadPct >= 80 ? '#10b981' : (stats.titularidadPct >= 50 ? '#f59e0b' : '#ef4444');
    const slug = generarSlug(j.nombre);

    html += `
      <a href="/jugadores/${slug}/" style="text-decoration: none; color: inherit; display: block;">
        <div style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); padding: 1.25rem; border-radius: 12px; border: 2px solid #e2e8f0; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.12)';" onmouseout="this.style.transform=''; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
          <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 2px solid #e5e7eb;">
            <img src="${j.foto}" style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid #e5e7eb;">
            <div>
              <div style="font-weight: 700; color: #0f172a; font-size: 1rem;">${j.nombre}</div>
              <div style="font-size: 0.85rem; color: #64748b;">${j.team_name}</div>
            </div>
          </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div style="text-align: center; background: white; padding: 0.75rem; border-radius: 8px;">
            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">Rating</div>
            <div style="font-size: 2rem; font-weight: 800; color: ${ratingColor};">
              ${stats.ratingPromedio > 0 ? stats.ratingPromedio : '-'}
            </div>
          </div>

          <div style="text-align: center; background: white; padding: 0.75rem; border-radius: 8px;">
            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">Titular</div>
            <div style="font-size: 2rem; font-weight: 800; color: ${titularidadColor};">
              ${stats.titularidadPct}%
            </div>
          </div>
        </div>
        </div>
      </a>
    `;
  });

  html += '</div></div>';

  // ========================================
  // TABLA DE ESTADÍSTICAS (código existente)
  // ========================================
  html += '<div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow-x: auto;">';
  html += '<h3 style="margin: 0 0 1.25rem 0; color: #0f172a; font-size: 1.25rem; text-align: center;">📊 Comparación Detallada</h3>';

  // Calcular ancho de columnas: primera columna 200px, resto equitativo
  const numJugadores = jugadores.length;
  const anchoColumnaJugador = `${Math.floor((100 - 25) / numJugadores)}%`;

  html += '<table class="tabla-jugadores" style="width: 100%; border-collapse: collapse; table-layout: fixed;"><thead><tr style="background: #0f172a;"><th style="width: 25%; padding: 1rem; text-align: left; font-weight: 700; color: #f1f5f9; border-bottom: 2px solid #3b82f6;">Estadística</th>';

  jugadores.forEach(j => {
    const slug = generarSlug(j.nombre);
    html += `<th style="width: ${anchoColumnaJugador}; text-align: center; padding: 1rem; border-bottom: 2px solid #3b82f6; background: #0f172a;">
      <a href="/jugadores/${slug}/" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: transform 0.2s;">
        <img src="${j.foto}" style="width: 60px; height: 60px; border-radius: 50%; margin-bottom: 0.5rem; border: 3px solid #3b82f6; transition: all 0.2s; display: block;" onmouseover="this.style.transform='scale(1.1)'; this.style.borderColor='#60a5fa';" onmouseout="this.style.transform=''; this.style.borderColor='#3b82f6';">
        <span style="font-weight: 700; color: #f1f5f9; font-size: 0.95rem; display: block; margin-bottom: 0.25rem;">${j.nombre}</span>
        <small style="font-weight: normal; color: #94a3b8; font-size: 0.85rem; display: block;">${j.team_name}</small>
      </a>
    </th>`;
  });

  html += '</tr></thead><tbody style="background: white;">';
  
  estadisticas.forEach((stat, idx) => {
    const valores = jugadores.map(j => {
      if (stat.calcular) {
        return parseFloat(stat.calcular(j)) || 0;
      }
      return parseFloat(j[stat.key]) || 0;
    });

    const mejor = stat.mejor === 'max' ? Math.max(...valores) : Math.min(...valores);
    const bgColor = idx % 2 === 0 ? '#ffffff' : '#f8fafc';

    html += `<tr style="background: ${bgColor}; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='${bgColor}'">
      <td style="padding: 0.875rem 1rem; font-weight: 600; color: #0f172a; border-bottom: 1px solid #e5e7eb;">${stat.label}</td>`;

    jugadores.forEach((j, index) => {
      const valor = valores[index];
      const esMejor = valor === mejor && mejor !== 0 && (stat.mejor === 'max' ? mejor > 0 : true);
      html += `<td style="text-align: center; padding: 0.875rem 1rem; border-bottom: 1px solid #e5e7eb; ${esMejor ? 'background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); font-weight: 700; color: #065f46; font-size: 1.05rem; box-shadow: inset 0 0 0 2px #86efac;' : 'color: #475569;'}">${valor}${stat.key.includes('porcentaje') || stat.key.includes('accuracy') || stat.key.includes('efficiency') ? '%' : ''}</td>`;
    });

    html += '</tr>';
  });

  html += '</tbody></table></div>';

  // ========================================
  // NUEVA SECCIÓN: ÚLTIMOS 5 PARTIDOS COMPRIMIDO
  // ========================================
  html += '<div style="margin-top: 2rem; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">';
  html += '<h3 style="text-align: center; color: #0f172a; font-size: 1.25rem; margin-bottom: 1.5rem;">📅 Últimos 5 Partidos</h3>';
  html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">';
  
  // Cargar últimos partidos de cada jugador
  for (const jugador of jugadores) {
    try {
      const response = await fetch(`/wp-json/labelme/v1/jugador-ultimos-partidos?player_id=${jugador.id}&limit=5`);
      const partidos = await response.json();
      const slug = generarSlug(jugador.nombre);

      html += `
        <a href="/jugadores/${slug}/" style="text-decoration: none; color: inherit; display: block;">
          <div style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); padding: 1.25rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 2px solid #e2e8f0; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.12)';" onmouseout="this.style.transform=''; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.75rem;">
              <img src="${jugador.foto}" style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid #e2e8f0;">
              <div>
                <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">${jugador.nombre}</div>
                <div style="font-size: 0.8rem; color: #64748b;">${jugador.team_name}</div>
              </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
      `;
      
      if (partidos && partidos.length > 0) {
        partidos.forEach(p => {
          const esTitular = p.substitute == 0 && p.minutes > 0;
          const badge = esTitular ? '🟢' : (p.minutes > 0 ? '🟡' : '🔴');
          const ratingColor = p.rating >= 7.0 ? '#10b981' : (p.rating >= 6.5 ? '#f59e0b' : '#ef4444');
          
          html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: #f8fafc; border-radius: 6px;">
              <div style="font-size: 0.85rem; flex: 1;">
                ${badge} vs ${p.opponent_team_name}
              </div>
              <div style="font-weight: 700; color: ${ratingColor}; font-size: 0.95rem;">
                ${p.rating || '-'}
              </div>
            </div>
          `;
        });
      } else {
        html += '<p style="text-align: center; color: #94a3b8; font-size: 0.85rem;">Sin datos</p>';
      }


      html += `
            </div>
          </div>
        </a>
      `;

    } catch (error) {
      console.error('Error cargando partidos:', error);
    }
  }
  
  html += '</div></div>';
  
  document.getElementById('resultadoComparacion').innerHTML = html;
}
</script>

<!-- Estilos movidos a labelme_global.css -->

<script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/cc1120e60af0020f9479833b/script.js"></script>
<button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
  🌙
</button>

<script>
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