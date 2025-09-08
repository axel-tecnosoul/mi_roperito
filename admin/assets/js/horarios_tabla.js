document.addEventListener('click', function(e){
  if (e.target.classList.contains('add-block')) {
    var day = e.target.getAttribute('data-day');
    var tbody = document.querySelector('#tablaHorarios tbody');
    var rows = tbody.querySelectorAll('tr[data-day="' + day + '"]');
    var lastRow = rows[rows.length - 1];
    var tr = document.createElement('tr');
    tr.setAttribute('data-day', day);
    tr.innerHTML =
      '<td></td>' +
      '<td><input type="time" step="300" name="horarios[' + day + '][inicio][]" class="form-control"></td>' +
      '<td><input type="time" step="300" name="horarios[' + day + '][fin][]" class="form-control"></td>' +
      '<td><button type="button" class="btn btn-danger btn-sm remove-block">X</button></td>';
    lastRow.parentNode.insertBefore(tr, lastRow.nextSibling);
  }
  if (e.target.classList.contains('remove-block')) {
    var row = e.target.closest('tr');
    if (row) {
      row.remove();
    }
  }
});

