@push('scripts')
    <script>
        (function () {
            function syncLinkedFields(select, option) {
                var nameTarget = select.dataset.nameTarget ? document.querySelector(select.dataset.nameTarget) : null;
                var employeeTarget = select.dataset.employeeTarget ? document.querySelector(select.dataset.employeeTarget) : null;

                if (nameTarget) {
                    nameTarget.value = option ? (option.dataset.personnelName || '') : '';
                }

                if (employeeTarget) {
                    employeeTarget.value = option ? (option.dataset.employeeNumber || '') : '';
                }
            }

            function makeSearchable(select) {
                if (!select || select.dataset.searchableReady === '1') {
                    return;
                }

                select.dataset.searchableReady = '1';

                var wrapper = document.createElement('div');
                wrapper.style.position = 'relative';
                wrapper.className = 'searchable-wrapper';

                var input = document.createElement('input');
                input.type = 'text';
                input.placeholder = 'Buscar personal...';
                input.className = 'searchable-input';
                input.autocomplete = 'off';

                var list = document.createElement('ul');
                list.className = 'searchable-list';
                list.style.position = 'absolute';
                list.style.left = '0';
                list.style.right = '0';
                list.style.top = '100%';
                list.style.zIndex = '10';
                list.style.maxHeight = '220px';
                list.style.overflowY = 'auto';
                list.style.margin = '4px 0 0';
                list.style.padding = '0';
                list.style.listStyle = 'none';
                list.style.background = '#fff';
                list.style.border = '1px solid #cbd5e1';
                list.style.borderRadius = '12px';
                list.style.boxShadow = '0 12px 24px rgba(15, 23, 42, .12)';
                list.hidden = true;

                var originalOptions = Array.from(select.options);

                function applyOption(option) {
                    if (!option) {
                        return;
                    }

                    select.value = option.value;
                    input.value = option.textContent.trim();
                    list.hidden = true;
                    syncLinkedFields(select, option);
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }

                function render(filter) {
                    list.innerHTML = '';
                    var term = (filter || '').toLowerCase().trim();

                    originalOptions.forEach(function (option, index) {
                        if (index === 0) {
                            return;
                        }

                        var text = option.textContent.trim();
                        if (term && !text.toLowerCase().includes(term)) {
                            return;
                        }

                        var item = document.createElement('li');
                        item.textContent = text;
                        item.dataset.value = option.value;
                        item.style.padding = '10px 12px';
                        item.style.cursor = 'pointer';
                        item.addEventListener('mouseenter', function () {
                            item.style.background = '#eff6ff';
                        });
                        item.addEventListener('mouseleave', function () {
                            item.style.background = '#fff';
                        });
                        item.addEventListener('mousedown', function (event) {
                            event.preventDefault();
                            applyOption(option);
                        });
                        list.appendChild(item);
                    });

                    list.hidden = list.children.length === 0;
                }

                input.addEventListener('focus', function () {
                    input.select();
                    render(input.value);
                });

                input.addEventListener('input', function () {
                    render(this.value);
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();

                    var firstVisible = list.querySelector('li');
                    if (!firstVisible) {
                        return;
                    }

                    var selectedOption = originalOptions.find(function (option) {
                        return option.value === firstVisible.dataset.value;
                    });

                    applyOption(selectedOption);
                });

                document.addEventListener('click', function (event) {
                    if (!wrapper.contains(event.target)) {
                        list.hidden = true;
                    }
                });

                select.parentNode.insertBefore(wrapper, select);
                wrapper.appendChild(input);
                wrapper.appendChild(list);
                select.style.display = 'none';

                var selectedOption = select.selectedOptions[0];
                if (selectedOption && selectedOption.value) {
                    input.value = selectedOption.textContent.trim();
                    syncLinkedFields(select, selectedOption);
                } else {
                    syncLinkedFields(select, null);
                }
            }

            document.querySelectorAll('.searchable-select').forEach(makeSearchable);
        })();
    </script>
@endpush
