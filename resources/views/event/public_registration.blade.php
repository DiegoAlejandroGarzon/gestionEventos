@extends('../themes/base')

@section('head')
    <title>PROYECTO EVENTOS</title>
@endsection

@section('content')
@if($event->color_one !== null)
    <style>
        body {
            overflow-x: hidden;
        }
        .bg-color-one {
            --tw-bg-opacity: 1;
            background-color: {{$event->color_one}};
        }
        .bg-color-two {
            --tw-bg-opacity: 1;
            background-color: {{$event->color_two}};
        }
        .before\:bg-color-two\/20::before {
            --tw-bg-opacity: 1;
            background-color: {{$event->color_two}};
        }
        .after\:bg-color-one::after {
            --tw-bg-opacity: 1;
            background-color: {{$event->color_one}};
        }
        @media (max-width: 1280px) {
            .lg\:overflow-hidden {
                overflow: hidden;
                background-color: {{$event->color_one}};
            }
        }
    </style>
    <div @class([
        'p-3 sm:px-8 relative h-screen bg-primary xl:bg-white dark:bg-darkmode-800 xl:dark:bg-darkmode-600',
        'before:hidden before:xl:block before:content-[\'\'] before:w-[57%] before:-mt-[28%] before:-mb-[16%] before:-ml-[13%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[-4.5deg] before:bg-color-two/20 before:rounded-[100%] before:dark:bg-darkmode-400',
        'after:hidden after:xl:block after:content-[\'\'] after:w-[57%] after:-mt-[20%] after:-mb-[13%] after:-ml-[13%] after:absolute after:inset-y-0 after:left-0 after:transform before:rotate-[-4.5deg] after:bg-color-one after:rounded-[100%] after:dark:bg-darkmode-700',
    ])>
@else
    <div @class([
        'p-3 sm:px-8 relative h-screen bg-primary xl:bg-white dark:bg-darkmode-800 xl:dark:bg-darkmode-600',
        'before:hidden before:xl:block before:content-[\'\'] before:w-[57%] before:-mt-[28%] before:-mb-[16%] before:-ml-[13%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[-4.5deg] before:bg-primary/20 before:rounded-[100%] before:dark:bg-darkmode-400',
        'after:hidden after:xl:block after:content-[\'\'] after:w-[57%] after:-mt-[20%] after:-mb-[13%] after:-ml-[13%] after:absolute after:inset-y-0 after:left-0 after:transform before:rotate-[-4.5deg] after:bg-primary after:rounded-[100%] after:dark:bg-darkmode-700',
    ])>
