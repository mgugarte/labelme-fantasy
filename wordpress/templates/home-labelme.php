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
  SELECT nombre, foto, team_name, team_logo, posicion, rating, goles_total, goles_asistencias
  FROM jugadores_laliga
  WHERE season = '2025'
  ORDER BY CAST(rating AS DECIMAL(10,2)) DESC
  LIMIT 6
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LabelMe - Estadísticas Fantasy LaLiga 2024-25 | Datos Actualizados</title>
  <meta name="description" content="Consulta estadísticas actualizadas de todos los jugadores de LaLiga para Fantasy. Goles, asistencias, rating, minutos y más datos en tiempo real.">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f5f6fa;
      color: #1e293b;
    }

    header {
      background-color: #0f172a;
      padding: 1rem 2rem;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    header img { height: 40px; }

    .hero {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
      color: white;
      padding: 6rem 2rem 4rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><path d="M50 0 L100 50 L50 100 L0 50 Z" fill="rgba(59,130,246,0.1)"/></svg>');
      opacity: 0.1;
    }

    .hero h1 {
      font-size: 3.5rem;
      font-weight: 800;
      margin-bottom: 1rem;
      position: relative;
      z-index: 1;
    }

    .hero p {
      font-size: 1.3rem;
      opacity: 0.9;
      max-width: 700px;
      margin: 0 auto 2rem;
      position: relative;
      z-index: 1;
    }

    .cta-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
    }

    .btn {
      padding: 1rem 2rem;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 700;
      font-size: 1.1rem;
      transition: all 0.3s;
      display: inline-block;
    }

    .btn-primary {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: white;
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
    }

    .btn-secondary {
      background: white;
      color: #0f172a;
      border: 2px solid white;
    }

    .btn-secondary:hover {
      background: transparent;
      color: white;
    }

    .search-section {
      max-width: 1200px;
      margin: -3rem auto 3rem;
      padding: 0 2rem;
      position: relative;
      z-index: 10;
    }

    .search-card {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }

    .search-card h2 {
      color: #0f172a;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
    }

    .search-form {
      display: grid;
      grid-template-columns: 1fr 1fr auto;
      gap: 1rem;
    }

    .search-form input,
    .search-form select {
      padding: 1rem;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      font-size: 1rem;
      font-family: inherit;
      transition: border-color 0.3s;
    }

    .search-form input:focus,
    .search-form select:focus {
      outline: none;
      border-color: #3b82f6;
    }

    .search-form button {
      padding: 1rem 2rem;
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: white;
      border: none;
      border-radius: 12px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s;
    }

    .search-form button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }

    .equipos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 1rem;
      margin-top: 2rem;
    }

    .equipo-card {
      background: #f8fafc;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      padding: 1rem;
      text-align: center;
      transition: all 0.3s;
      cursor: pointer;
      text-decoration: none;
      color: #1e293b;
    }

    .equipo-card:hover {
      border-color: #3b82f6;
      transform: translateY(-3px);
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
    }

    .equipo-card img {
      width: 60px;
      height: 60px;
      object-fit: contain;
      margin-bottom: 0.5rem;
    }

    .equipo-card span {
      font-size: 0.9rem;
      font-weight: 600;
      display: block;
    }

    .destacados-section {
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
    }

    .destacados-section h2 {
      font-size: 2.5rem;
      color: #0f172a;
      margin-bottom: 2rem;
      text-align: center;
    }

    .jugadores-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
    }

    .jugador-destacado {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s;
      text-decoration: none;
      color: inherit;
      display: block;
    }

    .jugador-destacado:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    }

    .jugador-destacado img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 1rem;
      border: 3px solid #3b82f6;
    }

    .jugador-destacado h3 {
      font-size: 1.3rem;
      color: #0f172a;
      margin-bottom: 0.5rem;
    }

    .jugador-destacado .equipo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
      color: #64748b;
    }

    .jugador-destacado .equipo img {
      width: 25px;
      height: 25px;
      border: none;
    }

    .stats-mini {
      display: flex;
      justify-content: space-around;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 2px solid #f1f5f9;
    }

    .stats-mini div {
      text-align: center;
    }

    .stats-mini .label {
      font-size: 0.75rem;
      color: #64748b;
      text-transform: uppercase;
      font-weight: 600;
    }

    .stats-mini .value {
      font-size: 1.5rem;
      font-weight: 700;
      color: #3b82f6;
    }

    /* Estilos para noticias */
    .noticias-section {
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
    }

    .noticias-section h2 {
      font-size: 2.5rem;
      color: #0f172a;
      margin-bottom: 2rem;
      text-align: center;
    }

    .noticias-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }

    .noticia-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s;
      text-decoration: none;
      color: inherit;
      display: block;
    }

    .noticia-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    }

    .noticia-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .noticia-content {
      padding: 1.5rem;
    }

    .noticia-fecha {
      font-size: 0.85rem;
      color: #64748b;
      margin-bottom: 0.5rem;
    }

    .noticia-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 0.5rem;
      line-height: 1.4;
    }

    .noticia-desc {
      font-size: 0.95rem;
      color: #64748b;
      line-height: 1.6;
    }

    .loading-skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
      border-radius: 8px;
    }

    @keyframes loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    footer {
      background: #0f172a;
      color: white;
      text-align: center;
      padding: 3rem 2rem;
      margin-top: 4rem;
    }

    @media (max-width: 768px) {
      .hero h1 { font-size: 2rem; }
      .hero p { font-size: 1rem; }
      .search-form {
        grid-template-columns: 1fr;
      }
      .equipos-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
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

<section class="hero" id="hero-vanta">
  <h1 data-aos="fade-up">Estadísticas Fantasy LaLiga</h1>
  <p data-aos="fade-up" data-aos-delay="100">
    Consulta datos actualizados de todos los jugadores de LaLiga. Goles, asistencias, rating y más.
  </p>
  <div class="cta-buttons" data-aos="fade-up" data-aos-delay="200">
    <a href="/jugadores/" class="btn btn-primary">📊 Ver Todos los Jugadores</a>
    <a href="#buscar" class="btn btn-secondary">🔍 Buscar Jugador</a>
  </div>
</section>

<section class="search-section" id="buscar">
  <div class="search-card" data-aos="fade-up">
    <h2>🔍 Busca tu jugador</h2>
    <form class="search-form" action="/jugadores/" method="get">
      <input type="text" name="buscar" placeholder="Nombre del jugador..." />
      <select name="equipo">
        <option value="">Todos los equipos</option>
        <?php foreach ($equipos as $equipo): ?>
          <option value="<?php echo esc_attr($equipo->team_name); ?>">
            <?php echo esc_html($equipo->team_name); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Buscar</button>
    </form>

    <h3 style="margin-top: 2rem; color: #0f172a;">⚽ Equipos LaLiga 2024-25</h3>
    <div class="equipos-grid">
      <?php foreach ($equipos as $equipo): ?>
        <a href="/jugadores/?equipo=<?php echo urlencode($equipo->team_name); ?>" class="equipo-card" data-aos="zoom-in">
          <img src="<?php echo esc_url($equipo->team_logo); ?>" alt="<?php echo esc_attr($equipo->team_name); ?>">
          <span><?php echo esc_html($equipo->team_name); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="destacados-section">
  <h2 data-aos="fade-up">🌟 Jugadores Destacados</h2>
  <div class="jugadores-grid">
    <?php foreach ($jugadores_destacados as $jugador): 
      $slug = sanitize_title($jugador->nombre);
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

  // Cargar noticias automáticamente
  async function cargarNoticias() {
    try {
      const rssUrl = 'https://www.mundodeportivo.com/feed/rss/futbol/laliga';
      const response = await fetch(`https://api.rss2json.com/v1/api.json?rss_url=${encodeURIComponent(rssUrl)}&count=6`);
      const data = await response.json();
      
      if (data.status === 'ok' && data.items && data.items.length > 0) {
        const noticiasGrid = document.getElementById('noticias-grid');
        noticiasGrid.innerHTML = '';
        
        data.items.forEach(item => {
          const article = document.createElement('a');
          article.href = item.link;
          article.target = '_blank';
          article.rel = 'noopener noreferrer';
          article.className = 'noticia-card';
          
          // Extraer imagen (algunas veces viene en el contenido)
          let imgSrc = item.enclosure?.link || item.thumbnail || '';
          if (!imgSrc && item.description) {
            const imgMatch = item.description.match(/<img[^>]+src="([^">]+)"/);
            if (imgMatch) imgSrc = imgMatch[1];
          }
          
          // Limpiar descripción de HTML
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = item.description || '';
          const cleanDesc = tempDiv.textContent.substring(0, 120) + '...';
          
          // Formatear fecha
          const fecha = new Date(item.pubDate);
          const fechaFormateada = fecha.toLocaleDateString('es-ES', { 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric' 
          });
          
          article.innerHTML = `
            ${imgSrc ? `<img src="${imgSrc}" alt="${item.title}" class="noticia-img" onerror="this.style.display='none'">` : '<div class="noticia-img" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"></div>'}
            <div class="noticia-content">
              <div class="noticia-fecha">${fechaFormateada}</div>
              <div class="noticia-title">${item.title}</div>
              <div class="noticia-desc">${cleanDesc}</div>
            </div>
          `;
          
          noticiasGrid.appendChild(article);
        });
      } else {
        mostrarErrorNoticias();
      }
    } catch (error) {
      console.error('Error cargando noticias:', error);
      mostrarErrorNoticias();
    }
  }

  function mostrarErrorNoticias() {
    const noticiasGrid = document.getElementById('noticias-grid');
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

  // Cargar noticias al cargar la página
  cargarNoticias();
</script>

<script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/cc1120e60af0020f9479833b/script.js"></script>
</body>
</html>