function updatePercentsTextPosition(PercentsTextPosition){
  $('[name="left_pos"]').val(PercentsTextPosition.left).attr("value",PercentsTextPosition.left);
  $('[name="top_pos"]').val(PercentsTextPosition.top).attr("value",PercentsTextPosition.top);

  var textarea = document.getElementById('text');
  var text = textarea.value;
  var newOptions = JSON.parse(text);
  newOptions.container = container;
  newOptions.textPosition.top = PercentsTextPosition.top;
  newOptions.textPosition.left = PercentsTextPosition.left;
  $('#text').val(JSON.stringify(newOptions));
}


var container = document.getElementById('container');

var cb = new window.CanvasBanner(options);

var textarea = document.getElementById('text')
var text = document.getElementById('text').value = (JSON.stringify(options, null, 2));

var btn = document.getElementById('btn');
btn.onclick = function () {
  var textarea = document.getElementById('text');
  var text = textarea.value;
  var newOptions = JSON.parse(text);
  newOptions.container = container;
  cb.reinit(newOptions);
};

$('[name="font_size"]').on("keyup", function (event) {
  var textarea = document.getElementById('text');
  var text = textarea.value;
  var newOptions = JSON.parse(text);
  newOptions.container = container;
  newOptions.fontProperties.fontSize = $(event.target).val();
  cb.reinit(newOptions);
});

//addText.add(options);

var textarea2 = document.getElementById('text2');
  textarea2.onkeyup = function() {
    var text2 = textarea2.value;
    var textarea = document.getElementById('text');
    var text = textarea.value;
    var newOptions = JSON.parse(text);
    newOptions.container = container;
    newOptions.text = text2;
    cb.drawText(text2);
    //addText.add(newOptions);
  }
