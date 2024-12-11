var page = require('webpage').create();
page.open('http://places.gachita.co.kr/contents/map_img/contents/map_img_save.php?idx=309', function(status) {
  console.log("Status: " + status);
  if(status === "success") {
    page.render('google.jpg');
  }
  phantom.exit();
});