$(document).ready(function(){
  var altezzaB = $(window).innerHeight();
  $("#wrap-folder-id").css("height", altezzaB);
  $('select').material_select();

  $('[name="watermark_text"]').trigger("click");
})
