const axios = require('axios');

const tests = [
    'https://www.drogueriascolsubsidio.com/api/catalog_system/pub/products/search?_from=0&_to=5',
    'https://www.locatelcolombia.com/api/catalog_system/pub/products/search?_from=0&_to=5',
    'https://www.carrefour.com.co/api/catalog_system/pub/products/search?_from=0&_to=5',
    'https://exito.com/api/catalog_system/pub/products/search?_from=0&_to=5',
    'https://www.linio.com.co/api/catalog_system/pub/products/search?_from=0&_to=5',
];

async function run() {
    for (const u of tests) {
        try {
            const r = await axios.get(u, { timeout: 8000, httpsAgent: new (require('https').Agent)({ rejectUnauthorized: false }) });
            console.log(`✓ OK ${u.split('/')[2]}: ${r.data.length} items`);
        } catch (e) {
            console.log(`✗ FAIL ${u.split('/')[2]}: ${e.response?.status || e.message.substring(0,40)}`);
        }
    }
}
run();
