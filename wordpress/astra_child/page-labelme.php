<?php
/*
Template Name: Página LabelMe HTML Completo
*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LabelMe - Datos Fantasy LaLiga</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/css/style_labelme.css" />
  <link rel="canonical" href="<?php echo home_url('/jugadores/'); ?>">

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    /* Select2 personalizado para LabelMe - UNIFORME */
    .select2-container--default .select2-selection--single {
      height: 44px !important;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      padding: 0 12px;
      background: #fff;
      display: flex;
      align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 44px;
      padding-left: 0;
      padding-right: 30px;
      color: #1e293b;
      font-size: 0.9rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 44px;
      right: 8px;
      top: 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
      color: #9ca3af;
    }
    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--open .select2-selection--single {
      border-color: #3b82f6;
      outline: none;
    }
    .select2-dropdown {
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      margin-top: 4px;
    }
    .select2-results__option {
      padding: 10px 12px;
      font-size: 0.9rem;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #3b82f6;
    }
    /* Igualar espaciado con otros inputs */
    .select2-container {
      width: 100% !important;
      margin-top: 0 !important;
    }

    .filtro-grupo .select2-container {
      margin-top: 0 !important;
    }
    /* Asegurar que los dropdowns no se corten */
    .filtros, .tabla-controles, .filtro-grupo {
      overflow: visible !important;
    }
    .select2-container--open .select2-dropdown {
      z-index: 10000;
    }
    /* Botón de limpiar */
    .select2-container--default .select2-selection--single .select2-selection__clear {
      margin-right: 20px;
      font-size: 1.2rem;
      color: #9ca3af;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear:hover {
      color: #ef4444;
    }
  </style>

  <?php wp_head(); ?>
  <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-JMQXMFQLBE"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-JMQXMFQLBE');
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

<section class="intro-seo" style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem; padding-top: 2rem;">
  <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">

    <h1 style="color: #0f172a; font-size: 1.8rem; margin-bottom: 1rem;">
      Estadísticas Fantasy LaLiga 2025-26 Actualizadas
    </h1>

    <p style="font-size: 1.05rem; line-height: 1.6; color: #475569; margin-bottom: 1rem;">
      <strong>Estadísticas fantasy LaLiga</strong> actualizadas de todos los jugadores de la temporada 2025-26: goles, asistencias, minutos, rating, titularidades y recomendaciones para tu <a href="https://fantasy.laliga.com/" target="_blank" rel="noopener" style="color: #3b82f6;">equipo Fantasy</a>.
    </p>

  </div>
</section>
<section class="filtros" data-aos="fade-up">
  <div class="tabla-controles">
    
    <div class="filtro-grupo">
      <label>Buscar jugador</label>
      <div class="autocomplete-container">
        <input type="text" id="buscador" placeholder="Nombre del jugador..." />
        <div id="sugerencias" class="sugerencias-lista"></div>
      </div>
    </div>

    <div class="filtro-grupo">
      <label>Equipo</label>
      <select id="filtroEquipo">
        <option value="">Todos</option>
        <?php foreach ($equipos_barra as $eq): ?>
          <option value="<?php echo esc_attr($eq->team_name); ?>">
            <?php echo esc_html($eq->team_name); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="filtro-grupo">
      <label>Posición</label>
      <select id="filtroPosicion">
        <option value="">Todas</option>
        <option value="Portero">Portero</option>
        <option value="Defensa">Defensa</option>
        <option value="Centrocampista">Centrocampista</option>
        <option value="Delantero">Delantero</option>
      </select>
    </div>

    <div class="filtro-grupo">
      <label>Estado</label>
      <select id="filtroEstado">
        <option value="">Todos</option>
        <option value="disponible">Disponibles</option>
        <option value="lesionado">Lesionados</option>
        <option value="sancionado">Sancionados</option>
        <option value="otros">Otros</option>
      </select>
    </div>

    <div class="filtro-grupo">
      <label>Recomendación</label>
      <select id="filtroRecomendacion">
        <option value="">Todas</option>
        <option value="oro">Oro (90-100%)</option>
        <option value="plata">Plata (70-89%)</option>
        <option value="bronce">Bronce (40-69%)</option>
        <option value="riesgo">Riesgo (0-39%)</option>
      </select>
    </div>

    <div class="filtro-grupo">
      <label>Rating mín.</label>
      <input type="number" id="filtroRating" min="0" max="10" step="0.1" placeholder="7.0" class="filtro-input">
    </div>

    <div class="filtro-grupo">
      <label>Titular %</label>
      <input type="number" id="filtroTitular" min="0" max="100" placeholder="70" class="filtro-input">
    </div>

  </div>
