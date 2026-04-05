<?php
/**
 * DERMACOMP PRO - VERSIÓN ULTRA-COMPATIBLE
 * Todo el diseño premium inyectado directamente
 */

$local_file = 'data/precios.json';
$github_user = "crisiian";
$github_repo = "dermacomp-monitor";
$github_url = "https://raw.githubusercontent.com/$github_user/$github_repo/main/data/precios.json";

$json_data = null;
if (file_exists($local_file)) {
    $json_data = file_get_contents($local_file);
    $source = "Local";
} else {
    $json_data = @file_get_contents($github_url);
    $source = "GitHub";
}

$data = json_decode($json_data, true);
$productos = $data['productos'] ?? [];
$actualizacion = $data['ultima_actualizacion'] ?? 'No disponible';

$grupos = [];
foreach ($productos as $p) {
    if (isset($p['en_grupo']) && $p['en_grupo']) {
        $gid = $p['grupo_id'];
        if (!isset($grupos[$gid])) $grupos[$gid] = [];
        $grupos[$gid][] = $p;
    } else {
        $grupos[$p['nombre'] . uniqid()] = [$p];
    }
}

function formatCOP($valor) {
    return '$ ' . number_format($valor, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dermacomp Pro | Inteligencia de Precios</title>
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/3063/3063822.png">
    <style>
        /* CSS PREMIUM INYECTADO - DERMACOMP PRO */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        
        :root {
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.3);
            --bg-dark: #020617;
            --card-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            background-image: radial-gradient(circle at 10% 20%, rgba(16, 185, 129, 0.08) 0%, transparent 50%);
            background-attachment: fixed;
            padding: 2rem;
        }

        .container { max-width: 1400px; margin: 0 auto; }

        header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 3rem; padding: 1.5rem 2rem;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border); border-radius: 1.5rem;
        }

        .logo-area h1 {
            font-size: 1.75rem; font-weight: 800;
            background: linear-gradient(to right, #10b981, #34d399);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .status-info { font-size: 0.85rem; color: var(--text-muted); background: rgba(0,0,0,0.3); padding: 0.5rem 1rem; border-radius: 2rem; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
        
        .stat-card {
            background: var(--card-bg); backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border); padding: 2rem; border-radius: 1.5rem;
        }

        .stat-card h3 { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; }
        .stat-card .value { font-size: 2.5rem; font-weight: 700; }
        .value.emerald { color: var(--primary); }

        .controls-bar { display: flex; gap: 1rem; margin-bottom: 2rem; align-items: center; }
        .search-wrapper { flex: 1; }
        .search-input {
            width: 100%; background: var(--card-bg); border: 1px solid var(--glass-border);
            padding: 1rem 1.5rem; border-radius: 1rem; color: white; font-size: 1rem;
        }

        .btn-refresh {
            background: var(--primary); color: var(--bg-dark); border: none;
            padding: 1rem 2rem; border-radius: 1rem; font-weight: 700; cursor: pointer;
        }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem; }

        .product-card {
            background: var(--card-bg); border: 1px solid var(--glass-border);
            border-radius: 2rem; overflow: hidden; display: flex; flex-direction: column;
        }

        .card-top { padding: 1.5rem; display: flex; gap: 1.5rem; background: rgba(255,255,255,0.03); }
        .img-container { width: 90px; height: 90px; background: white; border-radius: 1.25rem; padding: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .img-container img { max-width: 100%; max-height: 100%; object-fit: contain; }

        .item-info h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.25rem; }
        .brand-label { font-size: 0.75rem; font-weight: 600; color: var(--primary); text-transform: uppercase; }

        .card-bottom { padding: 1.5rem; }
        .price-item { display: grid; grid-template-columns: 100px 1fr auto; align-items: center; gap: 1rem; padding: 0.8rem 1rem; border-radius: 1.25rem; margin-bottom: 0.75rem; background: rgba(0,0,0,0.2); }
        .price-item.best { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); }

        .store-tag { font-size: 0.7rem; font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 0.75rem; text-align: center; }
        .tag-medipiel { background: #be185d; color: white; }
        .tag-bellapiel { background: #b45309; color: white; }
        .tag-cruzverde { background: #15803d; color: white; }
        .tag-amazon { background: #232f3e; border: 1px solid #ff9900; color: #ff9900; }
        .tag-colsubsidio { background: #1d4ed8; color: white; }
        .tag-xito { background: #fbbf24; color: #1a1a1a; }
        .tag-éxito { background: #fbbf24; color: #1a1a1a; }

        .btn-buy { text-decoration: none; color: white; background: rgba(255,255,255,0.1); padding: 0.5rem 1rem; border-radius: 0.75rem; font-size: 0.8rem; font-weight: 600; }

        @media (max-width: 768px) {
            body { padding: 1rem; }
            header { flex-direction: column; gap: 1rem; }
            .controls-bar { flex-direction: column; }
            .product-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-area">
                <h1>Dermacomp <span>PRO</span></h1>
            </div>
            <div class="status-info">
                <span>Última Sincronización: <?php echo date('d M, H:i', strtotime($actualizacion)); ?> (<?php echo $source; ?>)</span>
            </div>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <h3>Monitoreo Activo</h3>
                <div class="value"><?php echo count($productos); ?> <small>SKUs</small></div>
            </div>
            <div class="stat-card">
                <h3>Mejor Oportunidad</h3>
                <div class="value emerald">15% <small>OFF</small></div>
            </div>
            <div class="stat-card">
                <h3>Tiendas Conectadas</h3>
                <p class="value">4 <small>Sedes</small></p>
            </div>
        </section>

        <div class="controls-bar">
            <div class="search-wrapper">
                <input type="text" id="searchInput" class="search-input" placeholder="Buscar producto, marca o tienda..." onkeyup="filterProducts()">
            </div>
            <button class="btn-refresh" onclick="location.reload()">Sincronizar Ahora</button>
        </div>

        <main class="product-grid" id="productGrid">
            <?php foreach ($grupos as $gid => $items): 
                usort($items, function($a, $b) { return $a['precio'] <=> $b['precio']; });
                $base = $items[0];
            ?>
            <article class="product-card" data-search="<?php echo strtolower($base['nombre'] . ' ' . $base['marca'] . ' ' . implode(' ', array_column($items, 'tienda'))); ?>">
                <div class="card-top">
                    <div class="img-container">
                        <img src="<?php echo htmlspecialchars($base['imagen']); ?>" alt="img" onerror="this.src='https://placehold.co/100x100?text=Derma'">
                    </div>
                    <div class="item-info">
                        <span class="brand-label"><?php echo htmlspecialchars($base['marca']); ?></span>
                        <h2><?php echo htmlspecialchars($base['nombre']); ?></h2>
                    </div>
                </div>
                <div class="card-bottom">
                    <?php foreach ($items as $i => $item): 
                        $is_best = ($i === 0 && count($items) > 1);
                        $store_class = "tag-" . strtolower(str_replace(' ', '', $item['tienda']));
                    ?>
                    <div class="price-item <?php echo $is_best ? 'best' : ''; ?>">
                        <span class="store-tag <?php echo $store_class; ?>">
                            <?php echo htmlspecialchars($item['tienda']); ?>
                        </span>
                        <span class="price-val"><?php echo formatCOP($item['precio']); ?></span>
                        <a href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" class="btn-buy">Ver Oferta</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </main>
    </div>

    <script>
    function filterProducts() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const grid = document.getElementById('productGrid');
        const cards = grid.getElementsByClassName('product-card');

        for (let i = 0; i < cards.length; i++) {
            const searchData = cards[i].getAttribute('data-search');
            if (searchData.includes(filter)) {
                cards[i].style.display = "";
            } else {
                cards[i].style.display = "none";
            }
        }
    }
    </script>
</body>
</html>
