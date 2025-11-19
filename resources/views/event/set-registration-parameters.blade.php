@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Establecer Parámetros de Inscripción</title>
@endsection

@section('subcontent')
    <h2 class="intro-y mt-10 text-lg font-medium">Establecer Parámetros de Inscripción para el Evento</h2>

    {{-- Mensajes de Alerta --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <x-base.alert class="mb-2 flex items-center" variant="danger">
            <x-base.lucide class="mr-2 h-6 w-6" icon="AlertCircle" />
            {{ session('error') }}
        </x-base.alert>
    @endif
    {{-- Fin Mensajes de Alerta --}}

    <div class="mt-5">
        <div class="box p-5">
            <h3 class="text-lg font-medium">Evento: {{ $event->name }}</h3>
            <p><strong>Descripción:</strong> {{ $event->description }}</p>

            <form action="{{ route('events.storeRegistrationParameters', $event->id) }}" method="POST">
                @csrf

                <h4 class="text-lg font-medium mt-5">Parámetros de Inscripción</h4>

                <div class="mt-3 overflow-x-auto">
                    {{-- TABLA PRINCIPAL DE PARÁMETROS --}}
                    <table class="table w-full table-bordered table-striped">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-darkmode-800">
                                <th class="whitespace-nowrap w-1">Activo</th>
                                <th class="whitespace-nowrap">Parámetro</th>
                                <th class="whitespace-nowrap w-20">Tipo</th>
                                <th class="whitespace-nowrap w-1">Orden</th>
                                <th class="whitespace-nowrap w-1 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="parameters-table-body">
                            @php
                                $userColumns = [
                                    'name' => 'Nombre',
                                    'lastname' => 'Apellido',
                                    'email' => 'Correo Electrónico',
                                    'type_document' => 'Tipo de Documento',
                                    'document_number' => 'Número de Documento',
                                    'phone' => 'Teléfono',
                                    'city_id' => 'Ciudad',
                                    'birth_date' => 'Fecha de Nacimiento'
                                ];
                                $defaultIndex = 1; // Contador para el orden por defecto
                            @endphp

                            {{-- 1. CAMPOS ESTÁTICOS DE LA TABLA USERS --}}
                            @foreach($userColumns as $column => $label)
                                @php
                                    // Utiliza los arrays pre-procesados del controlador
                                    $defaultOrder = $selectedFieldsOrder[$column] ?? $defaultIndex;
                                    $isChecked = in_array($column, $selectedFields);
                                    $defaultIndex++; // Incrementar para el siguiente campo estático
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" id="{{ $column }}" name="fields[]" value="{{ $column }}" class="form-check-input"
                                            @if($isChecked) checked @endif>
                                    </td>
                                    <td>
                                        <label for="{{ $column }}" class="cursor-pointer">{{ $label }}</label>
                                    </td>
                                    <td>Campo Fijo</td>
                                    <td>
                                        <input type="number"
                                            name="fields_order[{{ $column }}]"
                                            value="{{ $defaultOrder }}"
                                            min="1"
                                            class="form-control w-20 text-center"
                                            title="Prioridad de aparición (1 = primero)">
                                    </td>
                                    <td class="text-center">
                                        <span class="text-gray-500 text-xs">(Fijo)</span>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- 2. PARÁMETROS ADICIONALES (Existentes) --}}
                            @foreach($additional_parameters as $index => $parameter)
                                <tr class="bg-orange-100/50 dark:bg-orange-900/50" id="additional-param-row-{{ $index }}">
                                    <td class="text-center">
                                        {{-- Los campos adicionales no son checkbox, se guardan por su existencia --}}
                                        <x-base.lucide class="h-5 w-5 mx-auto text-orange-600" icon="PlusCircle" />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="additional_parameters[{{ $index }}][name]"
                                            value="{{ $parameter->name }}"
                                            placeholder="Nombre del campo"
                                            class="form-control w-full"
                                            oninput="replaceSpaceWithUnderscore(this)"
                                            required
                                        >
                                    </td>
                                    <td>
                                        <select name="additional_parameters[{{ $index }}][type]" class="form-select w-full">
                                            <option value="text" @if($parameter->type == 'text') selected @endif>Texto</option>
                                            <option value="number" @if($parameter->type == 'number') selected @endif>Numérico</option>
                                            <option value="date" @if($parameter->type == 'date') selected @endif>Fecha</option>
                                            <option value="select" @if($parameter->type == 'select') selected @endif>Selección</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            name="additional_parameters[{{ $index }}][order]"
                                            value="{{ $parameter->order ?? ($defaultIndex + $index) }}"
                                            min="1"
                                            class="form-control w-20 text-center"
                                            title="Orden de aparición"
                                        >
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="text-red-500 hover:text-red-700" onclick="removeAdditionalParameterRow('{{ $index }}', '{{ $parameter->id }}')">
                                            <x-base.lucide class="h-4 w-4" icon="Trash2" />
                                        </button>
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                    </table>
                    {{-- FIN TABLA --}}
                </div>

                <input type="hidden" id="parameters-to-delete" name="parameters_to_delete" value="">

                <x-base.button type="button" class="w-full mt-3" variant="secondary" onclick="addAdditionalParameterRow()">
                    <x-base.lucide class="h-4 w-4 mr-2" icon="Plus" /> Agregar Nuevo Parámetro
                </x-base.button>

                <x-base.button class="w-full mt-5" type="submit" variant="primary">
                    Guardar Parámetros
                </x-base.button>
            </form>
        </div>
    </div>

 {{-- Fila de plantilla para nuevos campos adicionales (Se usará para inyectar con JS) --}}
