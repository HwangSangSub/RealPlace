mapboxgl.accessToken = 'pk.eyJ1IjoiZGRkc25zIiwiYSI6ImNrMXZxcmc3ZzB3bXozY284MGVmZnVuZjMifQ.f6jJWejBgnQNbz_RQ-f72g';

$(document).ready(function() {

  var map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/dddsns/ck24223ku539j1cnt05cfcqj6',
    center: {
      lat: 37.55231594,
      lng: 126.92191547
    },
    zoom: 14,
    preserveDrawingBuffer: true
  });

  $('button').click(function() {
    var img  = map.getCanvas().toDataURL();
    var width = $('#screenshotPlaceholder').width()
    var height = $('#screenshotPlaceholder').height()
    var imgHTML = `<img src="${img}", width=${width}, height = ${height}/>`
    $('#screenshotPlaceholder').empty();
    $('#screenshotPlaceholder').append(imgHTML);
  }); 
});
  
