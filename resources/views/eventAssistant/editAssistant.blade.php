@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Editar Asistente del Evento</title>
@endsection

@section('subcontent')
<div class="container">
    <h2 class="mb-4">Editar Asistente para el Evento: <b>{{ $event->name }}</b></h2>

    <!-- Formulario de Edición -->
        <form method="POST" action="{{ route('eventAssistant.update', $eventAssistant->id) }}">
            @csrf
            @php
                // Obtener los parámetros guardados en registration_parameters
                $selectedFields = json_decode($event->registration_parameters, true) ?? [];
            @endphp
            @method('PUT')

        <!-- Ticket -->
            <div class="mt-3">
                <x-base.form-label for="id_ticket">Ticket</x-base.form-label>
                <x-base.tom-select
                    class="w-full {{ $errors->has('id_ticket') ? 'border-red-500' : '' }}"
                    id="id_ticket"
                    name="id_ticket"
                >
                    <option></option>
                    @foreach ($ticketTypes as $ticket)
                        <option value="{{ $ticket->id }}" {{ $eventAssistant->ticketType?->id == $ticket?->id ? 'selected' : '' }}>{{ $ticket->name }}</option>
                    @endforeach
                </x-base.tom-select>
                @error('id_ticket')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

    <!-- Campos dinámicos basados en 'registration_parameters' -->
    @foreach ($selectedFields as $field)
        @switch($field)
            @case('name')
                <x-base.form-label for="name">Nombre</x-base.form-label>
                <x-base.form-input id="name" class="intro-x block min-w-full px-4 py-3 xl:min-w-[350px]" type="text" name="name" placeholder="Nombre" value="{{ old('name', $eventAssistant->user->name) }}" required />
                @break

            @case('lastname')
                <x-base.form-label for="lastname">Apellido</x-base.form-label>
                <x-base.form-input id="lastname" class="intro-x block min-w-full px-4 py-3 xl:min-w-[350px]" type="text" name="lastname" placeholder="Apellido" value="{{ old('lastname', $eventAssistant->user->lastname) }}" required />
                @break

            @case('type_document')
                <!-- Type Document -->
                <div class="mt-3">
                    <x-base.form-label for="type_document">Tipo de Documento</x-base.form-label>
                    <x-base.tom-select
                        class="w-full {{ $errors->has('type_document') ? 'border-red-500' : '' }}"
                        id="type_document"
                        name="type_document"
                    >
                        <option value=""></option>
                        <option value="CC" {{ old('type_document', $eventAssistant->user->type_document) == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                        <option value="TI" {{ old('type_document', $eventAssistant->user->type_document) == 'TI' ? 'selected' : '' }}>Tarjeta de Identidad</option>
                        <option value="CE" {{ old('type_document', $eventAssistant->user->type_document) == 'CE' ? 'selected' : '' }}>Cédula de Extranjería</option>
                        <option value="PAS" {{ old('type_document', $eventAssistant->user->type_document) == 'PAS' ? 'selected' : '' }}>Pasaporte</option>
                    </x-base.tom-select>
                    @error('type_document')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                @break

                @case('document_number')
                <!-- Document Number -->
                <div class="mt-3">
                    <x-base.form-label for="document_number">Número de Documento</x-base.form-label>
                    <x-base.form-input
                        class="w-full {{ $errors->has('document_number') ? 'border-red-500' : '' }}"
                        id="document_number"
                        name="document_number"
                        type="text"
                        placeholder="Número de Documento"
                        value="{{ $eventAssistant->user->document_number }}"
                    />
                    @error('document_number')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                @break

                @case('birth_date')
                <!-- Fecha Cumpleaños -->
                <div class="mt-3">
                    <x-base.form-label for="birth_date">Fecha Nacimiento</x-base.form-label>
                    <x-base.form-input
                        class="w-full {{ $errors->has('birth_date') ? 'border-red-500' : '' }}"
                        id="birth_date"
                        name="birth_date"
                        type="date"
                        value="{{ $eventAssistant->user->birth_date }}"
                        onchange="checkAge()"
                    />
                    @error('birth_date')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Botón Asignar Acudiente -->
                <div class="mt-3" id="guardian-section" style="display:none;">
                    <a onclick="showGuardianSelect()" class="bg-blue-500 text-white px-4 py-2 rounded">
                        Asignar Acudiente
                    </a>
                </div>
                <!-- Select Acudiente -->
                <div class="mt-3" id="guardian-select-section" style="{{$eventAssistant->guardian_id != null ? '' : 'display:none;'}}">
                    <x-base.form-label for="guardian_id">Acudientes disponibles</x-base.form-label>
                    <x-base.tom-select
                        class="w-full {{ $errors->has('guardian_id') ? 'border-red-500' : '' }}"
                        id="guardian_id"
                        name="guardian_id"
                        onchange="filterCities()"
                    >
                        <option></option>
                        @foreach ($guardians as $guardian)
                            <option value="{{$guardian->user->id}}" {{ $eventAssistant->guardian_id == $guardian->user->id ? 'selected' : '' }}>{{ $guardian->user->name }} - {{ $guardian->user->document_number }}</option>
                        @endforeach
                    </x-base.tom-select>
                    @error('guardian_id')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                @break

                @case('phone')
                <div class="mt-3">
                    <x-base.form-label for="phone">Teléfono</x-base.form-label>
                    <x-base.form-input
                        class="w-full {{ $errors->has('phone') ? 'border-red-500' : '' }}"
                        id="phone"
                        name="phone"
                        type="text"
                        placeholder="Teléfono"
                        value="{{ $eventAssistant->user->phone }}"
                    />
                    @error('phone')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                @break

                @case('email')
                    <x-base.form-label for="email">Email</x-base.form-label>
                    <x-base.form-input id="email" class="intro-x mt-4 block min-w-full px-4 py-3 xl:min-w-[350px]" type="email" name="email" placeholder="Correo Electrónico" value="{{ old('email', $eventAssistant->user->email) }}" required />
                    @break

                @case('city_id')
                <div class="mt-3">
                    <x-base.form-label for="department_id">Departamento</x-base.form-label>
                    <x-base.tom-select
                        class="w-full {{ $errors->has('department_id') ? 'border-red-500' : '' }}"
                        id="department_id"
                        name="department_id"
                        onchange="filterCities()"
                    >
                        <option></option>
                        @foreach ($departments as $department)
                            <option value="{{$department->id}}" {{ $eventAssistant->user->department_id == $department->id ? 'selected' : '' }}>{{ $department->code_dane }} - {{ $department->name }}</option>
                        @endforeach
                    </x-base.tom-select>
                    @error('department_id')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Ciudad -->
                <div class="mt-3">
                    <x-base.form-label for="city_id">Ciudad</x-base.form-label>
                    <x-base.tom-select
                        class="w-full {{ $errors->has('city_id') ? 'border-red-500' : '' }}"
                        id="city_id"
                        name="city_id"
                    >
                        <option></option>
                    </x-base.tom-select>
                    @error('city_id')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                @break

            <!-- Continúa con los demás campos de manera similar -->
        @endswitch

        @error($field)
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        @endforeach

        <!-- Campos adicionales dinámicos -->
        @foreach ($additionalParameters as $parameter)
            @php
                $type = $parameter['type'] ?? 'text';
                $name = $parameter['name'] ?? '';
                $label = $parameter['label'] ?? '';
                $options = $parameter['options'] ?? [];
                $value = '';
                foreach($userEventParameter as $parameterUser){
                    if($parameterUser->additional_parameter_id == $parameter['id']){
                        $value = $parameterUser->value;
                    }
                }
            @endphp

            <div class="mt-3">
                @if ($type == 'select')
                    <x-base.form-label for="{{ $name }}">{{ $label }}</x-base.form-label>
                    <x-base.tom-select
                        class="w-full {{ $errors->has($name) ? 'border-red-500' : '' }}"
                        id="{{ $name }}"
                        name="{{ $name }}"
                    >
                        <option value=""></option>
                        @foreach ($options as $key => $value)
                            <option value="{{ $key }}" {{ old($name, $userEventParameter->$name) == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </x-base.tom-select>
                @else
                    <x-base.form-label for="{{ $name }}">{{ $name }}</x-base.form-label>
                    <x-base.form-input
                        class="w-full {{ $errors->has($name) ? 'border-red-500' : '' }}"
                        id="{{ $name }}"
                        name="{{ $name }}"
                        type="{{ $type }}"
                        placeholder="{{ $label }}"
                        value="{{$value}}"
                    />
                @endif

                @error($name)
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        @endforeach

        <!-- Botón de envío -->
        <div class="intro-x mt-5 text-center xl:mt-8 xl:text-left">
            <x-base.button class="w-full px-4 py-3 align-top xl:mr-3 xl:w-32" type="submit" variant="primary">
                Guardar Cambios
            </x-base.button>
        </div>
    </form>
</div>


<script>
    // Función para calcular la edad y mostrar el botón si es menor de edad
    function checkAge() {
        const birthDate = document.getElementById('birth_date').value;
        if (birthDate) {
            const today = new Date();
            const birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear(); // Cambiado a 'let'
            const monthDiff = today.getMonth() - birth.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }

            if (age < 18) {
                document.getElementById('guardian-section').style.display = 'block';
            } else {
                document.getElementById('guardian-section').style.display = 'none';
                document.getElementById('guardian-select-section').style.display = 'none';
            }
        }
    }

    // Función para mostrar el select de acudiente con una alerta
    function showGuardianSelect() {
        alert("Recuerda que para asignar un acudiente, solo van a poder ser asignados los que ya están creados en el evento y tengan un tipo de documento.");
        document.getElementById('guardian-select-section').style.display = 'block';

        // Realizar petición para obtener los asistentes
        fetch('/event-assistants?event_id={{ $event->id }}') // Ruta al controlador para obtener los asistentes
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('guardian_id');
                select.innerHTML = ''; // Limpiar select
                data.forEach(assistant => {
                    if (assistant.document_number !== null) {
                        const option = document.createElement('option');
                        option.value = assistant.id;
                        option.text = assistant.name + ' ' + assistant.lastname;
                        select.appendChild(option);
                    }
                });
            });
    }

    @if(in_array('city_id', $selectedFields))

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
                citySelect.addOption({value: city.id, text: city.name});
            });

            @if(old('city_id'))
            console.log("se va a asiganr "+{{ old('city_id') }});
            citySelect.setValue({{ old('city_id') }});
            @endif
            // Refresca la lista de opciones para que se muestren correctamente en la interfaz
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
    @endif
</script>
@endsection
