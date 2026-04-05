const axios = require('axios');
const cheerio = require('cheerio');
const SCRAPER_API_KEY = '27d7f5d9fbbe4fa88c93b2434c195571';
const url = `http://api.scraperapi.com?api_key=${SCRAPER_API_KEY}&url=${encodeURIComponent('https://www.amazon.com/s?k=isdin+sunscreen')}`;

axios.get(url).then(res => {
    const $ = cheerio.load(res.data);
    const items = $('.s-result-item[data-component-type="s-search-result"]');
    console.log('Items found:', items.length);
    items.each((i, el) => {
        if (i < 3) console.log($(el).find('h2 a span').text().trim());
    });
}).catch(e => console.log(e.message));
