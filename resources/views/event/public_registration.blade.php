@extends('../themes/base')

@section('head')
    <title>PROYECTO EVENTOS</title>
@endsection

@section('content')
    <div @class([
        'p-3 sm:px-8 relative min-h-screen bg-primary xl:bg-white dark:bg-darkmode-800 xl:dark:bg-darkmode-600',
        'before:hidden before:xl:block before:content-[\'\'] before:w-[57%] before:-mt-[28%] before:-mb-[16%] before:-ml-[13%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[-4.5deg] before:bg-primary/20 before:rounded-[100%] before:dark:bg-darkmode-400',
        'after:hidden after:xl:block after:content-[\'\'] after:w-[57%] after:-mt-[20%] after:-mb-[13%] after:-ml-[13%] after:absolute after:inset-y-0 after:left-0 after:transform after:rotate-[-4.5deg] after:bg-primary after:rounded-[100%] after:dark:bg-darkmode-700',
    ])>
        <div class="container relative z-10 sm:px-10">
            <div class="block grid-cols-2 gap-4 xl:grid">
                <!-- BEGIN: Event Info -->
                <div class="hidden min-h-screen flex-col xl:flex">
                    <img class="w-6" src="{{ Vite::asset('resources/images/logo.svg') }}" alt="" />
                    <span class="ml-3 text-lg text-white"> SSISET </span>
                    <div class="my-auto">
                        @if ($event->header_image_path)
                            <img class="-intro-x -mt-16 w-1/2" src="{{ asset('storage/' . $event->header_image_path) }}"
                                alt="Imagen del evento" />
                        @else
                            <img class="-intro-x -mt-16 w-1/2" src="{{ Vite::asset('resources/images/illustration.svg') }}"
                                alt="" />
                        @endif
                        <div class="-intro-x mt-10 text-4xl font-medium leading-tight text-white">
                            PROYECTO EVENTOS
                        </div>
                        <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">
                            Registrar eventos y llevar su gestión
                        </div>
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

                            <!-- Selector de Ticket -->
                            <div class="mt-3">
                                <x-base.form-label for="id_ticket">Ticket</x-base.form-label>
                                <x-base.tom-select class="w-full {{ $errors->has('id_ticket') ? 'border-red-500' : '' }}"
                                    id="id_ticket" name="id_ticket" onchange="filterCities()">
                                    <option></option>
                                    @foreach ($ticketTypes as $ticket)
                                        <option value="{{ $ticket->id }}"
                                            {{ old('id_ticket') == $ticket->id ? 'selected' : '' }}>
                                            {{ $ticket->name }} - ${{ number_format($ticket->price, 0, '', '.') }}
                                        </option>
                                    @endforeach
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
                                            id="document_number" name="document_number" type="text"
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
                                    <x-base.form-label for="{{ $name }}">{{ $label }}</x-base.form-label>
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
                        fetch('/cities/' + departmentId)
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
        </script>
    @endsection
