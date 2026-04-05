const axios = require('axios');
const scraperApiKey = '27d7f5d9fbbe4fa88c93b2434c195571';
const targetUrl = 'https://www.cruzverde.com.co/api/catalog_system/pub/products/search?_from=0&_to=5&ft=protector+solar';
const url = `http://api.scraperapi.com?api_key=${scraperApiKey}&render=true&url=${encodeURIComponent(targetUrl)}`;

axios.get(url).then(r => {
    console.log('Status:', r.status);
    console.log('Data Type:', typeof r.data);
    if (Array.isArray(r.data)) {
        console.log('Items found:', r.data.length);
        console.log('First item:', r.data[0].productName);
    } else {
        console.log('Data is not an array. Body snippet:', JSON.stringify(r.data).substring(0, 200));
    }
}).catch(e => console.log('Error:', e.message));
