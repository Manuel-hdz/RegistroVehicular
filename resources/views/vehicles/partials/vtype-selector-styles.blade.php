@once
    @push('head')
        <style>
            .vtype-picker {
                position: relative;
            }
            .vtype-trigger {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                border: 1px solid #d1d5db;
                border-radius: 12px;
                background: #fff;
                padding: 10px 12px;
                text-align: left;
                cursor: pointer;
                box-shadow: 0 1px 2px rgba(0,0,0,.06);
                transition: all .2s ease;
            }
            .vtype-trigger:hover {
                border-color: #9ca3af;
                transform: translateY(-1px);
            }
            .vtype-trigger-copy small {
                display: block;
                color: #6b7280;
                font-size: 11px;
                margin-bottom: 4px;
            }
            .vtype-trigger-preview {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .vtype-trigger-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: #f9fafb;
                color: #374151;
            }
            .vtype-trigger-action {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                background: #eef2f7;
                color: #374151;
                font-size: 12px;
                font-weight: 700;
                padding: 6px 10px;
                white-space: nowrap;
            }
            .vtype-modal {
                position: fixed;
                inset: 0;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 16px;
                background: rgba(17, 24, 39, .52);
                backdrop-filter: blur(6px);
                z-index: 2100;
            }
            .vtype-modal[aria-hidden="false"] {
                display: flex;
            }
            .vtype-modal-card {
                width: min(920px, 100%);
                max-height: 85vh;
                overflow: auto;
                border-radius: 18px;
                background: #fff;
                border: 1px solid #d1d5db;
                box-shadow: 0 24px 60px rgba(0,0,0,.22);
            }
            .vtype-modal-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                padding: 16px 18px 12px;
                border-bottom: 1px solid #eef2f7;
            }
            .vtype-modal-head strong {
                display: block;
                font-size: 18px;
                color: #111827;
            }
            .vtype-modal-head p {
                margin: 6px 0 0;
                color: #6b7280;
                font-size: 13px;
            }
            .vtype-modal-close {
                border: none;
                background: transparent;
                font-size: 28px;
                line-height: 1;
                color: #6b7280;
                cursor: pointer;
            }
            .vtype-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
                gap: 10px;
                padding: 18px;
            }
            .vtype-option {
                width: 100%;
                border: 1px solid #d1d5db;
                border-radius: 12px;
                background: #fff;
                color: #1f2937;
                padding: 12px;
                text-align: left;
                cursor: pointer;
                transition: all .2s ease;
                box-shadow: 0 1px 2px rgba(0,0,0,.06);
            }
            .vtype-option:hover {
                border-color: #9ca3af;
                transform: translateY(-1px);
            }
            .vtype-option.is-selected {
                border-color: var(--green);
                background: #ecfdf5;
                box-shadow: 0 0 0 1px rgba(0,104,71,.16), 0 6px 16px rgba(0,0,0,.07);
            }
            .vtype-option.is-selected .vtype-option-icon {
                background: var(--green);
                color: #fff;
            }
            .vtype-option-preview {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .vtype-option-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: #f9fafb;
                color: #374151;
            }
            .vtype-option-label {
                display: block;
                font-weight: 700;
                font-size: 13px;
                line-height: 1.2;
            }
            .vtype-option-hint {
                display: block;
                margin-top: 2px;
                color: #6b7280;
                font-size: 11px;
                line-height: 1.25;
            }
            .vtype-modal-actions {
                display: flex;
                justify-content: flex-end;
                padding: 0 18px 18px;
            }
            @media (max-width: 576px){
                .vtype-grid {
                    grid-template-columns: 1fr;
                }
                .vtype-trigger {
                    align-items: flex-start;
                    flex-direction: column;
                }
            }
        </style>
    @endpush
@endonce
