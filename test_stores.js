const axios = require('axios');

async function testCruzVerde() {
    console.log("Testing Cruz Verde...");
    try {
        const url = 'https://www.cruzverde.com.co/api/catalog_system/pub/products/search?_from=0&_to=5';
        const res = await axios.get(url, { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' } });
        console.log("Cruz Verde OK:", res.status, res.data.length, "items.");
    } catch (e) {
        console.log("Cruz Verde Error:", e.message, e.response?.status);
    }
}

async function testAmazon() {
    console.log("Testing Amazon...");
    try {
        const url = 'https://www.amazon.com/s?k=dermocosm%C3%A9tico';
        const res = await axios.get(url, { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36' } });
        console.log("Amazon OK:", res.status);
    } catch (e) {
        console.log("Amazon Error:", e.message, e.response?.status);
    }
}

async function run() {
    await testCruzVerde();
    await testAmazon();
}
run();
