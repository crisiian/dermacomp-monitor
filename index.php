<?php
/**
 * DERMACOMP PRO - MONITOR DE PRECIOS PREMIUM
 * Versión: 2.0
 */

// 1. Cargar Datos (Prioridad: Local > GitHub)
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

// 2. Procesar Grupos
$grupos = [];
foreach ($productos as $p) {
    if (isset($p['en_grupo']) && $p['en_grupo']) {
        $gid = $p['grupo_id'];
        if (!isset($grupos[$gid])) $grupos[$gid] = [];
        $grupos[$gid][] = $p;
    } else {
        $grupos[$p['nombre'] . uniqid()] = [$p]; // Los solitarios se tratan como grupos de 1
    }
}

// 3. Formatear Moneda COP
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
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="https://cdn-icons-png.flaticon.com/512/3063/3063822.png">
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
                <div class="value amber">4 <small>Sedes</small></div>
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