<table style="display: none;">
    <tr id="additional-param-template" class="bg-orange-100/50 dark:bg-orange-900/50 new-param-row">
        <td class="text-center"><x-base.lucide class="h-5 w-5 mx-auto text-orange-600" icon="PlusCircle" /></td>
        <td>
            {{-- Note que #INDEX# sigue aquí, pero como está fuera del form, no causa error --}}
            <input type="text" name="additional_parameters[#INDEX#][name]" placeholder="Nombre del campo" class="form-control w-full" oninput="replaceSpaceWithUnderscore(this)" required>
        </td>
        <td>
            <select name="additional_parameters[#INDEX#][type]" class="form-select w-full">
                <option value="text">Texto</option>
                <option value="number">Numérico</option>
                <option value="date">Fecha</option>
                <option value="select">Selección</option>
            </select>
        </td>
        <td>
            <input type="number" name="additional_parameters[#INDEX#][order]" value="" min="1" class="form-control w-20 text-center" title="Orden de aparición">
        </td>
        <td class="text-center">
            <button type="button" class="text-red-500 hover:text-red-700" onclick="removeNewAdditionalParameterRow(this)">
                <x-base.lucide class="h-4 w-4" icon="Trash2" />
            </button>
        </td>
    </tr>
</table>
    <script>
        let nextAdditionalIndex = {{ count($additional_parameters) }}; // Inicializar con el conteo de parámetros existentes
        const initialDefaultOrder = {{ $defaultIndex }}; // El orden donde comienzan los campos adicionales

        // Función para eliminar filas de parámetros ADICIONALES YA GUARDADOS
        function removeAdditionalParameterRow(index, parameterId) {
            const row = document.getElementById('additional-param-row-' + index);
            if (row) row.remove();

            // Agregar el ID del parámetro a eliminar a un campo oculto
            let idsToDelete = document.getElementById('parameters-to-delete').value;
            idsToDelete = idsToDelete ? idsToDelete + ',' + parameterId : parameterId;
            document.getElementById('parameters-to-delete').value = idsToDelete;

            reindexAdditionalParameters(); // Reindexar para mantener la consistencia
        }

        // Función para eliminar filas de parámetros ADICIONALES NUEVOS (aún sin ID)
        function removeNewAdditionalParameterRow(button) {
            const row = button.closest('tr');
            if (row) row.remove();

            reindexAdditionalParameters(); // Reindexar para mantener la consistencia
        }

        // Función para agregar una nueva fila de parámetro adicional (utilizando la plantilla)
        function addAdditionalParameterRow() {
            const tableBody = document.getElementById('parameters-table-body');
            const templateRow = document.getElementById('additional-param-template');

            // Clonar la fila de la plantilla
            const newRow = templateRow.cloneNode(true);
            newRow.removeAttribute('id');
            newRow.style.display = ''; // Hacer visible la nueva fila

            // Calcular el orden por defecto: último orden conocido + 1
            let orderInput = newRow.querySelector('input[name*="[order]"]');
            let lastOrder = initialDefaultOrder;

            // Encontrar el último orden en la tabla
            const lastOrderInput = tableBody.querySelector('input[name*="[order]"]:last-of-type');
            if (lastOrderInput) {
                lastOrder = parseInt(lastOrderInput.value) + 1;
            } else {
                lastOrder = initialDefaultOrder;
            }

            // Reemplazar placeholders y establecer valores
            const htmlContent = newRow.outerHTML.replace(/#INDEX#/g, nextAdditionalIndex);

            tableBody.insertAdjacentHTML('beforeend', htmlContent);

            // Reobtener la fila recién insertada para establecer el valor del orden
            const insertedRow = tableBody.lastElementChild;
            const insertedOrderInput = insertedRow.querySelector('input[name*="[order]"]');
            if (insertedOrderInput) {
                insertedOrderInput.value = lastOrder;
            }

            nextAdditionalIndex++; // Incrementar el índice global para el próximo nuevo parámetro
        }

        // Asegura que los nombres de los campos de los parámetros adicionales no tengan espacios
        function replaceSpaceWithUnderscore(input) {
            input.value = input.value.replace(/\s+/g, '_');
        }

        // Reindexar los campos adicionales (importante después de eliminar)
        function reindexAdditionalParameters() {
            const tableBody = document.getElementById('parameters-table-body');
            // Selecciona todas las filas que son de parámetros adicionales (incluye existentes y nuevos)
            const additionalRows = Array.from(tableBody.querySelectorAll('tr[id^="additional-param-row-"], .new-param-row'));

            additionalRows.forEach((row, newIndex) => {
                // Si tiene ID, significa que ya existía. Mantenemos el índice existente para no perder la referencia.
                // Si no tiene ID (es un campo nuevo), usamos el newIndex.
                const isNew = row.classList.contains('new-param-row') || !row.id.startsWith('additional-param-row-');

                // Si la fila es una de las nuevas, debe usar nextAdditionalIndex,
                // pero si es una fila que se mantuvo, simplemente actualizamos sus nombres.

                let indexToUse = isNew ? newIndex : row.id.split('-').pop(); // Usamos el índice de la tabla si es nueva

                const nameInput = row.querySelector('input[name*="[name]"]');
                const typeSelect = row.querySelector('select[name*="[type]"]');
                const orderInput = row.querySelector('input[name*="[order]"]');

                if (nameInput) nameInput.name = `additional_parameters[${newIndex}][name]`;
                if (typeSelect) typeSelect.name = `additional_parameters[${newIndex}][type]`;
                if (orderInput) orderInput.name = `additional_parameters[${newIndex}][order]`;

                // Asegurar que el nextAdditionalIndex sea mayor que el mayor índice existente.
                if (newIndex >= nextAdditionalIndex) {
                    nextAdditionalIndex = newIndex + 1;
                }
            });
            // Al final, restablecemos nextAdditionalIndex para que los nuevos parámetros se agreguen al final.
            nextAdditionalIndex = additionalRows.length;
        }
        document.querySelector("form").addEventListener("submit", function () {
            document.querySelectorAll("[data-ignore-on-submit='true']").forEach(input => {
                input.removeAttribute("name"); // evita que se envíe
            });
        });
    </script>

@endsection
