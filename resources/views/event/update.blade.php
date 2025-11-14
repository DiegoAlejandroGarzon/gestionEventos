@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Eventos - Editar</title>
    <link rel="stylesheet" href="{{url('css/blade.css')}}">
@endsection

@section('subcontent')
    @if(session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
    @endif
    <div class="intro-y mt-8 flex items-center">
        <h2 class="mr-auto text-lg font-medium">Editar Evento</h2>
    </div>
    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <form method="POST" action="{{ route('event.update', ['id' => $event->id]) }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Nombre del Evento -->
                    <div class="mt-3">
                        <x-base.form-label for="name">Nombre del Evento</x-base.form-label>
                        <x-base.form-input
                            class="w-full {{ $errors->has('name') ? 'border-red-500' : '' }}"
                            id="name"
                            name="name"
                            type="text"
                            placeholder="Nombre del Evento"
                            value="{{ old('name', $event->name) }}"
                        />
                        @error('name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Aforo Total -->
                    <div class="mt-3">
                        <x-base.form-label for="capacity">Aforo Total</x-base.form-label>
                        <x-base.form-input
                            class="w-full {{ $errors->has('capacity') ? 'border-red-500' : '' }}"
                            id="capacity"
                            name="capacity"
                            type="number"
                            placeholder="Capacidad total del evento"
                            value="{{ old('capacity', $event->capacity) }}"
                        />
                        @error('capacity')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- <!-- Departamento -->
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
                                <option value="{{$department->id}}"
                                    {{ old('department_id', optional($event->city)->department ? $event->city->department->id : null) == $department->id ? 'selected' : '' }}>
                                    {{ $department->code_dane }} - {{ $department->name }}</option>

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
                        @if($event->city)
                            <option value="{{ $event->city->id }}">{{ $event->city->name }}</option>
                        @else
                            <option></option>
                        @endif
                        </x-base.tom-select>
                        @error('city_id')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div> --}}

                    <div class="mt-3 box">
                        <x-base.form-label class="m-2">Fechas Evento</x-base.form-label>
                        <div class="grid-cols-2 gap-2 sm:grid">
                            <!-- Fecha del Evento -->
                            <div class="m-2">
                                <x-base.form-label for="event_date">Fecha Inicial del Evento</x-base.form-label>
                                <x-base.form-input
                                    class="w-full {{ $errors->has('event_date') ? 'border-red-500' : '' }}"
                                    id="event_date"
                                    name="event_date"
                                    type="date"
                                    value="{{ old('event_date', $event->event_date) }}"
                                />
                                @error('event_date')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha Final del Evento -->
                            <div class="m-2">
                                <x-base.form-label for="event_date_end">Fecha Final del Evento</x-base.form-label>
                                <x-base.form-input
                                    class="w-full {{ $errors->has('event_date_end') ? 'border-red-500' : '' }}"
                                    id="event_date_end"
                                    name="event_date_end"
                                    type="date"
                                    value="{{ old('event_date_end', $event->event_date_end) }}"
                                />
                                @error('event_date_end')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row row_horaIni-Fin">
                        <!-- Hora de Inicio -->
                        <div class="col mt-3 col_horaIni">
                            <x-base.form-label for="start_time">Hora de Inicio</x-base.form-label>
                            <x-base.form-input
                                class="w-full {{ $errors->has('start_time') ? 'border-red-500' : '' }}"
                                id="start_time"
                                name="start_time"
                                type="time"
                                value="{{ old('start_time', $event->start_time) }}"
                            />
                            @error('start_time')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hora de Fin -->
                        <div class="col mt-3 col_horaFin">
                            <x-base.form-label for="end_time">Hora de Fin</x-base.form-label>
                            <x-base.form-input
                                class="w-full {{ $errors->has('end_time') ? 'border-red-500' : '' }}"
                                id="end_time"
                                name="end_time"
                                type="time"
                                value="{{ old('end_time', $event->end_time) }}"
                            />
                            @error('end_time')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div  class="row row_dptocity">
                        <!-- Departamento -->
                        <div class="col mt-3 col_depto">
                            <x-base.form-label for="department_id">Departamento</x-base.form-label>
                            <x-base.tom-select
                                class="w-full {{ $errors->has('department_id') ? 'border-red-500' : '' }}"
                                id="department_id"
                                name="department_id"
                                onchange="filterCities()"
                            >
                                <option></option>
                                @foreach ($departments as $department)
                                    <option value="{{$department->id}}"
                                        {{ old('department_id', optional($event->city)->department ? $event->city->department->id : null) == $department->id ? 'selected' : '' }}>
                                        {{ $department->code_dane }} - {{ $department->name }}</option>

                                @endforeach
                            </x-base.tom-select>
                            @error('department_id')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ciudad -->
                        <div class="col mt-3 col_city">
                            <x-base.form-label for="city_id">Ciudad</x-base.form-label>
                            <x-base.tom-select
                                class="w-full {{ $errors->has('city_id') ? 'border-red-500' : '' }}"
                                id="city_id"
                                name="city_id"
                            >
                            @if($event->city)
                                <option value="{{ $event->city->id }}">{{ $event->city->name }}</option>
                            @else
                                <option></option>
                            @endif
                            </x-base.tom-select>
                            @error('city_id')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tipos de Entradas -->
                    <div class="mt-3 box intro-x mt-8 max-h-[90vh] px-1 xl:px-0 overflow-y-auto">
                        <x-base.form-label class="m-2">Tipos de Entradas</x-base.form-label>
                        <div id="ticket-types-container" class="m-2">
                            @foreach($event->ticketTypes as $index => $ticket)
                                <div class="flex items-center mt-2" id="ticket_type_{{ $index }}_wrapper">
                                    <!-- Campo oculto para enviar el ID -->
                                    <input type="hidden" name="ticketTypes[{{ $index }}][id]" value="{{ $ticket->id }}">
                                    <x-base.form-input
                                        type="text"
                                        name="ticketTypes[{{ $index }}][name]"
                                        placeholder="Tipo de Entrada"
                                        class="form-control w-1/3 mr-2"
                                        value="{{ old('ticketTypes.' . $index . '.type', $ticket->name) }}"
                                    />
                                    <x-base.form-input
                                        type="number"
                                        name="ticketTypes[{{ $index }}][capacity]"
                                        placeholder="Capacidad"
                                        class="form-control w-1/3 mr-2"
                                        value="{{ old('ticketTypes.' . $index . '.capacity', $ticket->capacity) }}"
                                    />
                                    <x-base.form-input
                                        type="number"
                                        name="ticketTypes[{{ $index }}][price]"
                                        placeholder="Precio"
                                        class="form-control w-1/3 mr-2"
                                        value="{{ old('ticketTypes.' . $index . '.price', $ticket->price) }}"
                                    />
                                    <x-base.form-input
                                        type="date"
                                        name="ticketTypes[{{ $index }}][entry_date]"
                                        class="form-control w-full"
                                        placeholder="Fecha de ingreso"
                                        min="{{ $event->event_date }}"
                                        max="{{ $event->event_date_end }}"
                                        value="{{ old('ticketTypes.' . $index . '.price', $ticket->entry_date) }}"
                                    />
                                    <x-base.form-input
                                        type="time"
                                        name="ticketTypes[{{ $index }}][entry_start_time]"
                                        class="form-control w-full"
                                        placeholder="Hora inicio ingreso"
                                        value="{{ old('ticketTypes.' . $index . '.price', $ticket->entry_start_time) }}"
                                    />
                                    <x-base.form-input
                                        type="time"
                                        name="ticketTypes[{{ $index }}][entry_end_time]"
                                        class="form-control w-full"
                                        placeholder="Hora fin ingreso"
                                        value="{{ old('ticketTypes.' . $index . '.price', $ticket->entry_end_time) }}"
                                    />
                                    <x-base.tom-select
                                    type="text"
                                    name="ticketTypes[{{ $index }}][features][]"
                                    placeholder="Características"
                                    class="form-control w-1/3 mr-2"
                                    value="{{ old('ticketTypes.' . $index . '.features', $ticket->features) }}"
                                        multiple
                                    >

                                    @php $selectedFeatures = $selectedFeaturesByIndex[$index] ?? []; @endphp

                                    @foreach($features as $feature)
                                        <option value="{{ $feature['id'] }}" {{ in_array($feature['id'], old('features', $selectedFeatures)) ? 'selected' : '' }}>
                                            {{ $feature['name'] }}
                                        </option>
                                    @endforeach
                                    </x-base.tom-select>

                                    <x-base.button
                                        type="button"
                                        variant="outline-danger"
                                        onclick="removeTicketType('ticket_type_{{ $index }}')"
                                    >
                                        Eliminar
                                    </x-base.button>
                                </div>
                            @endforeach
                        </div>
                        <x-base.button
                            class="m-3"
                            type="button"
                            variant="outline-secondary"
                            onclick="addTicketType()"
                        >
                            Añadir Tipo de Entrada
                        </x-base.button>

                        @error('ticketTypes')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror

                        @foreach ($errors->get('ticketTypes.*') as $fieldIndex => $errorMessages)
                            @foreach ($errorMessages as $errorMessage)
                                <div class="text-red-500 text-sm mt-1">
                                    {{ "Ticket " . ($loop->parent->index + 1) . ": " . $errorMessage }}
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <!-- Descripción del Evento -->
                    <div class="mt-3">
                        <x-base.form-label for="description">Descripción del Evento</x-base.form-label>
                        <textarea
                            class="w-full form-control {{ $errors->has('description') ? 'border-red-500' : '' }}"
                            id="description"
                            name="description"
                            placeholder="Descripción del Evento"
                            rows="5"
                        >{{ old('description', $event->description) }}</textarea>
                        @error('description')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Dirección del Evento -->
                    <div class="mt-3">
                        <x-base.form-label for="address">Dirección del Evento</x-base.form-label>
                        <x-base.form-input
                            class="w-full {{ $errors->has('address') ? 'border-red-500' : '' }}"
                            id="address"
                            name="address"
                            type="text"
                            placeholder="Direccion del evento"
                            value="{{ old('address', $event->address) }}"
                        />
                        @error('address')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Estado por defecto-->
                    <div class="mt-3">
                        <x-base.form-label for="department_id">Estado por defecto</x-base.form-label>
                        <x-base.tom-select
                            class="w-full {{ $errors->has('status') ? 'border-red-500' : '' }}"
                            id="status"
                            name="status"
                        >
                            <option></option>
                            @foreach (config('statusEvento') as $label => $valor)
                                <option value="{{$valor}}" {{ old('status', $event->status) == $valor ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-base.tom-select>
                        @error('status')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Imagen del Encabezado -->
                    <div class="mt-3">
                        <x-base.form-label for="header_image_path">Imagen del Encabezado</x-base.form-label>
                        <input
                            class="w-full form-control {{ $errors->has('header_image_path') ? 'border-red-500' : '' }}"
                            id="header_image_path"
                            name="header_image_path"
                            type="file"
                            accept="image/*"
                        />
                        @error('header_image_path')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                        @if($event->header_image_path)
                            <div class="mt-3">
                                <img src="{{ asset('storage/' . $event->header_image_path) }}" alt="Imagen del Evento" class="w-32 h-32">
                            </div>
                        @endif
                    </div>

                    <div class="mt-3 box">
                        <x-base.form-label class="m-2">Colores Representativos</x-base.form-label>
                        <div class="grid-cols-2 gap-2 sm:grid">
                            <!-- color_one -->
                            <div class="m-2">
                                <x-base.form-label for="color_one">Color Primario</x-base.form-label>
                                <x-base.form-input
                                    class="w-full {{ $errors->has('color_one') ? 'border-red-500' : '' }}"
                                    id="color_one"
                                    name="color_one"
                                    type="color"
                                    placeholder="Direccion del evento"
                                    value="{{ old('color_one', $event->color_one ?? '#FFFFFF') }}"
                                />
                                @error('color_one')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- two -->
                            <div class="m-2">
                                <x-base.form-label for="color_two">Color Secundario</x-base.form-label>
                                <x-base.form-input
                                    class="w-full {{ $errors->has('color_two') ? 'border-red-500' : '' }}"
                                    id="color_two"
                                    name="color_two"
                                    type="color"
                                    placeholder="Direccion del evento"
                                    value="{{ old('color_one', $event->color_two ?? '#FFFFFF') }}"
                                />
                                @error('color_two')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Permitir menores de edad -->
                    <div class="mt-3">
                        <x-base.form-label for="allow_minors">¿Permitir inscripción de menores de edad?</x-base.form-label>
                        <div class="flex items-center space-x-2">
                            <input type="hidden" name="allow_minors" value="0">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    id="allow_minors"
                                    name="allow_minors"
                                    class="form-check-input"
                                    value="1"
                                    {{ old('allow_minors', $event->allow_minors) ? 'checked' : '' }}
                                >
                                <span class="ml-2 text-slate-700">Sí, permitir registro de menores</span>
                            </label>
                        </div>
                        @error('allow_minors')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Generar código QR al inscribir -->
                    <input type="hidden" name="generate_qr" value="0">
                    <div class="mt-3 box p-3">
                        <x-base.form-label for="generate_qr">¿Generar código QR al inscribir?</x-base.form-label>
                        <div class="flex items-center space-x-2">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    id="generate_qr"
                                    name="generate_qr"
                                    class="form-check-input"
                                    value="1"
                                    {{ old('generate_qr', $event->generate_qr) ? 'checked' : '' }}
                                >
                                <span class="ml-2 text-slate-700">Sí, generar QR</span>
                            </label>
                        </div>
                        @error('generate_qr')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Enviar correo al inscribir -->
                    <input type="hidden" name="send_email" value="0">
                    <div class="mt-3 box p-3">
                        <x-base.form-label for="send_email">¿Enviar correo al inscribir?</x-base.form-label>
                        <div class="flex items-center space-x-2">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    id="send_email"
                                    name="send_email"
                                    class="form-check-input"
                                    value="1"
                                    {{ old('send_email', $event->send_email) ? 'checked' : '' }}
                                >
                                <span class="ml-2 text-slate-700">Sí, enviar correo</span>
                            </label>
                        </div>
                        @error('send_email')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Habilitar modo offline al validar información -->
                    <input type="hidden" name="mode_offline" value="0">
                    <div class="mt-3 box p-3">
                        <x-base.form-label for="mode_offline">¿Habilitar modo offline al validar información?</x-base.form-label>
                        <div class="flex items-center space-x-2">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    id="mode_offline"
                                    name="mode_offline"
                                    class="form-check-input"
                                    value="1"
                                    {{ old('mode_offline', $event->mode_offline) ? 'checked' : '' }}
                                >
                                <span class="ml-2 text-slate-700">Sí, enviar correo</span>
                            </label>
                        </div>
                        @error('mode_offline')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Campos Adicionales -->
                    <div class="mt-3">
                        <x-base.form-label>Campos Adicionales</x-base.form-label>
                        <div id="dynamic-fields-container">
                            @if (!is_null($event->additionalFields) && is_array(json_decode($event->additionalFields, true)))
                            @foreach(json_decode($event->additionalFields, true) as $index => $field)
                                <div class="flex items-center mt-2" id="additional_field_{{ $index }}_wrapper">
                                    <input
                                        type="text"
                                        name="additionalFields[{{ $index }}][label]"
                                        placeholder="Etiqueta"
                                        class="form-control w-1/3 mr-2"
                                        value="{{ $field['label'] }}"
                                    />
                                    <input
                                        type="text"
                                        name="additionalFields[{{ $index }}][value]"
                                        placeholder="Valor"
                                        class="form-control w-1/3 mr-2"
                                        value="{{ $field['value'] }}"
                                    />
                                    <x-base.button
                                        type="button"
                                        variant="outline-danger"
                                        onclick="removeDynamicField('additional_field_{{ $index }}')"
                                    >
                                        Eliminar
                                    </x-base.button>
                                </div>
                            @endforeach
                            @endif
                        </div>
                        <x-base.button
                            class="mt-3"
                            type="button"
                            variant="outline-secondary"
                            onclick="addDynamicField()"
                        >
                            Añadir Campo
                        </x-base.button>

                        @error('additionalFields')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror

                        @foreach ($errors->get('additionalFields.*') as $fieldIndex => $errorMessages)
                            @foreach ($errorMessages as $errorMessage)
                                <div class="text-red-500 text-sm mt-1">
                                    {{ "Campo adicional " . ($loop->parent->index + 1) . ": " . $errorMessage }}
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="mt-5 text-right">
                        <x-base.button
                            class="mr-1 w-24"
                            type="button"
                            variant="outline-secondary"
                            onclick="window.location='{{ url()->previous() }}'"
                        >
                            Cancelar
                        </x-base.button>
                        <x-base.button
                            class="w-24"
                            type="submit"
                            variant="primary"
                        >
                            Actualizar
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let ticketIndex = {{ $event->ticketTypes->count() }};
        @if (!is_null($event->additionalFields) && is_array(json_decode($event->additionalFields, true)))
        let fieldIndex = {{ count(json_decode($event->additionalFields, true)) }};
        @else
        let fieldIndex = 0;
        @endif

        function addTicketType() {
            const container = document.getElementById('ticket-types-container');
            const ticketTypeId = `ticket_type_${ticketIndex}`;
            const fieldHtml = `
                <div class="flex items-center mt-2" id="${ticketTypeId}_wrapper">
                    <x-base.form-input
                        class="w-full"
                        id="${ticketTypeId}_name"
                        name="ticketTypes[${ticketIndex}][name]"
                        type="text"
                        placeholder="Nombre del Tipo de Entrada"
                    />
                    <x-base.form-input
                        id="${ticketTypeId}_capacity"
                        type="number"
                        name="ticketTypes[${ticketIndex}][capacity]"
                        placeholder="Capacidad"
                        class="form-control w-full"
                    />
                    <x-base.form-input
                        id="${ticketTypeId}_price"
                        type="number"
                        step="0.01"
                        name="ticketTypes[${ticketIndex}][price]"
                        placeholder="Precio"
                        class="form-control w-full"
                    />

                    <x-base.form-input
                        type="date"
                        name="ticketTypes[${ticketIndex}][entry_date]"
                        placeholder="Fecha de ingreso"
                        class="form-control w-full sm:w-1/5"
                        min="${document.getElementById('event_date')?.value}"
                        max="${document.getElementById('event_date_end')?.value}"
                    />

                    <x-base.form-input
                        type="time"
                        name="ticketTypes[${ticketIndex}][entry_start_time]"
                        placeholder="Hora inicio ingreso"
                        class="form-control w-full sm:w-1/6"
                    />

                    <x-base.form-input
                        type="time"
                        name="ticketTypes[${ticketIndex}][entry_end_time]"
                        placeholder="Hora fin ingreso"
                        class="form-control w-full sm:w-1/6"
                    />
                    <select
                        id="${ticketTypeId}_features"
                        name="ticketTypes[${ticketIndex}][features][]"
                        multiple="multiple"
                        class="tom-select w-full mt-2"
                    >
                        @foreach($features as $feature)
                            <option value="{{ $feature->id }}" >{{ $feature->name }} {{ $feature->consumable ? '- (CONSUMIBLE)' : '' }}</option>
                        @endforeach
                    </select>
                    <x-base.button
                        type="button"
                        variant="outline-danger"
                        onclick="removeTicketType('${ticketTypeId}')"
                        class="mt-3"
                    >
                        Eliminar
                    </x-base.button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', fieldHtml);

            // Inicializar Tom Select en el nuevo elemento select
            new TomSelect(`#${ticketTypeId}_features`, {
                plugins: ['remove_button'],
                maxItems: null,
            });

            ticketIndex++;
        }

        function removeTicketType(ticketId) {
            document.getElementById(`${ticketId}_wrapper`).remove();
        }

        function addDynamicField() {
            const container = document.getElementById('dynamic-fields-container');
            const fieldId = `additional_field_${fieldIndex}`;
            const fieldHtml = `
                <div class="flex items-center mt-2" id="${fieldId}_wrapper">
                    <input
                        type="text"
                        name="additionalFields[${fieldIndex}][label]"
                        placeholder="Etiqueta"
                        class="form-control w-1/3 mr-2"
                    />
                    <input
                        type="text"
                        name="additionalFields[${fieldIndex}][value]"
                        placeholder="Valor"
                        class="form-control w-1/3 mr-2"
                    />
                    <x-base.button
                        type="button"
                        variant="outline-danger"
                        onclick="removeDynamicField('${fieldId}')"
                    >
                        Eliminar
                    </x-base.button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', fieldHtml);
            fieldIndex++;
        }
        function removeDynamicField(fieldId) {
            document.getElementById(`${fieldId}_wrapper`).remove();
        }

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
                        if (Array.isArray(data.cities)) {
                            updateCityOptions(data.cities);
                        } else {
                            console.error('Invalid data format:', data);
                        }
                    })
                    .catch(error => console.error('Error fetching cities:', error));
            }
        }

        document.addEventListener('change', function(e) {
            if (e.target && e.target.type === 'date' && e.target.name.includes('entry_date')) {
                const eventStart = document.getElementById('event_date').value;
                const eventEnd = document.getElementById('event_date_end').value;
                const selected = e.target.value;
                if (eventStart && eventEnd && (selected < eventStart || selected > eventEnd)) {
                    alert('⚠️ La fecha de ingreso debe estar dentro del rango del evento.');
                    e.target.value = ''; // limpia el campo
                }
            }
        });
    </script>
@endsection
