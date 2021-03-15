	$(document).ready(function () {
    if (findGetParameter("isEdit") == 0) {
      $counter = document.getElementById("inputContainer").childElementCount - 6;
    }
    else {
      $counter = document.getElementById("inputContainer").childElementCount - 4;
    }

  $("#addRec").click(function() {
    var $container =  $("#inputContainer");
    $container.append('<div class = "form-group"><div class="input-group"><input type="text" class="form-control" name = "rec' + $counter + '" placeholder = "Реквизит ' + $counter + ' "><span class="input-group-btn"><button id = "del' + $counter + '" type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button></span></div></div>');
    var button = document.getElementById('del' + $counter);
    button.onclick = function() {
      $(this).parent().parent().parent().remove();
    };
    $counter++;
    });

  $(".btn-danger").click(function() {
    $(this).parent().parent().parent().remove();
    });

});

function deleteButton() {
  $(this).parent().parent().parent().remove();
}

function showWarning() {
  // Get the checkbox
  var checkBox = document.getElementById("isHuman");
  // Get the output text
  var text = document.getElementById("isHumanText");

  // If the checkbox is checked, display the output text
  if (checkBox.checked == true){
    text.style.display = "block";
  } else {
    text.style.display = "none";
  }
}

function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    var items = location.search.substr(1).split("&");
    for (var index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
    }
    return result;
}