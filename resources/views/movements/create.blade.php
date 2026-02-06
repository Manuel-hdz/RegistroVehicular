@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Registrar Salida</h2>
    <form method="POST" action="{{ route('movements.store') }}" class="grid grid-3" onsubmit="return confirm('Antes de guardar, por favor revisa que los datos sean correctos (vehículo, conductor, odómetro, combustible y fecha/hora). ¿Deseas continuar?')">
        @csrf
        <div>
            <label for="vehicle_id">Vehículo</label>
            <select id="vehicle_id" class="searchable-select" name="vehicle_id" required>
                <option value=""></option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" data-odometer="{{ $lastOdometers[$v->id] ?? '' }}" @selected(old('vehicle_id')==$v->id)>
                        {{ $v->identifier ?: $v->plate }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="driver_id">Conductor</label>
            <select id="driver_id" class="searchable-select" name="driver_id" required>
                <option value=""></option>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" @selected(old('driver_id')==$d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="odometer_out">Odómetro salida (km)</label>
            <input id="odometer_out" type="number" name="odometer_out" value="{{ old('odometer_out') }}" min="0" required>
        </div>
        <div>
            <label for="fuel_out_base">Combustible salida</label>
            <div class="row" style="gap:8px;">
                <select id="fuel_out_base" name="fuel_out_base" required>
                    <option value="reserve" @selected(old('fuel_out_base')==='reserve')>Reserva</option>
                    <option value="1/4" @selected(old('fuel_out_base')==='1/4')>1/4</option>
                    <option value="1/2" @selected(old('fuel_out_base','1/2')==='1/2')>1/2</option>
                    <option value="3/4" @selected(old('fuel_out_base')==='3/4')>3/4</option>
                    <option value="1" @selected(old('fuel_out_base')==='1')>Lleno</option>
                </select>
                <select name="fuel_out_dir" required>
                    <option value="below" @selected(old('fuel_out_dir')==='below')>Abajo de</option>
                    <option value="exact" @selected(old('fuel_out_dir','exact')==='exact')>Exacto</option>
                    <option value="above" @selected(old('fuel_out_dir')==='above')>Arriba de</option>
                </select>
            </div>
            <small style="color:#555;">Se almacenará como porcentaje aproximado.</small>
        </div>
        <div>
            <label for="departed_at">Fecha/Hora salida</label>
            <input id="departed_at" type="datetime-local" name="departed_at" value="{{ old('departed_at', now()->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div>
            <label for="destination">Destino</label>
            <input id="destination" type="text" name="destination" value="{{ old('destination') }}">
        </div>
        <div class="grid" style="grid-column: 1/-1;">
            <label for="notes_out">Notas</label>
            <textarea id="notes_out" name="notes_out">{{ old('notes_out') }}</textarea>
        </div>
        <div style="grid-column: 1/-1;" class="row actions-stick">
            <a class="btn btn-secondary" href="{{ route('movements.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar Salida</button>
        </div>
    </form>
</div>
<script>
    (function(){
        var input = document.querySelector('input[name="departed_at"]');
        if(input){
            var d = new Date();
            var yyyy = d.getFullYear();
            var mm = String(d.getMonth()+1).padStart(2,'0');
            var dd = String(d.getDate()).padStart(2,'0');
            input.value = yyyy + '-' + mm + '-' + dd + 'T08:00';
        }

        function makeSearchable(select){
            if(!select) return;
            var wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.className = 'searchable-wrapper';

            var input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Buscar...';
            input.className = 'searchable-input';
            input.autocomplete = 'off';

            var list = document.createElement('ul');
            list.className = 'searchable-list';
            list.style.position = 'absolute';
            list.style.left = '0';
            list.style.right = '0';
            list.style.top = '100%';
            list.style.zIndex = '10';
            list.style.maxHeight = '160px';
            list.style.overflowY = 'auto';
            list.style.margin = '4px 0 0';
            list.style.padding = '0';
            list.style.listStyle = 'none';
            list.style.background = '#fff';
            list.style.border = '1px solid #ccc';
            list.style.boxShadow = '0 2px 6px rgba(0,0,0,.1)';
            list.hidden = true;

            var originalOptions = Array.from(select.options);

            function render(filter){
                list.innerHTML = '';
                var term = (filter || '').toLowerCase();
                originalOptions.forEach(function(opt, idx){
                    if(idx===0) return; // saltar "Seleccione…"
                    var text = opt.textContent;
                    if(term && !text.toLowerCase().includes(term)) return;
                    var li = document.createElement('li');
                    li.textContent = text;
                    li.dataset.value = opt.value;
                    li.style.padding = '6px 8px';
                    li.style.cursor = 'pointer';
                    li.addEventListener('mousedown', function(e){
                        e.preventDefault();
                        select.value = opt.value;
                        input.value = text;
                        list.hidden = true;
                    });
                    list.appendChild(li);
                });
                list.hidden = list.children.length === 0;
            }

            input.addEventListener('focus', function(){
                input.select();
                render(input.value);
            });

            input.addEventListener('input', function(){
                render(this.value);
            });

            document.addEventListener('click', function(e){
                if(!wrapper.contains(e.target)){
                    list.hidden = true;
                }
            });

            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(input);
            wrapper.appendChild(list);
            select.style.display = 'none';

            // mostrar valor inicial si ya hay
            var selectedOpt = select.selectedOptions[0];
            if(selectedOpt){
                input.value = selectedOpt.textContent;
            }
        }

        function validateSearchables(form){
            form.addEventListener('submit', function(e){
                var hasError = false;
                document.querySelectorAll('.searchable-wrapper').forEach(function(w){
                    var input = w.querySelector('.searchable-input');
                    var select = w.nextElementSibling;
                    if(!input || !select || select.tagName !== 'SELECT') return;
                    var options = Array.from(select.options).slice(1); // sin "Seleccione…"
                    var term = input.value.trim().toLowerCase();
                    var match = options.some(function(o){
                        var text = o.textContent.toLowerCase();
                        return text.includes(term) || o.value === select.value;
                    });
                    var msg = w.querySelector('.searchable-error');
                    if(!msg){
                        msg = document.createElement('small');
                        msg.className = 'searchable-error';
                        msg.style.color = '#d33';
                        msg.style.display = 'block';
                        msg.style.marginTop = '4px';
                        w.appendChild(msg);
                    }
                    if(!match){
                        hasError = true;
                        msg.textContent = 'Los datos de vehículo o conductor no se encontraron en la lista.';
                    }else{
                        msg.textContent = '';
                    }
                });
                if(hasError){
                    e.preventDefault();
                }
            });
        }

        document.querySelectorAll('.searchable-select').forEach(makeSearchable);
        var form = document.querySelector('form');
        if(form) validateSearchables(form);

        function toggleDirOnReserve(baseSelect, dirSelect){
            if(!baseSelect || !dirSelect) return;
            var sync = function(){
                var isReserve = baseSelect.value === 'reserve';
                dirSelect.disabled = isReserve;
                if(isReserve){
                    dirSelect.value = 'exact';
                }
            };
            baseSelect.addEventListener('change', sync);
            sync();
        }

        toggleDirOnReserve(
            document.querySelector('select[name=\"fuel_out_base\"]'),
            document.querySelector('select[name=\"fuel_out_dir\"]')
        );

        // Autocompletar odómetro con el último registrado del vehículo (editable)
        (function(){
            var selectVehicle = document.querySelector('select[name=\"vehicle_id\"]');
            var odometerInput = document.querySelector('input[name=\"odometer_out\"]');
            if(!selectVehicle || !odometerInput) return;
            selectVehicle.addEventListener('change', function(){
                var option = selectVehicle.selectedOptions[0];
                if(!option) return;
                var last = option.dataset.odometer;
                if(last){
                    odometerInput.value = last;
                }
            });
            // inicializar si ya viene preseleccionado
            if(selectVehicle.value){
                selectVehicle.dispatchEvent(new Event('change'));
            }
        })();
    })();
</script>
@endsection
