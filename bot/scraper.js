const axios = require('axios');
const fs = require('fs');
const path = require('path');
const https = require('https');

const httpsAgent = new https.Agent({ rejectUnauthorized: false });

// ============================================================
//  DERMACOMP PRO v7 - 4 Tiendas Colombianas Confirmadas
//  Medipiel + Bella Piel + Colsubsidio + Éxito
// ============================================================

async function scrapeMedipiel() {
    console.log('[1/4] Medipiel...');
    try {
        const res = await axios.get('https://www.medipiel.com.co/api/catalog_system/pub/products/search?_from=0&_to=30');
        console.log(`✓ Medipiel: ${res.data.length}`);
        return res.data.map(p => ({
            nombre: p.productName, marca: p.brand || 'Medipiel',
            precio: p.items[0]?.sellers[0]?.commertialOffer?.Price || 0,
            tienda: 'Medipiel', imagen: p.items[0]?.images[0]?.imageUrl || '', url: p.link
        })).filter(p => p.precio > 0);
    } catch (e) { console.error('! Medipiel:', e.message); return []; }
}

async function scrapeBellaPiel() {
    console.log('[2/4] Bella Piel...');
    try {
        const res = await axios.get('https://www.bellapiel.com.co/api/catalog_system/pub/products/search?_from=0&_to=30');
        console.log(`✓ Bella Piel: ${res.data.length}`);
        return res.data.map(p => ({
            nombre: p.productName, marca: p.brand || 'Bella Piel',
            precio: p.items[0]?.sellers[0]?.commertialOffer?.Price || 0,
            tienda: 'Bella Piel', imagen: p.items[0]?.images[0]?.imageUrl || '', url: p.link
        })).filter(p => p.precio > 0);
    } catch (e) { console.error('! Bella Piel:', e.message); return []; }
}

async function scrapeColsubsidio() {
    console.log('[3/4] Colsubsidio (Drog.)...');
    try {
        const res = await axios.get(
            'https://www.drogueriascolsubsidio.com/api/catalog_system/pub/products/search?_from=0&_to=30',
            { httpsAgent }
        );
        console.log(`✓ Colsubsidio: ${Array.isArray(res.data) ? res.data.length : 0}`);
        if (!Array.isArray(res.data)) return [];
        return res.data.map(p => ({
            nombre: p.productName, marca: p.brand || 'Colsubsidio',
            precio: p.items[0]?.sellers[0]?.commertialOffer?.Price || 0,
            tienda: 'Colsubsidio', imagen: p.items[0]?.images[0]?.imageUrl || '', url: p.link
        })).filter(p => p.precio > 0);
    } catch (e) { console.error('! Colsubsidio:', e.message); return []; }
}

async function scrapeExito() {
    console.log('[4/4] Éxito...');
    try {
        const res = await axios.get(
            'https://exito.com/api/catalog_system/pub/products/search?_from=0&_to=30',
            { httpsAgent }
        );
        console.log(`✓ Éxito: ${Array.isArray(res.data) ? res.data.length : 0}`);
        if (!Array.isArray(res.data)) return [];
        return res.data.map(p => ({
            nombre: p.productName, marca: p.brand || 'Éxito',
            precio: p.items[0]?.sellers[0]?.commertialOffer?.Price || 0,
            tienda: 'Éxito', imagen: p.items[0]?.images[0]?.imageUrl || '', url: p.link
        })).filter(p => p.precio > 0);
    } catch (e) { console.error('! Éxito:', e.message); return []; }
}

function processAndGroup(products) {
    const groups = [];
    const ignore = new Set(['para','con','del','los','las','isdin','protector','solar','facial','crema','gel','liquido','exito','colsubsidio','medipiel','bella','piel']);
    
    products.forEach(p => {
        const words = p.nombre.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9 ]/g, '').split(' ')
            .filter(w => w.length > 3 && !ignore.has(w));
        
        let match = groups.find(g => {
            if (words.length === 0) return false;
            return words.filter(w => g.keySet.has(w)).length >= 1;
        });
        if (match) { match.items.push(p); words.forEach(w => match.keySet.add(w)); }
        else groups.push({ keySet: new Set(words), items: [p] });
    });
    
    let final = [];
    groups.forEach((g, idx) => {
        const minP = Math.min(...g.items.map(i => i.precio));
        const gid = `G${idx.toString().padStart(3, '0')}`;
        g.items.forEach(i => final.push({
            ...i,
            es_mas_barato: (g.items.length > 1 && i.precio === minP),
            grupo_id: gid,
            en_grupo: g.items.length > 1
        }));
    });
    return final.sort((a, b) => a.grupo_id.localeCompare(b.grupo_id) || a.precio - b.precio);
}

async function run() {
    console.log('=== DERMACOMP PRO v7 - 4 TIENDAS COLOMBIANAS ===');
    const m = await scrapeMedipiel();
    const b = await scrapeBellaPiel();
    const c = await scrapeColsubsidio();
    const e = await scrapeExito();
    
    const all = processAndGroup([...m, ...b, ...c, ...e]);
    fs.writeFileSync(
        path.join(__dirname, '../data/precios.json'),
        JSON.stringify({ ultima_actualizacion: new Date().toISOString(), productos: all }, null, 2)
    );
    console.log(`\n✅ SINCRONIZACIÓN COMPLETA: ${all.length} productos guardados.`);
    console.log(`   Medipiel: ${m.length} | Bella Piel: ${b.length} | Colsubsidio: ${c.length} | Éxito: ${e.length}`);
}

run().catch(e => console.error('! Fatal:', e.message));
