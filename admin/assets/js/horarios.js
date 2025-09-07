document.addEventListener('click', function(e){
  if (e.target.classList.contains('add-block')) {
    var day = e.target.getAttribute('data-day');
    var container = e.target.closest('.day-block').querySelector('.blocks');
    var count = container.querySelectorAll('.block').length + 1;
    var div = document.createElement('div');
    div.className = 'block form-group row';
    div.innerHTML =
      '<span class="block-label col-12">Bloque ' + count + '</span>' +
      '<div class="col-sm-5"><label>Inicio</label><input type="time" step="300" name="horarios[' + day + '][inicio][]" class="form-control"></div>' +
      '<div class="col-sm-5"><label>Fin</label><input type="time" step="300" name="horarios[' + day + '][fin][]" class="form-control"></div>' +
      '<div class="col-sm-2 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-block">X</button></div>';
    container.appendChild(div);
  }
  if (e.target.classList.contains('remove-block')) {
    var block = e.target.closest('.block');
    if (block) {
      var container = block.parentElement;
      block.remove();
      container.querySelectorAll('.block-label').forEach(function(label, idx){
        label.textContent = 'Bloque ' + (idx + 1);
      });
    }
  }
});
