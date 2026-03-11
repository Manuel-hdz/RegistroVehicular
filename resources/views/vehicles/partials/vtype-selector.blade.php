@props([
    'selected' => 'auto',
    'idPrefix' => 'vtype',
])

@php
    $options = [
        ['value' => 'auto', 'label' => 'Auto', 'hint' => 'Sedan o compacto'],
        ['value' => 'pickup', 'label' => 'Pickup', 'hint' => 'Caja trasera abierta'],
        ['value' => 'furgoneta', 'label' => 'Furgoneta', 'hint' => 'Van o unidad cerrada'],
        ['value' => 'camion', 'label' => 'Camion', 'hint' => 'Unidad de carga pesada'],
        ['value' => 'transporte_personal', 'label' => 'Transporte personal', 'hint' => 'Unidad para traslado de personal'],
        ['value' => 'remolcable', 'label' => 'Remolcable', 'hint' => 'Unidad tipo remolque o arrastre'],
        ['value' => 'equipo_pesado', 'label' => 'Equipo pesado', 'hint' => 'Retroexcavadora y similares'],
        ['value' => 'trompo', 'label' => 'Trompo', 'hint' => 'Camion revolvedor'],
    ];
@endphp

<div class="vtype-grid">
    @foreach($options as $option)
        @php($inputId = $idPrefix . '_' . $option['value'])
        <div class="vtype-item">
            <input
                class="vtype-radio"
                type="radio"
                name="vtype"
                id="{{ $inputId }}"
                value="{{ $option['value'] }}"
                {{ $selected === $option['value'] ? 'checked' : '' }}
            >
            <label class="vtype-option" for="{{ $inputId }}">
                @include('vehicles.partials.vtype-icon', [
                    'type' => $option['value'],
                    'size' => 26,
                    'class' => 'vtype-option-icon',
                ])
                <span>
                    <span class="vtype-option-label">{{ $option['label'] }}</span>
                    <small class="vtype-option-hint">{{ $option['hint'] }}</small>
                </span>
            </label>
        </div>
    @endforeach
</div>
