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
    $selectedOption = collect($options)->firstWhere('value', $selected) ?? $options[0];
    $modalId = $idPrefix . '_modal';
@endphp

<div class="vtype-picker" data-vtype-picker>
    <input type="hidden" name="vtype" value="{{ $selectedOption['value'] }}" data-vtype-value>

    <button class="vtype-trigger" type="button" data-vtype-open aria-haspopup="dialog" aria-controls="{{ $modalId }}">
        <span class="vtype-trigger-copy">
            <small>Icono seleccionado</small>
            <span class="vtype-trigger-preview" data-vtype-summary>
                @include('vehicles.partials.vtype-icon', [
                    'type' => $selectedOption['value'],
                    'size' => 26,
                    'class' => 'vtype-trigger-icon',
                ])
                <span>
                    <span class="vtype-option-label">{{ $selectedOption['label'] }}</span>
                    <small class="vtype-option-hint">{{ $selectedOption['hint'] }}</small>
                </span>
            </span>
        </span>
        <span class="vtype-trigger-action">Cambiar</span>
    </button>

    <div class="vtype-modal" id="{{ $modalId }}" aria-hidden="true">
        <div class="vtype-modal-card" role="dialog" aria-modal="true" aria-labelledby="{{ $idPrefix }}_title">
            <div class="vtype-modal-head">
                <div>
                    <strong id="{{ $idPrefix }}_title">Seleccionar icono</strong>
                    <p>Elige el tipo de unidad sin saturar el formulario.</p>
                </div>
                <button class="vtype-modal-close" type="button" aria-label="Cerrar" data-vtype-close>&times;</button>
            </div>

            <div class="vtype-grid">
                @foreach($options as $option)
                    <button
                        class="vtype-option {{ $selectedOption['value'] === $option['value'] ? 'is-selected' : '' }}"
                        type="button"
                        data-vtype-choice
                        data-value="{{ $option['value'] }}"
                    >
                        <span class="vtype-option-preview">
                            @include('vehicles.partials.vtype-icon', [
                                'type' => $option['value'],
                                'size' => 24,
                                'class' => 'vtype-option-icon',
                            ])
                            <span>
                                <span class="vtype-option-label">{{ $option['label'] }}</span>
                                <small class="vtype-option-hint">{{ $option['hint'] }}</small>
                            </span>
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="vtype-modal-actions">
                <button class="btn btn-secondary btn-sm" type="button" data-vtype-close>Cerrar</button>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                function initPicker(picker) {
                    const hiddenInput = picker.querySelector('[data-vtype-value]');
                    const summary = picker.querySelector('[data-vtype-summary]');
                    const modal = picker.querySelector('.vtype-modal');
                    const openButton = picker.querySelector('[data-vtype-open]');
                    const closeButtons = picker.querySelectorAll('[data-vtype-close]');
                    const choices = picker.querySelectorAll('[data-vtype-choice]');

                    function openModal() {
                        if (!modal) return;
                        modal.setAttribute('aria-hidden', 'false');
                    }

                    function closeModal() {
                        if (!modal) return;
                        modal.setAttribute('aria-hidden', 'true');
                    }

                    function updateSelection(choice) {
                        const value = choice.dataset.value;
                        const preview = choice.querySelector('.vtype-option-preview');
                        if (!hiddenInput || !summary || !preview || !value) return;

                        hiddenInput.value = value;
                        summary.innerHTML = preview.innerHTML;
                        choices.forEach(function (item) {
                            item.classList.toggle('is-selected', item === choice);
                        });
                        closeModal();
                    }

                    if (openButton) {
                        openButton.addEventListener('click', openModal);
                    }

                    closeButtons.forEach(function (button) {
                        button.addEventListener('click', closeModal);
                    });

                    if (modal) {
                        modal.addEventListener('click', function (event) {
                            if (event.target === modal) {
                                closeModal();
                            }
                        });
                    }

                    choices.forEach(function (choice) {
                        choice.addEventListener('click', function () {
                            updateSelection(choice);
                        });
                    });
                }

                document.querySelectorAll('[data-vtype-picker]').forEach(initPicker);

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') return;

                    document.querySelectorAll('.vtype-modal[aria-hidden="false"]').forEach(function (modal) {
                        modal.setAttribute('aria-hidden', 'true');
                    });
                });
            })();
        </script>
    @endpush
@endonce
