// Script to manage groups of days and time blocks
let groupCounter = 0;
const diasSemana = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];

function addGroup(){
  const container = document.getElementById('groups');
  const idx = groupCounter++;
  const row = document.createElement('div');
  row.className = 'group-row row mb-3';

  let options = '';
  for(let i=0;i<diasSemana.length;i++){
    options += `<option value="${i}">${diasSemana[i]}</option>`;
  }

  row.innerHTML = `
    <div class="group-block col-sm-11 border p-3" data-index="${idx}">
      <div class="mb-2">
        <label class="col-form-label mb-0">Días</label>
      </div>
      <div class="form-group">
        <select multiple class="dias-select" name="horarios[${idx}][dias][]">${options}</select>
      </div>
      <div class="blocks"></div>
      <button type="button" class="btn btn-secondary btn-sm add-block">Agregar bloque</button>
    </div>
    <div class="col-sm-1 d-flex align-items-center justify-content-center">
      <button type="button" class="btn btn-danger btn-sm remove-group">Eliminar grupo</button>
    </div>
  `;

  container.appendChild(row);
  const block = row.querySelector('.group-block');
  $(block).find('.dias-select').select2({width:'100%'});
  addBlock(block);
  updateDisabledDays();
}

function removeGroup(row){
  row.remove();
  updateDisabledDays();
}

function addBlock(group){
  const idx = group.dataset.index;
  const container = group.querySelector('.blocks');
  const count = container.querySelectorAll('.block').length + 1;
  const div = document.createElement('div');
  div.className = 'block form-group row';
  div.innerHTML = `
    <span class="block-label col-12">Bloque ${count}</span>
    <div class="col-sm-5">
      <label>Inicio</label>
      <input type="time" step="300" name="horarios[${idx}][inicio][]" class="form-control">
    </div>
    <div class="col-sm-5">
      <label>Fin</label>
      <input type="time" step="300" name="horarios[${idx}][fin][]" class="form-control">
    </div>
    <div class="col-sm-2 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-block">X</button></div>
  `;
  container.appendChild(div);
}

function removeBlock(block){
  const container = block.parentElement;
  block.remove();
  container.querySelectorAll('.block-label').forEach((label, i)=>{
    label.textContent = 'Bloque ' + (i+1);
  });
}

function updateDisabledDays(){
  const selects = document.querySelectorAll('.dias-select');
  const selected = [];
  selects.forEach(sel=>{
    Array.from(sel.selectedOptions).forEach(opt=>selected.push(opt.value));
  });
  selects.forEach(sel=>{
    Array.from(sel.options).forEach(opt=>{
      if(selected.includes(opt.value) && !Array.from(sel.selectedOptions).map(o=>o.value).includes(opt.value)){
        opt.disabled = true;
      }else{
        opt.disabled = false;
      }
    });
    $(sel).trigger('change.select2');
  });
}

document.addEventListener('click', function(e){
  if(e.target.id === 'add-group'){
    addGroup();
  }
  if(e.target.classList.contains('remove-group')){
    removeGroup(e.target.closest('.group-row'));
  }
  if(e.target.classList.contains('add-block')){
    addBlock(e.target.closest('.group-block'));
  }
  if(e.target.classList.contains('remove-block')){
    removeBlock(e.target.closest('.block'));
  }
});

document.addEventListener('change', function(e){
  if(e.target.classList.contains('dias-select')){
    updateDisabledDays();
  }
});

document.addEventListener('DOMContentLoaded', function(){
  groupCounter = document.querySelectorAll('#groups .group-block').length;
  if(groupCounter === 0){
    addGroup();
  }else{
    document.querySelectorAll('#groups .group-block').forEach(group=>{
      $(group).find('.dias-select').select2({width:'100%'});
    });
    updateDisabledDays();
  }
});
