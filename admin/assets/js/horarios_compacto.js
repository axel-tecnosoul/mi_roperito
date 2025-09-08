document.addEventListener('click', function(e){
  if (e.target.classList.contains('add-block')) {
    var day = e.target.getAttribute('data-day');
    var container = e.target.closest('.day-block').querySelector('.blocks');
    var count = container.querySelectorAll('.block').length + 1;
    var div = document.createElement('div');
    div.className = 'block form-row align-items-end';
    div.innerHTML =
      '<span class="block-label col-12">Bloque ' + count + '</span>' +
      '<div class="col-4"><label class="sr-only">Inicio</label><input type="time" step="300" name="horarios[' + day + '][inicio][]" class="form-control" placeholder="Inicio"></div>' +
      '<div class="col-4"><label class="sr-only">Fin</label><input type="time" step="300" name="horarios[' + day + '][fin][]" class="form-control" placeholder="Fin"></div>' +
      '<div class="col-2"><button type="button" class="btn btn-danger btn-sm remove-block">X</button></div>';
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