@endif
        <div class="container relative z-10 sm:px-10">
            <div class="block grid-cols-2 gap-4 xl:grid">
                <!-- BEGIN: Event Info -->
                <div
                    class="hidden relative min-h-screen xl:flex flex-col justify-center items-center text-center text-white bg-cover bg-center"
                    style="background-image: url('{{ $event->header_image_path ? asset('storage/' . $event->header_image_path) : Vite::asset('resources/images/illustration.svg') }}'); margin-left: -180px;"
                >
                    <!-- Capa gris oscura encima -->
                    <div class="absolute inset-0 bg-black/50"></div>

                    <!-- Contenido textual encima de la capa -->
                    <div class="relative z-10 p-8">
                        <div class="flex items-center justify-center mb-6">
                            <img class="w-10" src="{{ Vite::asset('resources/images/logo.svg') }}" alt="Logo" />
                            <span class="ml-3 text-lg font-semibold">ValiApp</span>
                        </div>

                        <h1 class="text-4xl font-bold mb-4 leading-tight">
                            PROYECTO EVENTOS
                        </h1>
                        <p class="text-lg text-white/90 max-w-md mx-auto">
                            Registrar eventos y llevar su gestión
                        </p>
                    </div>
                </div>


                <!-- END: Event Info -->

                <!-- BEGIN: Registration Form-->
                <div class="intro-x mt-8 h-[85vh] px-1 xl:px-0">

                    <div
                        class="mx-auto my-auto w-full rounded-md bg-white px-5 py-8 shadow-md dark:bg-darkmode-600 sm:w-3/4 sm:px-8 lg:w-2/4 xl:ml-20 xl:w-auto xl:bg-transparent xl:p-0 xl:shadow-none">
                        <h2 class="intro-x text-center text-2xl font-bold xl:text-left xl:text-3xl">
                            Inscripción para el evento: {{ $event->name }}
                        </h2>
                        <p class="intro-x mt-2 text-center text-slate-400 xl:hidden">
                            {{ $event->description }}
                        </p>
                        @if (session('success'))
                            <div class="intro-x mt-4 alert alert-success text-green-500">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="intro-x mt-4 alert alert-danger text-red-500">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('qrCode'))
                            <p><strong>Recuerda Guardar el codigo QR para poder acceder al evento:</strong></p>
                            <p><strong>Código QR:</strong></p>
                            <div class="mt-2">
                                {{ session('qrCode') }}
                            </div>
                        @endif
                        <form action="{{ route('event.register.submit', $event->public_link) }}" method="POST">
                            @csrf
                            @php
                                $selectedFields = json_decode($event->registration_parameters, true) ?? [];
                            @endphp

                            <!-- Checkbox cortesía -->
                            <div class="mt-3">
                                <x-base.form-label for="courtesy_code_checkbox">¿Tienes un Cupón de
                                    cortesía?</x-base.form-label>
                                <input type="checkbox" id="courtesy_code_checkbox" onclick="toggleCourtesyCodeInput()" />
                            </div>

                            <!-- Código de cortesía -->
                            <div class="mt-3" id="courtesy_code_container" style="display: none;">
                                <x-base.form-label for="courtesy_code">Cupón de cortesía</x-base.form-label>
                                <x-base.form-input id="courtesy_code" class="w-full" type="text" maxlength="6"
                                    name="courtesy_code" placeholder="Ingresa el código de cortesía"
                                    oninput="checkCourtesyCode()" />
                                <div id="courtesy_code_message" class="text-red-500 text-sm mt-1"></div>
                            </div>

                            <!-- Selector de Fecha -->
                            <div class="mt-3">
                                <x-base.form-label for="filter_date">Seleccionar Fecha</x-base.form-label>

                                @php
                                    $availableDates = collect($ticketTypes)
                                        ->pluck('entry_date')
                                        ->filter()
                                        ->unique()
                                        ->sort();

                                    // Si solo hay una fecha disponible, la guardamos
                                    $singleDate = $availableDates->count() === 1 ? $availableDates->first() : null;
                                @endphp

                                <x-base.tom-select
                                    id="filter_date"
                                    name="filter_date"
                                    onchange="filterTicketsByDate()"
                                    class="w-full"
                                >
                                    <option value="">Seleccione una fecha</option>

                                    @foreach ($availableDates as $date)
                                        <option
                                            value="{{ $date }}"
                                            @if($singleDate && $singleDate === $date) selected @endif
                                        >
                                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                                        </option>
                                    @endforeach
                                </x-base.tom-select>
                            </div>

                            @if($singleDate)
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const select = document.getElementById('filter_date');
                                        if (select) {
                                            select.value = "{{ $singleDate }}";
                                            // Ejecuta el filtro automáticamente
                                            filterTicketsByDate();
                                        }
                                    });
                                </script>
                            @endif

                            <!-- Selector de Ticket -->
                            <div class="mt-3">
                                <x-base.form-label for="id_ticket">Ticket</x-base.form-label>
                                <x-base.tom-select class="w-full {{ $errors->has('id_ticket') ? 'border-red-500' : '' }}"
                                    id="id_ticket" name="id_ticket">
                                    <option value="">Seleccione una fecha primero</option>
                                </x-base.tom-select>
                                @error('id_ticket')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Grid de campos en 2 columnas (solo en XL) -->
                            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                                @if (in_array('name', $selectedFields))
                                    <div>
                                        <x-base.form-label for="name">Nombre</x-base.form-label>
                                        <x-base.form-input id="name" name="name" type="text" class="w-full"
                                            placeholder="Nombre" value="{{ old('name') }}" required />
                                        @error('name')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if (in_array('lastname', $selectedFields))
                                    <div>
                                        <x-base.form-label for="lastname">Apellidos</x-base.form-label>
                                        <x-base.form-input id="lastname" name="lastname" type="text" class="w-full"
                                            placeholder="Apellidos" value="{{ old('lastname') }}" required />
                                        @error('lastname')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if (in_array('type_document', $selectedFields))
                                    <div>
                                        <x-base.form-label for="type_document">Tipo de Documento</x-base.form-label>
                                        <x-base.tom-select
                                            class="w-full {{ $errors->has('type_document') ? 'border-red-500' : '' }}"
                                            id="type_document" name="type_document">
                                            <option value=""></option>
                                            <option value="CC" {{ old('type_document') == 'CC' ? 'selected' : '' }}>
                                                Cédula
                                                de Ciudadanía</option>
                                            <option value="TI" {{ old('type_document') == 'TI' ? 'selected' : '' }}>
                                                Tarjeta
                                                de Identidad</option>
                                            <option value="CE" {{ old('type_document') == 'CE' ? 'selected' : '' }}>
                                                Cédula
                                                de Extranjería</option>
                                            <option value="PAS" {{ old('type_document') == 'PAS' ? 'selected' : '' }}>
                                                Pasaporte</option>
                                        </x-base.tom-select>
                                        @error('type_document')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if (in_array('document_number', $selectedFields))
                                    <div>
                                        <x-base.form-label for="document_number">Número de Documento</x-base.form-label>
                                        <x-base.form-input
                                            class="w-full {{ $errors->has('document_number') ? 'border-red-500' : '' }}"
                                            id="document_number" name="document_number" type="number"
                                            placeholder="Número de Documento" value="{{ old('document_number') }}" />
                                        @error('document_number')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if (in_array('birth_date', $selectedFields))
                                    <div>
                                        <x-base.form-label for="birth_date">Fecha Nacimiento</x-base.form-label>
                                        <x-base.form-input
                                            class="w-full {{ $errors->has('birth_date') ? 'border-red-500' : '' }}"
                                            id="birth_date" name="birth_date" type="date"
                                            value="{{ old('birth_date') }}" />
                                        @error('birth_date')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if (in_array('phone', $selectedFields))
                                    <div>
                                        <x-base.form-label for="phone">Teléfono</x-base.form-label>
                                        <x-base.form-input
                                            class="w-full {{ $errors->has('phone') ? 'border-red-500' : '' }}"
                                            id="phone" name="phone" type="text" placeholder="Teléfono"
                                            value="{{ old('phone') }}" />
                                        @error('phone')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if (in_array('email', $selectedFields))
                                    <div>
                                        <x-base.form-label for="email">Email</x-base.form-label>
                                        <x-base.form-input id="email" name="email" type="email" class="w-full"
                                            placeholder="Correo Electrónico" value="{{ old('email') }}" required />
                                        @error('email')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if (in_array('city_id', $selectedFields))
                                    <div>
                                        <x-base.form-label for="department_id">Departamento</x-base.form-label>
                                        <x-base.tom-select
                                            class="w-full {{ $errors->has('department_id') ? 'border-red-500' : '' }}"
                                            id="department_id" name="department_id" onchange="filterCities()">
                                            <option></option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}"
                                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                    {{ $department->code_dane }} - {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </x-base.tom-select>
                                        @error('department_id')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div>
                                        <x-base.form-label for="city_id">Ciudad</x-base.form-label>
                                        <x-base.tom-select
                                            class="w-full {{ $errors->has('city_id') ? 'border-red-500' : '' }}"
                                            id="city_id" name="city_id">
                                            <option></option>
                                        </x-base.tom-select>
                                        @error('city_id')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            <!-- Parámetros adicionales -->
                            @foreach ($additionalParameters as $parameter)
                                @php
                                    $type = $parameter['type'] ?? 'text';
                                    $name = $parameter['name'] ?? '';
                                    $label = $parameter['label'] ?? '';
                                    $options = $parameter['options'] ?? [];
                                @endphp
                                <div class="mt-4">
                                    <x-base.form-label for="{{ $name }}">{{ $name }}</x-base.form-label>
                                    @if ($type === 'select')
                                        <x-base.tom-select
                                            class="w-full {{ $errors->has($name) ? 'border-red-500' : '' }}"
                                            id="{{ $name }}" name="{{ $name }}">
                                            <option value=""></option>
                                            @foreach ($options as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old($name) == $key ? 'selected' : '' }}>{{ $value }}
                                                </option>
                                            @endforeach
                                        </x-base.tom-select>
                                    @else
                                        <x-base.form-input
                                            class="w-full {{ $errors->has($name) ? 'border-red-500' : '' }}"
                                            id="{{ $name }}" name="{{ $name }}"
                                            type="{{ $type }}" placeholder="{{ $label }}"
                                            value="{{ old($name) }}" />
                                    @endif
                                    @error($name)
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach

                            @if ($event->allow_minors)
                            <!-- Pregunta sobre menores -->
                            <div class="mt-6">
                                <label class="font-semibold text-slate-700">¿Desea inscribir menores de edad?</label>
                                <div class="flex items-center gap-4 mt-2">
                                    <label><input type="radio" name="has_minors" value="no" checked> No</label>
                                    <label><input type="radio" name="has_minors" value="yes"> Sí</label>
                                </div>
                            </div>

                            <!-- Cantidad de menores -->
                            <div id="minor-count-container" class="mt-4 hidden">
                                <label class="font-semibold text-slate-700">Cantidad de menores (máximo 5)</label>
                                <input type="number" id="minorCount" name="minor_count" min="1" max="5"
                                    class="w-24 mt-2 border rounded-md text-center" />
                            </div>

                            <!-- Campos dinámicos para menores -->
                            <div id="minorsContainer" class="mt-4 space-y-4"></div>
                            @endif

                            <!-- Botón -->
                            <div class="intro-x mt-6 text-center xl:text-left">
                                <x-base.button class="w-full px-4 py-3 xl:w-32" type="submit" variant="primary">
                                    Registrarse
                                </x-base.button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <script>
            @if (in_array('city_id', $selectedFields))
                function updateCityOptions(cities) {
                    var citySelect = document.querySelector('#city_id').tomselect;

                    // Verifica si 'cities' es un array
                    if (!Array.isArray(cities)) {
                        console.error('Expected an array of cities but got:', cities);
                        return;
                    }

                    // Limpia todas las opciones actuales
                    citySelect.clearOptions();

                    // Agrega nuevas opciones dinámicamente
                    cities.forEach(city => {
                        citySelect.addOption({
                            value: city.id,
                            text: city.name
                        });
                    });

                    // Refresca la lista de opciones para que se muestren correctamente en la interfaz
                    @if (old('city_id'))
                        citySelect.setValue({{ old('city_id') }});
                    @endif
                    citySelect.refreshOptions(false);
                }

                function filterCities() {
                    var departmentId = document.getElementById('department_id').value;
                    var citySelect = document.getElementById('city_id');

                    // Limpia el select de ciudades
                    citySelect.innerHTML = '<option></option>';

                    if (departmentId) {
                        fetch('{{ route('getCitiesByDepartment', '') }}/' + departmentId)
                            .then(response => response.json())
                            .then(data => {
                                // Verifica si 'data.cities' existe y es un array
                                if (Array.isArray(data.cities)) {
                                    updateCityOptions(data.cities);
                                } else {
                                    console.error('Invalid data format:', data);
                                }
                            })
                            .catch(error => console.error('Error fetching cities:', error));
                    }
                }
                filterCities();
            @endif

            function toggleCourtesyCodeInput() {
                var checkbox = document.getElementById('courtesy_code_checkbox');
                var container = document.getElementById('courtesy_code_container');
                if (checkbox.checked) {
                    container.style.display = 'block';
                } else {
                    container.style.display = 'none';
                }
            }

            function checkCourtesyCode() {
                var code = document.getElementById('courtesy_code').value;

                // Solo realiza la petición si el código tiene 6 dígitos
                if (code.length === 6) {
                    fetch(`/check-courtesy-code/{{ $event->id }}/${code}`, {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            var messageElement = document.getElementById('courtesy_code_message');
                            var selectTicket = document.querySelector('#id_ticket').tomselect;
                            if (data.exists) {
                                messageElement.textContent = '¡Código válido!';
                                messageElement.classList.remove('text-red-500');
                                messageElement.classList.add('text-green-500');
                                // Cambiar el valor del select
                                selectTicket.setValue(data.ticket_type.id);
                                selectTicket.refreshOptions(false);
                                // Bloquea el select
                                selectTicket.disable();
                            } else {
                                // Código no válido: muestra mensaje de error
                                messageElement.textContent = 'Código no válido.';
                                messageElement.classList.remove('text-green-500');
                                messageElement.classList.add('text-red-500');
                                // Habilita el select si estaba deshabilitado
                                selectTicket.enable();
                            }
                        })
                        .catch(error => {
                            console.error('Error al verificar el código:', error);
                        });
                }
            }

            // Mostrar opciones según la respuesta
            document.querySelectorAll('input[name="has_minors"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    const show = this.value === 'yes';
                    document.getElementById('minor-count-container').classList.toggle('hidden', !show);
                    document.getElementById('minorsContainer').innerHTML = ''; // limpiar campos
                });
            });

            // Generar los campos según la cantidad
            document.getElementById('minorCount').addEventListener('input', function () {
                const count = Math.min(parseInt(this.value || 0), 5);
                const container = document.getElementById('minorsContainer');
                container.innerHTML = '';

                for (let i = 1; i <= count; i++) {
                    container.insertAdjacentHTML('beforeend', `
                        <div class="border p-4 rounded-md bg-slate-50">
                            <h4 class="font-semibold mb-2">Menor ${i}</h4>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-base.form-label for="minor_name_${i}">Nombre completo</x-base.form-label>
                                    <x-base.form-input name="minors[${i}][full_name]" type="text"
                                        placeholder="Nombre del menor" required />
                                </div>
                                <div>
                                    <x-base.form-label for="minor_age_${i}">Edad</x-base.form-label>
                                    <x-base.form-input name="minors[${i}][age]" type="number" min="1" max="17"
                                        placeholder="Edad" required />
                                </div>
                            </div>
                        </div>
                    `);
                }
            });

        </script>
        @php
            $ticketsJs = [];
            foreach ($ticketTypes as $ticket) {
                // Normaliza fecha (YYYY-MM-DD) y horas (HH:MM)
                $entryDate = null;
                if (!empty($ticket->entry_date)) {
                    try {
                        $entryDate = \Carbon\Carbon::parse($ticket->entry_date)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $entryDate = $ticket->entry_date;
                    }
                }

                $startTime = null;
                if (!empty($ticket->entry_start_time)) {
                    try {
                        $startTime = \Carbon\Carbon::parse($ticket->entry_start_time)->format('H:i');
                    } catch (\Exception $e) {
                        $startTime = $ticket->entry_start_time;
                    }
                }

                $endTime = null;
                if (!empty($ticket->entry_end_time)) {
                    try {
                        $endTime = \Carbon\Carbon::parse($ticket->entry_end_time)->format('H:i');
                    } catch (\Exception $e) {
                        $endTime = $ticket->entry_end_time;
                    }
                }

                $ticketsJs[] = [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'price' => number_format($ticket->price, 0, '', '.'),
                    'entry_date' => $entryDate,
                    'entry_start_time' => $startTime,
                    'entry_end_time' => $endTime,
                    'capacity' => $ticket->capacity,
                    'registered' => $ticket->EventAssistant()->count() + \App\Models\Minor::whereIn('event_assistant_id', $ticket->EventAssistant->pluck('id'))->count(),
                ];
            }
        @endphp

        <script>
            const allTickets = @json($ticketsJs);
            // console.log(allTickets); // descomenta para debug
        </script>

        <script>
            function filterTicketsByDate() {
                const selectedDate = document.getElementById('filter_date').value;
                const ticketSelect = document.getElementById('id_ticket');
                const tomSelect = ticketSelect.tomselect;

                // Limpia las opciones actuales
                tomSelect.clearOptions();

                if (!selectedDate) {
                    tomSelect.addOption({ value: '', text: 'Seleccione una fecha primero' });
                    tomSelect.refreshOptions(false);
                    return;
                }

                // Filtrar los tickets que correspondan a la fecha seleccionada
                const filteredTickets = allTickets.filter(ticket => ticket.entry_date === selectedDate);

                if (filteredTickets.length === 0) {
                    tomSelect.addOption({ value: '', text: 'No hay tickets disponibles para esta fecha' });
                } else {
                    filteredTickets.forEach(ticket => {
                        let label = `${ticket.name} - $${ticket.price}`;
                        if (ticket.entry_start_time && ticket.entry_end_time) {
                            label += ` (${ticket.entry_start_time} - ${ticket.entry_end_time})`;
                        }
                        if (ticket.capacity && ticket.registered !== undefined) {
                            label += ` — Aforo: ${ticket.registered}/${ticket.capacity}`;
                        }
                        tomSelect.addOption({ value: ticket.id, text: label });
                    });
                }

                tomSelect.refreshOptions(false);

                // Si solo hay un ticket disponible, seleccionarlo automáticamente
                if (filteredTickets.length === 1) {
                    const singleTicket = filteredTickets[0];
                    tomSelect.setValue(singleTicket.id);
                }
            }

        </script>



    @endsection
