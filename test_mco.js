const axios = require('axios');

async function testML() {
    try {
        const url = 'https://api.mercadolibre.com/sites/MCO/search?q=isdin+fusion+water&limit=5';
        const res = await axios.get(url);
        console.log(res.data.results.map(p => ({
            nombre: p.title,
            precio: p.price,
            tienda: 'MercadoLibre',
            imagen: p.thumbnail,
            url: p.permalink
        })));
    } catch (e) {
        console.error(e.message);
    }
}
testML();
