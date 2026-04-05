const axios = require('axios');
const cheerio = require('cheerio');

async function testAmazon() {
    const scraperApiKey = '27d7f5d9fbbe4fa88c93b2434c195571';
    const targetUrl = 'https://www.amazon.com/s?k=isdin+protector+solar';
    const url = `http://api.scraperapi.com?api_key=${scraperApiKey}&url=${encodeURIComponent(targetUrl)}`;
    
    console.log('Fetching Amazon via ScraperAPI...');
    try {
        const response = await axios.get(url);
        const $ = cheerio.load(response.data);
        
        const results = $('.s-result-item[data-component-type="s-search-result"]');
        console.log('Number of items found:', results.length);
        
        results.each((i, el) => {
            if (i > 2) return;
            const name = $(el).find('h2 a span').text().trim();
            const price = $(el).find('.a-price .a-offscreen').first().text().trim();
            const img = $(el).find('.s-image').attr('src');
            console.log(`- [${i}] Name: ${name} | Price: ${price} | Img: ${img}`);
        });
    } catch (e) {
        console.error('Error fetching Amazon:', e.message);
    }
}

testAmazon();
