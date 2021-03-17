$(document).ready(function(){
	 var URL = window.URL || window.webkitURL;

    var input = document.querySelector('#input');
    var preview = document.querySelector('#preview');
    
    // When the file input changes, create a object URL around the file.
    input.addEventListener('change', function () {
        preview.src = URL.createObjectURL(this.files[0]);
    });
    
    // When the image loads, release object URL
    preview.addEventListener('load', function () {
        URL.revokeObjectURL(this.src);
    });

    el = document.getElementById('branches');
    if (el !== null) {
    	el.addEventListener('change', refresh_form);
      if (document.getElementById('not_update') == null) {
        refresh_form();
      }
    }
  function refresh_form() {
  var xhttp;
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("txthint").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "objects_refresh.php?branchid="+el.value, true);
  xhttp.send();
}
});