const axios = require('axios');
const fs = require('fs');
const path = require('path');
const cheerio = require('cheerio');

const SCRAPER_API_KEY = '27d7f5d9fbbe4fa88c93b2434c195571';

async function scrapeMedipiel() {
    console.log('[Medipiel] Buscando...');
    try {
        const response = await axios.get('https://www.medipiel.com.co/api/catalog_system/pub/products/search?_from=0&_to=30');
        return response.data.map(p => ({
            nombre: p.productName, marca: p.brand, precio: p.items[0]?.sellers[0]?.commertialOffer?.Price || 0,
            tienda: 'Medipiel', imagen: p.items[0]?.images[0]?.imageUrl || '', url: p.link
        })).filter(p => p.precio > 0);
    } catch (e) { return []; }
}

async function scrapeBellaPiel() {
    console.log('[Bella Piel] Buscando...');
    try {
        const response = await axios.get('https://www.bellapiel.com.co/api/catalog_system/pub/products/search?_from=0&_to=30');
        return response.data.map(p => ({
            nombre: p.productName, marca: p.brand, precio: p.items[0]?.sellers[0]?.commertialOffer?.Price || 0,
            tienda: 'Bella Piel', imagen: p.items[0]?.images[0]?.imageUrl || '', url: p.link
        })).filter(p => p.precio > 0);
    } catch (e) { return []; }
}

async function scrapeCruzVerde() {
    console.log('[Cruz Verde] Buscando...');
    try {
        // Usamos render=true y una URL de búsqueda simple
        const url = `http://api.scraperapi.com?api_key=${SCRAPER_API_KEY}&render=true&url=${encodeURIComponent('https://www.cruzverde.com.co/search?q=isdin')}`;
        const response = await axios.get(url);
        const $ = cheerio.load(response.data);
        let products = [];
        $('.product-item').each((i, el) => {
            const nombre = $(el).find('.product-item-link').text().trim();
            const precioText = $(el).find('.price').first().text().replace(/[^\d]/g, '');
            const precio = parseInt(precioText) || 0;
            if (nombre && precio > 0) {
                products.push({
                    nombre, marca: 'Cruz Verde', precio, tienda: 'Cruz Verde',
                    imagen: $(el).find('.product-image-photo').attr('src') || '',
                    url: $(el).find('.product-item-link').attr('href') || '#'
                });
            }
        });
        return products;
    } catch (e) { return []; }
}

async function scrapeAmazon() {
    console.log('[Amazon] Buscando...');
    try {
        const url = `http://api.scraperapi.com?api_key=${SCRAPER_API_KEY}&url=${encodeURIComponent('https://www.amazon.com/s?k=isdin+sunscreen')}`;
        const response = await axios.get(url);
        const $ = cheerio.load(response.data);
        let products = [];
        $('.s-result-item[data-component-type="s-search-result"]').each((i, el) => {
            const nombre = $(el).find('h2 a span').first().text().trim();
            const priceText = $(el).find('.a-price .a-offscreen').first().text().trim() || $(el).find('.a-price-whole').first().text().trim();
            if (nombre && priceText) {
                const cleanPrice = parseFloat(priceText.replace(/[^\d.]/g, ''));
                if (cleanPrice > 0) {
                    products.push({
                        nombre, marca: 'Amazon', precio: Math.round(cleanPrice * 4000), tienda: 'Amazon',
                        imagen: $(el).find('.s-image').attr('src') || '',
                        url: 'https://www.amazon.com' + $(el).find('h2 a').attr('href')
                    });
                }
            }
        });
        return products;
    } catch (e) { return []; }
}

function processAndGroup(products) {
    const groups = [];
    products.forEach(p => {
        const nName = p.nombre.toLowerCase().replace(/[^a-z0-9 ]/g, '');
        const words = nName.split(' ').filter(w => w.length > 3 && !['para','con','del','los','las','isdin','protector','solar'].includes(w));
        
        let match = groups.find(g => {
            if (words.length === 0) return false;
            let common = words.filter(w => new Set(g.keywords).has(w));
            return common.length >= 2; 
        });

        if (match) {
            match.items.push(p);
        } else {
            groups.push({ keywords: words, items: [p] });
        }
    });

    let final = [];
    groups.forEach((g, idx) => {
        const min = Math.min(...g.items.map(i => i.precio));
        const gid = `G${idx.toString().padStart(3, '0')}`;
        g.items.forEach(i => final.push({ ...i, es_mas_barato: i.precio === min, grupo_id: gid, en_grupo: g.items.length > 1 }));
    });
    return final;
}

async function run() {
    const m = await scrapeMedipiel(); console.log(`Medipiel: ${m.length}`);
    const b = await scrapeBellaPiel(); console.log(`Bella Piel: ${b.length}`);
    const c = await scrapeCruzVerde(); console.log(`Cruz Verde: ${c.length}`);
    const a = await scrapeAmazon(); console.log(`Amazon: ${a.length}`);
    
    const all = processAndGroup([...m, ...b, ...c, ...a]);
    fs.writeFileSync(path.join(__dirname, '../data/precios.json'), JSON.stringify({ ultima_actualizacion: new Date().toISOString(), productos: all }, null, 2));
    console.log('Finalizado.');
}

run();
