document.addEventListener('click', function(e){
  if(e.target.classList.contains('add-block')){
    var day = e.target.getAttribute('data-day');
    var container = e.target.closest('.day-block').querySelector('.blocks');
    var div = document.createElement('div');
    div.className = 'block form-group row';
    div.innerHTML = '<div class="col-sm-5"><input type="time" step="300" name="horarios['+day+'][inicio][]" class="form-control"></div>' +
                    '<div class="col-sm-5"><input type="time" step="300" name="horarios['+day+'][fin][]" class="form-control"></div>' +
                    '<div class="col-sm-2"><button type="button" class="btn btn-danger btn-sm remove-block">X</button></div>';
    container.appendChild(div);
  }
  if(e.target.classList.contains('remove-block')){
    var block = e.target.closest('.block');
    if(block){
      block.remove();
    }
  }
});
