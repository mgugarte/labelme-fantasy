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
      display: flex;
      align-items: center;
    }

    header img { height: 40px; }

    .hero {
      text-align: center;
      color: white;
      padding: 4rem 1rem;
      background-color: #0f172a;
    }

    .hero h2 { font-size: 2rem; margin-bottom: 0.5rem; }
    .hero p { font-size: 1.1rem; opacity: 0.9; }

    .filtros {
      text-align: center;
      margin: 2rem auto;
    }
    
    .filtros input, .filtros select {
      padding: 0.6rem 1rem;
      margin: 0.5rem;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 1rem;
      width: 220px;
      max-width: 90%;
    }

    .tabla-jugadores {
      width: 95%;
      margin: 2rem auto;
      border-collapse: collapse;
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .tabla-jugadores th {
      background-color: #0f172a;
      color: #fff;
      padding: 10px;
      text-align: left;
      cursor: pointer;
      user-select: none;
    }

    .tabla-jugadores th:hover { background-color: #1e293b; }

    .tabla-jugadores td {
      border-bottom: 1px solid #e5e7eb;
      padding: 8px;
      vertical-align: middle;
    }

    .tabla-jugadores tr:nth-child(even) { background-color: #f1f5f9; }

    .tabla-jugadores th.sorted-asc::after { content: " ▲"; font-size: 0.8em; }
    .tabla-jugadores th.sorted-desc::after { content: " ▼"; font-size: 0.8em; }

    .jugador-img, .team-logo {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      object-fit: cover;
      vertical-align: middle;
      margin-right: 6px;
    }

    .badge-baja {
      background-color: #ef4444;
      color: white;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-block;
    }

    .badge-disponible {
      background-color: #10b981;
      color: white;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-block;
    }

    footer {
      text-align: center;
      padding: 1rem;
      margin-top: 3rem;
      font-size: 0.9rem;
      color: #64748b;
    }
  </style>
</head>
<body>
<header>
  <a href="/" style="display: flex; align-items: center;">
    <img src="http://labelme.es/wp-content/uploads/2025/05/labelMe_negro-removebg-preview.png" alt="LabelMe">
  </a>
</header>

<section class="breadcrumb-jugadores" style="background: white; padding: 1.5rem 2rem; border-bottom: 2px solid #e5e7eb;">
  <div style="max-width: 1200px; margin: 0 auto;">
    <a href="/" style="color: #3b82f6; text-decoration: none; font-weight: 600;">← Volver al inicio</a>
    <h1 style="color: #0f172a; font-size: 2rem; margin-top: 1rem;">
      📊 Listado Completo de Jugadores LaLiga 2024-25
    </h1>
    <p style="color: #64748b; margin-top: 0.5rem;">
      <?php echo $wpdb->get_var("SELECT COUNT(*) FROM jugadores_laliga WHERE season = '2025'"); ?> jugadores disponibles
    </p>
  </div>
</section>

<section class="intro-seo" style="max-width: 1200px; margin: 2rem auto; padding: 0 2rem;">
  <div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
    
    <h1 style="color: #0f172a; font-size: 1.8rem; margin-bottom: 1rem;">
      Estadísticas Fantasy LaLiga 2024-25: Todos los jugadores actualizados
    </h1>
    
    <p style="font-size: 1.1rem; line-height: 1.6; color: #475569; margin-bottom: 1rem;">
      Accede a las <strong>estadísticas fantasy LaLiga</strong> más completas y actualizadas de la temporada 2024-25. 
      Consulta el rendimiento de cada jugador, minutos disputados, goles, asistencias y rating para tomar las 
      mejores decisiones en tu <a href="https://fantasy.laliga.com/" target="_blank" rel="noopener" style="color: #3b82f6;">equipo Fantasy</a>.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
      
      <div style="display: flex; gap: 1rem; align-items: start;">
        <img src="http://labelme.es/wp-content/uploads/2025/01/estadisticas-icon.png" 
             alt="Estadísticas fantasy LaLiga actualizadas" 
             style="width: 50px; height: 50px; object-fit: contain;" 
             onerror="this.style.display='none'">
        <div>
          <h3 style="color: #0f172a; font-size: 1.1rem; margin: 0 0 0.5rem 0;">📊 Datos en tiempo real</h3>
          <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
            Información actualizada de todos los jugadores de LaLiga con sus estadísticas completas.
          </p>
        </div>
      </div>

      <div style="display: flex; gap: 1rem; align-items: start;">
        <img src="http://labelme.es/wp-content/uploads/2025/01/jugadores-icon.png" 
             alt="Jugadores LaLiga Fantasy" 
             style="width: 50px; height: 50px; object-fit: contain;"
             onerror="this.style.display='none'">
        <div>
          <h3 style="color: #0f172a; font-size: 1.1rem; margin: 0 0 0.5rem 0;">⚽ Análisis detallado</h3>
          <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
            Compara rendimiento, titularidades y participación de cada jugador para optimizar tu equipo.
          </p>
        </div>
      </div>

      <div style="display: flex; gap: 1rem; align-items: start;">
        <img src="http://labelme.es/wp-content/uploads/2025/01/lesiones-icon.png" 
             alt="Estado de forma jugadores LaLiga" 
             style="width: 50px; height: 50px; object-fit: contain;"
             onerror="this.style.display='none'">
        <div>
          <h3 style="color: #0f172a; font-size: 1.1rem; margin: 0 0 0.5rem 0;">🏥 Estado de lesiones</h3>
          <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
            Consulta qué jugadores están disponibles o de baja para planificar tus fichajes.
          </p>
        </div>
      </div>

    </div>

    <h2 style="color: #0f172a; font-size: 1.5rem; margin: 2rem 0 1rem 0;">
      Cómo usar las estadísticas fantasy LaLiga
    </h2>
    
    <p style="line-height: 1.6; color: #475569; margin-bottom: 1rem;">
      Nuestra tabla interactiva te permite filtrar por equipo, buscar jugadores específicos y ordenar por cualquier 
      estadística. Haz clic en el nombre de cualquier jugador para ver su perfil completo con análisis detallado 
      de rendimiento, historial y recomendaciones para Fantasy LaLiga.
    </p>

    <p style="line-height: 1.6; color: #475569; margin-bottom: 1rem;">
      Las <strong>estadísticas fantasy LaLiga</strong> incluyen: goles totales, asistencias, tarjetas amarillas y rojas, 
      rating promedio, minutos jugados, porcentaje de titularidad y participación. Toda la información que necesitas 
      para dominar tu liga fantasy está aquí.
    </p>

    <div style="background: #f1f5f9; border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
      <p style="margin: 0; color: #475569; font-size: 0.95rem;">
        💡 <strong>Tip:</strong> Consulta también las páginas individuales de cada jugador para ver análisis personalizados 
        y proyecciones de rendimiento. Descubre enlaces internos en la tabla para explorar más detalles.
      </p>
    </div>

  </div>
</section>
<<!-- LÍNEA ~70: MODIFICAR el filtro de equipos para incluir escudos -->
<section class="filtros" data-aos="fade-up">
  <input type="text" id="buscador" placeholder="🔎 Buscar jugador..." />
  <select id="filtroEquipo" style="padding-left: 45px; background-repeat: no-repeat; background-position: 10px center; background-size: 25px;">
    <option value="">⚽ Filtrar por equipo</option>
  </select>
</section>

<?php
global $wpdb;
$resultados = $wpdb->get_results("
  SELECT nombre, foto, team_name as equipo, team_logo, posicion, minutos, rating, 
         goles_total, goles_asistencias, amarillas, rojas, 
         porcentaje_titularidades, porcentaje_participacion,
         baja, tipo_baja, razon_baja
  FROM jugadores_laliga
  WHERE season = '2025'
  ORDER BY equipo, nombre
");

if ($resultados) {
  echo '<table class="tabla-jugadores" id="tablaJugadores" data-aos="fade-up">';
  echo '<thead><tr>';
  echo '<th>Jugador</th><th>Equipo</th><th>Posición</th><th>Minutos</th><th>Rating</th>';
  echo '<th>Goles</th><th>Asistencias</th><th>Amarillas</th><th>Rojas</th>';
  echo '<th>% Titularidad</th><th>% Participación</th><th>Estado</th><th>Tipo Baja</th><th>Razón</th>';
  echo '</tr></thead><tbody>';

foreach ($resultados as $fila) {
    // Generar slug del jugador para la URL
    $slug_jugador = sanitize_title($fila->nombre);
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
    echo '<td>' . esc_html($fila->minutos) . '</td>';
    echo '<td>' . esc_html($fila->rating) . '</td>';
    echo '<td>' . esc_html($fila->goles_total) . '</td>';
    echo '<td>' . esc_html($fila->goles_asistencias) . '</td>';
    echo '<td>' . esc_html($fila->amarillas) . '</td>';
    echo '<td>' . esc_html($fila->rojas) . '</td>';
    echo '<td>' . esc_html($fila->porcentaje_titularidades) . '%</td>';
    echo '<td>' . esc_html($fila->porcentaje_participacion) . '%</td>';
    
    if ($fila->baja == 1) {
      echo '<td><span class="badge-baja">BAJA</span></td>';
    } else {
      echo '<td><span class="badge-disponible">OK</span></td>';
    }
    
    echo '<td>' . ($fila->tipo_baja ? esc_html($fila->tipo_baja) : '-') . '</td>';
    echo '<td>' . ($fila->razon_baja ? esc_html($fila->razon_baja) : '-') . '</td>';
    echo '</tr>';
  }
  echo '</tbody></table>';
} else {
  echo '<p style="text-align:center;">No hay datos disponibles por el momento.</p>';
}
?>

<footer>&copy; 2025 LabelMe. Todos los derechos reservados.</footer>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({ duration: 800, once: true });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta/dist/vanta.waves.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.fog.min.js"></script>

<script>
  VANTA.NET({
    el: ".hero",
    color: 0x60a5fa,
    backgroundColor: 0x0f172a,
    points: 10.0,
    maxDistance: 25.0,
    spacing: 18.0
  });

  const buscador = document.getElementById('buscador');
  const filtroEquipo = document.getElementById('filtroEquipo');
  const tabla = document.getElementById('tablaJugadores');
  const filas = tabla.getElementsByTagName('tr');

// ======== FILTRO EQUIPOS CON ESCUDOS ========
  const equiposMap = new Map();
  for (let i = 1; i < filas.length; i++) {
    const equipo = filas[i].cells[1].innerText.trim();
    const logoImg = filas[i].cells[1].querySelector('img');
    const logoSrc = logoImg ? logoImg.src : '';
    if (!equiposMap.has(equipo)) {
      equiposMap.set(equipo, logoSrc);
    }
  }

  [...equiposMap.entries()].sort((a, b) => a[0].localeCompare(b[0])).forEach(([equipo, logo]) => {
    const option = document.createElement('option');
    option.value = equipo;
    option.textContent = equipo;
    option.setAttribute('data-logo', logo);
    filtroEquipo.appendChild(option);
  });

  // Actualizar el fondo del select cuando cambia
  filtroEquipo.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const logo = selectedOption.getAttribute('data-logo');
    if (logo && logo !== '') {
      this.style.backgroundImage = `url(${logo})`;
    } else {
      this.style.backgroundImage = 'none';
    }
    filtrarTabla();
  });

  // ======== ORDENAMIENTO POR COLUMNAS ========
  const ths = tabla.querySelectorAll('th');
  let ordenActual = {};

  ths.forEach((th, idx) => {
    th.addEventListener('click', () => {
      const asc = !(ordenActual[idx] && ordenActual[idx] === 'asc');
      ordenarTabla(idx, asc);
      ordenActual = { [idx]: asc ? 'asc' : 'desc' };
      ths.forEach(header => header.classList.remove('sorted-asc', 'sorted-desc'));
      th.classList.add(asc ? 'sorted-asc' : 'sorted-desc');
    });
  });

  function ordenarTabla(columna, asc = true) {
    const tbody = tabla.querySelector('tbody');
    const filasArray = Array.from(tbody.querySelectorAll('tr'));
    
    filasArray.sort((a, b) => {
      const aText = a.cells[columna].innerText.trim();
      const bText = b.cells[columna].innerText.trim();
      const aNum = parseFloat(aText.replace(',', '.').replace('%', ''));
      const bNum = parseFloat(bText.replace(',', '.').replace('%', ''));
      const esNumerico = !isNaN(aNum) && !isNaN(bNum);
      if (esNumerico) return asc ? aNum - bNum : bNum - aNum;
      return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
    });

    tbody.innerHTML = '';
    filasArray.forEach(fila => tbody.appendChild(fila));
  }
</script>

<script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/cc1120e60af0020f9479833b/script.js"></script>
</body>
</html>