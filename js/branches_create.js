$(document).ready(function(){

  el = document.getElementById('branches');
  el.addEventListener('change', refresh_multipick);

  function refresh_multipick() {
  var xhttp;
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      var serviceSelect = $('#services');
    serviceSelect.selectpicker('deselectAll');
    serviceSelect.find('option').remove();
    serviceSelect.find('li').remove();
    serviceSelect.selectpicker('refresh');
    var opt = this.responseText.split("?");
    opt[0].split(",").forEach((element) => {
      var simplePar = element.split(".");
      serviceSelect.append('<option value="'+simplePar[0]+'">'+simplePar[1]+'</option>');
      serviceSelect.selectpicker('refresh');
    });
    var districtSelect = $('#districts');
    districtSelect.selectpicker('deselectAll');
    districtSelect.find('option').remove();
    districtSelect.find('li').remove();
    districtSelect.selectpicker('refresh');
    opt[1].split(",").forEach((element) => {
      var simplePar = element.split(".");
      districtSelect.append('<option value="'+simplePar[0]+'">'+simplePar[1]+'</option>');
      districtSelect.selectpicker('refresh');
    });

    }
  };
  xhttp.open("GET", "branches_refresh.php?branchid="+el.value, true);
  xhttp.send();
}

});