<?php
/*
Template Name: Home LabelMe
*/

global $wpdb;

// Obtener equipos únicos para el filtro
$equipos = $wpdb->get_results("
  SELECT DISTINCT team_name, team_logo 
  FROM jugadores_laliga 
  WHERE season = '2025' 
  ORDER BY team_name
");

// Obtener jugadores destacados (top rating)
$jugadores_destacados = $wpdb->get_results("
  SELECT nombre, foto, team_name, team_logo, posicion, rating, goles_total, goles_asistencias, CONCAT(ROUND(porcentaje_titularidades,0),'%') AS porcentaje_titularidades
  FROM jugadores_laliga
  WHERE season = '2025'
  AND porcentaje_titularidades>=80
  AND baja = 0
  ORDER BY CAST(rating AS DECIMAL(10,2)) DESC
  LIMIT 6
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LabelMe - Estadísticas Fantasy LaLiga 2025-26 | Datos Actualizados</title>
  <meta name="description" content="Consulta estadísticas actualizadas de todos los jugadores de LaLiga para Fantasy. Goles, asistencias, rating, minutos y más datos en tiempo real.">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <link rel="canonical" href="<?php echo home_url('/'); ?>">

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

<section class="hero-modern">
  <div class="hero-container">
    <div class="hero-badge">
      ⚽ Temporada 2025-26
    </div>
    
    <h1>
      Estadísticas <span class="highlight">Fantasy LaLiga</span>
    </h1>
    
    <p class="hero-subtitle">
      Datos actualizados · 636 jugadores · 20 equipos
    </p>

    <!-- Buscador integrado en hero -->
    <div class="hero-search">
      <div class="autocomplete-container">
        <input 
          type="text" 
          id="buscador" 
          placeholder="Buscar jugador o equipo..." 
          autocomplete="off"
        />
        <div id="sugerencias" class="sugerencias-lista"></div>
      </div>
    </div>

    <div class="hero-actions">
      <a href="/jugadores/" class="btn-hero btn-primary">
        Ver Jugadores
      </a>
      <a href="/comparador/" class="btn-hero btn-secondary">
        Comparador
      </a>
    </div>
  </div>
</section>


<!-- BARRA SUPERIOR DE EQUIPOS -->
<div class="equipos-barra">
  <div class="equipos-barra-inner">
    <?php foreach ($equipos as $equipo): ?>
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

<?php
// Obtener MÁXIMO 3 bajas más recientes
$bajas_recientes = $wpdb->get_results("
  SELECT nombre, foto, team_name, team_logo, tipo_baja, posicion
  FROM jugadores_laliga
  WHERE baja = 1 AND season = '2025'
  ORDER BY actualizado DESC
  LIMIT 3
");

if ($bajas_recientes):
?>
<section class="alertas-bajas-compact">
  <div class="bajas-container">
    <div class="bajas-header">
      <h3>⚠️ Jugadores de Baja</h3>
      <a href="/jugadores/?estado=lesionado" class="ver-todas">Ver todas →</a>
    </div>
    
    <div class="bajas-grid">
      <?php foreach ($bajas_recientes as $baja): 
        $slug_baja = sanitize_title($baja->nombre);
        $url_baja = home_url('/jugadores/' . $slug_baja . '/');
      ?>
        <a href="<?php echo esc_url($url_baja); ?>" class="baja-card-mini">
          <div class="baja-avatar">
            <img src="<?php echo esc_url($baja->foto); ?>" alt="<?php echo esc_attr($baja->nombre); ?>">
            <span class="baja-icon">🚑</span>
          </div>
          <div class="baja-info">
            <strong><?php echo esc_html($baja->nombre); ?></strong>
            <div class="baja-meta">
              <img src="<?php echo esc_url($baja->team_logo); ?>" alt="">
              <span><?php echo esc_html($baja->team_name); ?></span>
            </div>
            <span class="baja-tipo"><?php echo esc_html($baja->tipo_baja); ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- SECCIÓN DE JUGADORES DESTACADOS -->
<section class="destacados-section">
  <h2 data-aos="fade-up">🌟 Jugadores Destacados</h2>
  <div class="jugadores-grid">
    <?php foreach ($jugadores_destacados as $jugador): 
      $slug = labelme_sanitize_slug($jugador->nombre);
      $url = home_url('/jugadores/' . $slug . '/');
    ?>
      <a href="<?php echo esc_url($url); ?>" class="jugador-destacado" data-aos="fade-up">
        <img src="<?php echo esc_url($jugador->foto); ?>" alt="<?php echo esc_attr($jugador->nombre); ?>">
        <h3><?php echo esc_html($jugador->nombre); ?></h3>
        <div class="equipo">
          <img src="<?php echo esc_url($jugador->team_logo); ?>" alt="">
          <span><?php echo esc_html($jugador->team_name); ?></span>
        </div>
        <div class="stats-mini">
          <div>
            <div class="label">Rating</div>
            <div class="value"><?php echo esc_html($jugador->rating); ?></div>
          </div>
          <div>
            <div class="label">Goles</div>
            <div class="value"><?php echo esc_html($jugador->goles_total); ?></div>
          </div>
          <div>
            <div class="label">Asist.</div>
            <div class="value"><?php echo esc_html($jugador->goles_asistencias); ?></div>
          </div>
          <div>
            <div class="label">%Tiular</div>
            <div class="value"><?php echo esc_html($jugador->porcentaje_titularidades); ?></div>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- SECCIÓN DE NOTICIAS -->
<section class="noticias-section">
  <h2 data-aos="fade-up">📰 Últimas Noticias LaLiga</h2>
  <div id="noticias-grid" class="noticias-grid">
    <!-- Placeholders de carga -->
    <div class="noticia-card">
      <div class="loading-skeleton" style="height: 200px;"></div>
      <div class="noticia-content">
        <div class="loading-skeleton" style="height: 20px; width: 60%; margin-bottom: 10px;"></div>
        <div class="loading-skeleton" style="height: 60px;"></div>
      </div>
    </div>
    <div class="noticia-card">
      <div class="loading-skeleton" style="height: 200px;"></div>
      <div class="noticia-content">
        <div class="loading-skeleton" style="height: 20px; width: 60%; margin-bottom: 10px;"></div>
        <div class="loading-skeleton" style="height: 60px;"></div>
      </div>
    </div>
    <div class="noticia-card">
      <div class="loading-skeleton" style="height: 200px;"></div>
      <div class="noticia-content">
        <div class="loading-skeleton" style="height: 20px; width: 60%; margin-bottom: 10px;"></div>
        <div class="loading-skeleton" style="height: 60px;"></div>
      </div>
    </div>
  </div>
</section>

<footer>
  <p>&copy; 2025 LabelMe. Estadísticas Fantasy LaLiga actualizadas.</p>
</footer>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>

<script>
  // Inicializar animaciones
  AOS.init({ duration: 800, once: true });
  
  // Fondo animado
  VANTA.NET({
    el: "#hero-vanta",
    color: 0x60a5fa,
    backgroundColor: 0x0f172a,
    points: 12.0,
    maxDistance: 25.0,
    spacing: 16.0
  });
</script>

<script>
async function cargarNoticias() {
  try {
    const response = await fetch('/wp-json/labelme/v1/noticias?limit=6');
    const data = await response.json();

    const noticiasGrid = document.getElementById("noticias-grid");
    noticiasGrid.innerHTML = "";

    data.forEach(noticia => {
      const article = document.createElement("a");
      article.href = noticia.link;
      article.target = "_blank";
      article.rel = "noopener noreferrer";
      article.className = "noticia-card";

      article.innerHTML = `
        ${noticia.image 
          ? `<img src="${noticia.image}" alt="${noticia.title}" class="noticia-img" onerror="this.style.display='none'">`
          : `<div class="noticia-img" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"></div>`
        }
        <div class="noticia-content">
          <div class="noticia-fecha">${noticia.pubDate}</div>
          <div class="noticia-title">${noticia.title}</div>
          <div class="noticia-desc">${noticia.description}</div>
        </div>
      `;

      noticiasGrid.appendChild(article);
    });

  } catch (error) {
    console.error("Error cargando noticias:", error);
    mostrarErrorNoticias();
  }
}


  function mostrarErrorNoticias() {
    const noticiasGrid = document.getElementById("noticias-grid");
    noticiasGrid.innerHTML = `
      <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #64748b;">
        <p>📰 No se pudieron cargar las noticias en este momento.</p>
        <p style="margin-top: 1rem;">
          <a href="https://www.marca.com/futbol/primera-division.html" target="_blank" rel="noopener" style="color: #3b82f6; text-decoration: none; font-weight: 600;">
            Ver noticias en Marca →
          </a>
        </p>
      </div>
    `;
  }

  // Cargar noticias al iniciar
  cargarNoticias();
</script>


<script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/cc1120e60af0020f9479833b/script.js"></script>
<script>
const buscador = document.getElementById('buscador');
const sugerencias = document.getElementById('sugerencias');
let timeoutId;

buscador.addEventListener('input', function() {
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
            
            if (datos.length === 0) {
                sugerencias.style.display = 'none';
                return;
            }
            
            sugerencias.innerHTML = datos.map(item => {
                const isEquipo = item.tipo === 'equipo';
                const url = isEquipo
                    ? `/equipos/${encodeURIComponent(item.team_name.toLowerCase().replace(/ /g, '-'))}/`
                    : `/jugadores/${encodeURIComponent(item.nombre.toLowerCase().replace(/ /g, '-'))}/`;

                return `
                <div class="sugerencia-item" data-url="${url}">
                    <img src="${item.foto}" alt="${item.nombre}">
                    <div class="sugerencia-info">
                        <div class="sugerencia-nombre">${item.nombre}</div>
                        <div class="sugerencia-equipo">
                            ${!isEquipo ? `
                                <img src="${item.team_logo}" alt="${item.team_name}">
                                ${item.team_name} - ${item.posicion}
                            ` : `
                                <span>⚽ Equipo de LaLiga</span>
                            `}
                        </div>
                    </div>
                </div>
                `;
            }).join('');
            
            sugerencias.style.display = 'block';
        } catch (error) {
            console.error('Error buscando jugadores:', error);
        }
    }, 300);
});

// Manejar clics en sugerencias
sugerencias.addEventListener('click', (e) => {
    const sugerencia = e.target.closest('.sugerencia-item');
    if (sugerencia) {
        window.location.href = sugerencia.dataset.url;
    }
});

// Cerrar sugerencias al hacer clic fuera
document.addEventListener('click', (e) => {
    if (!buscador.contains(e.target) && !sugerencias.contains(e.target)) {
        sugerencias.style.display = 'none';
    }
});
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