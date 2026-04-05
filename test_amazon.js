const axios = require('axios');
const cheerio = require('cheerio');
const scraperApiKey = '27d7f5d9fbbe4fa88c93b2434c195571';
const targetUrl = 'https://www.amazon.com/s?k=isdin+protector+solar';
const url = `http://api.scraperapi.com?api_key=${scraperApiKey}&url=${encodeURIComponent(targetUrl)}`;
axios.get(url).then(r => {
    const $ = cheerio.load(r.data);
    let n = $('.s-result-item[data-component-type="s-search-result"]').length;
    console.log('Result items length:', n);
    if(n === 0) console.log($('title').text());
}).catch(e => console.log(e.message));