</section>

<?php
// Función para obtener badge de estado según razón de baja
function obtener_badge_estado($baja, $razon_baja) {
  if ($baja != 1) {
    return '<span class="badge-disponible">✓ DISPONIBLE</span>';
  }

  $razon = strtolower($razon_baja);

  // SANCIONADO: suspensiones, tarjetas, etc.
  if (stripos($razon, 'suspended') !== false ||
      stripos($razon, 'yellow card') !== false ||
      stripos($razon, 'red card') !== false ||
      stripos($razon, 'accumulation') !== false ||
      stripos($razon, 'sancion') !== false ||
      stripos($razon, 'tarjeta') !== false) {
    return '<span class="badge-suspension">🟥 SANCIONADO</span>';
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
    return '<span class="badge-baja">🏥 LESIONADO</span>';
  }

  // OTROS: resto de casos (inactive, rest, coach, international, etc.)
  return '<span class="badge-tecnica">⚠️ OTROS</span>';
}

global $wpdb;
$resultados = $wpdb->get_results("
  SELECT id, nombre, foto, team_name as equipo, team_logo, posicion, minutos, rating,
         goles_total, goles_asistencias, amarillas, rojas,
         porcentaje_titularidades, porcentaje_participacion,
         recomendacion_fantasy,
         baja, tipo_baja, razon_baja
  FROM jugadores_laliga
  WHERE season = '2025'
  ORDER BY equipo, nombre
");

if ($resultados) {
  echo '<div class="tabla-jugadores-wrapper">';
  echo '<table class="tabla-jugadores" id="tablaJugadores" data-aos="fade-up">';
  echo '<thead><tr>';
  echo '<th class="sortable" data-column="0" data-type="text">Jugador</th>';
  echo '<th class="sortable" data-column="1" data-type="text">Equipo</th>';
  echo '<th class="sortable" data-column="2" data-type="text">Posición</th>';
  echo '<th class="sortable" data-column="3" data-type="number">Minutos</th>';
  echo '<th class="sortable" data-column="4" data-type="number">Rating</th>';
  echo '<th class="sortable" data-column="5" data-type="number">Goles</th>';
  echo '<th class="sortable" data-column="6" data-type="number">Asistencias</th>';
  echo '<th class="sortable" data-column="7" data-type="number">Amarillas</th>';
  echo '<th class="sortable" data-column="8" data-type="number">Rojas</th>';
  echo '<th class="sortable" data-column="9" data-type="number">% Titularidad</th>';
  echo '<th class="sortable" data-column="10" data-type="number">% Participación</th>';
  echo '<th class="sortable" data-column="11" data-type="number">Recomendación Fantasy</th>';
  echo '<th class="sortable" data-column="12" data-type="text">Estado</th>';
  echo '<th class="sortable" data-column="13" data-type="text">Tipo Baja</th>';
  echo '<th class="sortable" data-column="14" data-type="text">Razón</th>';
  echo '</tr></thead><tbody>';

foreach ($resultados as $fila) {
    // Generar slug del jugador para la URL
    $slug_jugador = labelme_sanitize_slug($fila->nombre);
    $url_jugador = home_url('/jugadores/' . $slug_jugador . '/');
    
    echo '<tr>';
    // Jugador con foto Y ENLACE a su página individual
    echo '<td>';
    echo '<a href="' . esc_url($url_jugador) . '" style="display: flex; align-items: center; text-decoration: none; color: #1e293b; font-weight: 600;">';
    echo '<img src="'.esc_url($fila->foto).'" class="jugador-img" alt="'.esc_attr($fila->nombre).'">';
    echo esc_html($fila->nombre);
    echo '</a>';
    echo '</td>';
    
    // Resto de columnas igual...
    echo '<td><img src="'.esc_url($fila->team_logo).'" class="team-logo" alt="Equipo">'.esc_html($fila->equipo).'</td>';
    echo '<td>' . esc_html($fila->posicion) . '</td>';
    echo '<td data-sort="' . (int)$fila->minutos . '">' . esc_html($fila->minutos) . '</td>';
    echo '<td data-sort="' . (float)$fila->rating . '">' . esc_html($fila->rating) . '</td>';
    echo '<td data-sort="' . (int)$fila->goles_total . '">' . esc_html($fila->goles_total) . '</td>';
    echo '<td data-sort="' . (int)$fila->goles_asistencias . '">' . esc_html($fila->goles_asistencias) . '</td>';
    echo '<td data-sort="' . (int)$fila->amarillas . '">' . esc_html($fila->amarillas) . '</td>';
    echo '<td data-sort="' . (int)$fila->rojas . '">' . esc_html($fila->rojas) . '</td>';
    echo '<td data-sort="' . (float)$fila->porcentaje_titularidades . '">' . esc_html($fila->porcentaje_titularidades) . '%</td>';
    echo '<td data-sort="' . (float)$fila->porcentaje_participacion . '">' . esc_html($fila->porcentaje_participacion) . '%</td>';

    // Columna de Recomendación Fantasy (renderizada en servidor)
    echo '<td data-sort="' . (int)$fila->recomendacion_fantasy . '">';
    $recomendacion = (int)$fila->recomendacion_fantasy;

    // Determinar estilo de medalla según recomendación
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

    echo '<div style="
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: ' . $backgroundColor . ';
        ' . $medalStyle . '
        font-weight: 800;
        font-size: 0.9rem;
        color: white;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        cursor: help;
    " title="' . esc_attr($nivel) . '">';
    echo $recomendacion . '%';
    echo '</div>';
    echo '</td>';

    // Mostrar badge de estado según razón de baja
    echo '<td>' . obtener_badge_estado($fila->baja, $fila->razon_baja) . '</td>';

    // Mostrar tipo de baja (ya viene traducido de Python)
    echo '<td>' . ($fila->tipo_baja ? esc_html($fila->tipo_baja) : '-') . '</td>';

    // Mostrar razón de baja (ya viene traducido de Python)
    echo '<td>' . ($fila->razon_baja ? esc_html($fila->razon_baja) : '-') . '</td>';
    echo '</tr>';
  }
  echo '</tbody></table>';
  echo '</div>';
} else {
  echo '<p style="text-align:center;">No hay datos disponibles por el momento.</p>';
}
?>

<footer>&copy; 2025 LabelMe. Todos los derechos reservados.</footer>

<!-- jQuery y Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({ duration: 800, once: true });

  // Inicializar Select2 en todos los filtros
  $(document).ready(function() {
    $('#filtroEquipo').select2({
      placeholder: 'Todos los equipos',
      allowClear: true,
      width: '100%'
    });

    $('#filtroPosicion').select2({
      placeholder: 'Todas las posiciones',
      allowClear: true,
      width: '100%'
    });

    $('#filtroEstado').select2({
      placeholder: 'Todos los estados',
      allowClear: true,
      width: '100%'
    });

    $('#filtroRecomendacion').select2({
      placeholder: 'Todas las recomendaciones',
      allowClear: true,
      width: '100%'
    });
  });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta/dist/vanta.waves.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.fog.min.js"></script>

 <script>
  AOS.init({ duration: 800, once: true });

  VANTA.NET({
    el: ".hero",
    color: 0x60a5fa,
    backgroundColor: 0x0f172a,
    points: 10.0,
    maxDistance: 25.0,
    spacing: 18.0
  });

  // Si ya tienes este script en la página, no duplicarlo. Este es el mismo comportamiento usado en home.
  const buscador = document.getElementById('buscador');
  const sugerencias = document.getElementById('sugerencias');
  let timeoutId;

  if (buscador) {
    buscador.addEventListener('input', function() {
      clearTimeout(timeoutId);
      const busqueda = this.value.trim();
      if (busqueda.length < 2) { sugerencias.style.display = 'none'; return; }

      timeoutId = setTimeout(async () => {
        try {
          const resp = await fetch(`/wp-json/labelme/v1/buscar-jugadores?q=${encodeURIComponent(busqueda)}`);
          const datos = await resp.json();
          if (!datos || datos.length === 0) { sugerencias.style.display = 'none'; return; }
          sugerencias.innerHTML = datos.map(j => `
            <div class="sugerencia-item" data-url="/jugadores/${encodeURIComponent(j.nombre.toLowerCase().replace(/ /g,'-'))}/">
              <img src="${j.foto}" alt="${j.nombre}">
              <div style="display:flex;flex-direction:column;">
                <strong style="font-size:0.95rem;color:#0f172a;">${j.nombre}</strong>
                <small style="color:#64748b;">${j.team_name} · ${j.posicion}</small>
              </div>
            </div>
          `).join('');
          sugerencias.style.display = 'block';
        } catch (e) { console.error(e); }
      }, 300);
    });

    sugerencias.addEventListener('click', (ev) => {
      const s = ev.target.closest('.sugerencia-item');
      if (s) window.location.href = s.dataset.url;
    });

    document.addEventListener('click', (ev) => {
      if (!buscador.contains(ev.target) && !sugerencias.contains(ev.target)) sugerencias.style.display = 'none';
    });
  }
</script>
<script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/cc1120e60af0020f9479833b/script.js"></script>
<script>
// Filtrado avanzado de tabla
const tabla = document.getElementById('tablaJugadores');
const filtroEquipo = document.getElementById('filtroEquipo');
const filtroPosicion = document.getElementById('filtroPosicion');
const filtroEstado = document.getElementById('filtroEstado');
const filtroRecomendacion = document.getElementById('filtroRecomendacion');
const filtroRating = document.getElementById('filtroRating');
const filtroTitular = document.getElementById('filtroTitular');

function aplicarFiltros() {
  const filas = tabla.querySelectorAll('tbody tr');
  let visibles = 0;

  filas.forEach(fila => {
    const equipo = fila.cells[1].textContent.trim();
    const posicion = fila.cells[2].textContent.trim();
    const rating = parseFloat(fila.cells[4].textContent) || 0;
    const titular = parseFloat(fila.cells[9].textContent) || 0;
    const recomendacion = parseFloat(fila.cells[11].dataset.sort) || 0;
    const estadoTexto = fila.cells[12].textContent.trim();

    // Determinar estado según el badge
    let estado = 'disponible';
    if (estadoTexto.includes('LESIONADO')) {
      estado = 'lesionado';
    } else if (estadoTexto.includes('SANCIONADO')) {
      estado = 'sancionado';
    } else if (estadoTexto.includes('OTROS')) {
      estado = 'otros';
    }

    let mostrar = true;

    if (filtroEquipo.value && !equipo.includes(filtroEquipo.value)) mostrar = false;
    if (filtroPosicion.value && posicion !== filtroPosicion.value) mostrar = false;
    if (filtroEstado.value && estado !== filtroEstado.value) mostrar = false;

    // Filtro de recomendación
    if (filtroRecomendacion.value) {
      if (filtroRecomendacion.value === 'oro' && recomendacion < 90) mostrar = false;
      if (filtroRecomendacion.value === 'plata' && (recomendacion < 70 || recomendacion >= 90)) mostrar = false;
      if (filtroRecomendacion.value === 'bronce' && (recomendacion < 40 || recomendacion >= 70)) mostrar = false;
      if (filtroRecomendacion.value === 'riesgo' && recomendacion >= 40) mostrar = false;
    }

    if (filtroRating.value && rating < parseFloat(filtroRating.value)) mostrar = false;
    if (filtroTitular.value && titular < parseFloat(filtroTitular.value)) mostrar = false;

    fila.style.display = mostrar ? '' : 'none';
    if (mostrar) visibles++;
  });

  // Mostrar contador
  const contador = document.getElementById('contadorResultados') || crearContador();
  contador.textContent = `Mostrando ${visibles} de ${filas.length} jugadores`;
}

function crearContador() {
  const contador = document.createElement('div');
  contador.id = 'contadorResultados';
  contador.style.cssText = 'text-align: center; padding: 1rem; background: #f1f5f9; border-radius: 8px; margin: 1rem auto; max-width: 1200px; font-weight: 600;';
  tabla.parentNode.insertBefore(contador, tabla);
  return contador;
}

[filtroEquipo, filtroPosicion, filtroEstado, filtroRecomendacion, filtroRating, filtroTitular].forEach(filtro => {
  filtro.addEventListener('change', aplicarFiltros);
  if (filtro.type === 'number') filtro.addEventListener('input', aplicarFiltros);
});

// Inicializar contador
aplicarFiltros();

// ========================================
// ORDENAMIENTO DE TABLA
// ========================================
const sortableHeaders = document.querySelectorAll('.tabla-jugadores th.sortable');
let currentSortColumn = null;
let currentSortOrder = 'asc';

sortableHeaders.forEach(header => {
  header.addEventListener('click', function() {
    const column = parseInt(this.dataset.column);
    const type = this.dataset.type;

    // Si es la misma columna, cambiar orden; si no, resetear a ascendente
    if (currentSortColumn === column) {
      currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
    } else {
      currentSortOrder = 'asc';
    }
    currentSortColumn = column;

    // Remover clases de ordenamiento de todos los headers
    sortableHeaders.forEach(h => {
      h.classList.remove('asc', 'desc');
    });

    // Agregar clase al header actual
    this.classList.add(currentSortOrder);

    // Ordenar la tabla
    sortTable(column, type, currentSortOrder);
  });
});

function sortTable(column, type, order) {
  const tbody = tabla.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr'));

  rows.sort((a, b) => {
    let aValue, bValue;
    const aCell = a.cells[column];
    const bCell = b.cells[column];

    if (type === 'number') {
      // Usar data-sort si está disponible, sino extraer del texto
      aValue = aCell.dataset.sort ? parseFloat(aCell.dataset.sort) : parseFloat(aCell.textContent.trim().replace('%', '')) || 0;
      bValue = bCell.dataset.sort ? parseFloat(bCell.dataset.sort) : parseFloat(bCell.textContent.trim().replace('%', '')) || 0;
    } else {
      // Para columnas de texto
      aValue = aCell.textContent.trim().toLowerCase();
      bValue = bCell.textContent.trim().toLowerCase();
    }

    if (type === 'number') {
      return order === 'asc' ? aValue - bValue : bValue - aValue;
    } else {
      if (aValue < bValue) return order === 'asc' ? -1 : 1;
      if (aValue > bValue) return order === 'asc' ? 1 : -1;
      return 0;
    }
  });

  // Reordenar el DOM
  rows.forEach(row => tbody.appendChild(row));
}
</script>
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