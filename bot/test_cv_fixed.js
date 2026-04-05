const axios = require('axios');
const cheerio = require('cheerio');

async function testCruzVerde() {
    const scraperApiKey = '27d7f5d9fbbe4fa88c93b2434c195571';
    // Using the search page instead of the API
    const targetUrl = 'https://www.cruzverde.com.co/search?q=isdin&lang=es_CO';
    const url = `http://api.scraperapi.com?api_key=${scraperApiKey}&url=${encodeURIComponent(targetUrl)}`;
    
    console.log('Fetching Cruz Verde via ScraperAPI (Web Search)...');
    try {
        const response = await axios.get(url);
        const $ = cheerio.load(response.data);
        
        // Let's try to find products
        const items = $('.product-item');
        console.log('Number of items found:', items.length);
        
        items.each((i, el) => {
            if (i > 3) return;
            const name = $(el).find('.product-item-link').text().trim();
            const price = $(el).find('.price').text().trim();
            const img = $(el).find('.product-image-photo').attr('src');
            console.log(`- [${i}] Name: ${name} | Price: ${price} | Img: ${img}`);
        });
        
        if (items.length === 0) {
            console.log('No elements with .product-item found. Page Title:', $('title').text());
            // console.log('HTML snippet:', response.data.substring(0, 1000));
        }
    } catch (e) {
        console.error('Error fetching Cruz Verde:', e.message);
    }
}

testCruzVerde();
