@once
    @push('head')
        <style>
            .vtype-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
                gap: 10px;
            }
            .vtype-item {
                position: relative;
            }
            .vtype-radio {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }
            .vtype-option {
                display: flex;
                align-items: center;
                gap: 10px;
                width: 100%;
                border: 1px solid #d1d5db;
                border-radius: 12px;
                background: #fff;
                color: #1f2937;
                padding: 11px 12px;
                text-align: left;
                cursor: pointer;
                transition: all .2s ease;
                box-shadow: 0 1px 2px rgba(0,0,0,.06);
            }
            .vtype-option:hover {
                border-color: #9ca3af;
                transform: translateY(-1px);
            }
            .vtype-option-icon {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                background: #f9fafb;
                color: #374151;
            }
            .vtype-option-label {
                display: block;
                font-weight: 700;
                font-size: 14px;
                line-height: 1.2;
            }
            .vtype-option-hint {
                display: block;
                margin-top: 2px;
                color: #6b7280;
                font-size: 12px;
                line-height: 1.25;
            }
            .vtype-radio:checked + .vtype-option {
                border-color: var(--green);
                background: #ecfdf5;
                box-shadow: 0 0 0 1px rgba(0,104,71,.16), 0 6px 16px rgba(0,0,0,.07);
            }
            .vtype-radio:checked + .vtype-option .vtype-option-icon {
                background: var(--green);
                color: #fff;
            }
            @media (max-width: 576px){
                .vtype-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush
@endonce
