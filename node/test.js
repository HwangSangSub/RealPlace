
var conIdx  = process.argv.slice(2);

var baseUrl = "http://places.gachita.co.kr/contents/map_img/contents/map_img_save.php?idx=";

let url = baseUrl.concat(conIdx[0]);

const captureWebsite = require('capture-website');

(async () => {
//http://places.gachita.co.kr/contents/map_img/contents/map_img_save.php?idx=340

	await captureWebsite.file(url, './data/screenshot.png'

  ,{
		beforeScreenshot: async (page, browser) => {
			// await checkSomething();
			// await page.click('#btnSave');
			await page.waitForSelector('#capture');

     //await page.waitForNavigation()
    }
		}



  );
})();
