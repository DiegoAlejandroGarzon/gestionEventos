@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Usuarios - Crear</title>
    <link rel="stylesheet" href="{{url('css/blade.css')}}">
@endsection

@section('subcontent')
    <div class="intro-y mt-8 flex items-center">
        <h2 class="mr-auto text-lg font-medium">Crear Usuario</h2>
    </div>
    <div class="mt-5 grid grid-cols-12 gap-6">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <!-- Nombre Completo -->
                <div class="intro-y col-span-12 lg:col-span-6">
                    <x-base.form-label for="name">Nombre Completo</x-base.form-label>
                    <div class="grid-cols-2 gap-2 sm:grid">
                        <x-base.form-input
                            class="w-full {{ $errors->has('name') ? 'border-red-500' : '' }}"
                            id="name"
                            name="name"
                            type="text"
                            placeholder="Nombres"
                            value="{{ old('name') }}"
                        />
                        @error('name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror

                        <x-base.form-input
                            class="w-full {{ $errors->has('lastname') ? 'border-red-500' : '' }}"
                            id="lastname"
                            name="lastname"
                            type="text"
                            placeholder="Apellidos"
                            value="{{ old('lastname') }}"
                        />
                        @error('lastname')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>


                <!-- Type Document -->
                <div class="row row_documento_id">
                <div class="col mt-3 col_document_type">
                    <x-base.form-label for="type_document">Tipo de Documento</x-base.form-label>
                    <x-base.tom-select
                        class="w-full {{ $errors->has('type_document') ? 'border-red-500' : '' }}"
                        id="type_document"
                        name="type_document"
                    >
                        <option value=""></option>
                        <option value="CC" {{ old('type_document') == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                        <option value="TI" {{ old('type_document') == 'TI' ? 'selected' : '' }}>Tarjeta de Identidad</option>
                        <option value="CE" {{ old('type_document') == 'CE' ? 'selected' : '' }}>Cédula de Extranjería</option>
                        <option value="PAS" {{ old('type_document') == 'PAS' ? 'selected' : '' }}>Pasaporte</option>
                    </x-base.tom-select>
                    @error('type_document')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Document Number -->
                <div class="col mt-3 col_document_id">
                    <x-base.form-label for="document_number">Número de Documento</x-base.form-label>
                    <x-base.form-input
                        class="w-full {{ $errors->has('document_number') ? 'border-red-500' : '' }}"
                        id="document_number"
                        name="document_number"
                        type="text"
                        placeholder="Número de Documento"
                        value="{{ old('document_number') }}"
                    />
                    @error('document_number')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                </div>


                <div class="row row_phone_cumpleaños">

                     <!-- Fecha Cumpleaños -->
                    <div class="col mt-3 col_cumpleaños">
                        <x-base.form-label for="birth_date">Fecha Nacimiento</x-base.form-label>
                        <x-base.form-input
                            class="w-full {{ $errors->has('birth_date') ? 'border-red-500' : '' }}"
                            id="birth_date"
                            name="birth_date"
                            type="date"
                            value="{{ old('birth_date') }}"
                        />
                        @error('birth_date')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="col mt-3 col_phone">
                        <x-base.form-label for="phone">Teléfono</x-base.form-label>
                        <x-base.form-input
                            class="w-full {{ $errors->has('phone') ? 'border-red-500' : '' }}"
                            id="phone"
                            name="phone"
                            type="text"
                            placeholder="Teléfono"
                            value="{{ old('phone') }}"
                        />
                        @error('phone')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                 <!-- Correo Electrónico -->
                 <div class="mt-3">
                    <x-base.form-label for="email">Correo Electrónico</x-base.form-label>
                    <x-base.form-input
                        class="w-full {{ $errors->has('email') ? 'border-red-500' : '' }}"
                        id="email"
                        name="email"
                        type="email"
                        placeholder="Email"
                        value="{{ old('email') }}"
                    />
                    @error('email')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>



                <!-- Contraseña -->
                <div class="row row_contraseña">
                    <div class="col mt-3 col_contraseña">
                        <x-base.form-label for="password">Contraseña</x-base.form-label>
                        <x-base.form-input
                            class="w-full {{ $errors->has('password') ? 'border-red-500' : '' }}"
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Contraseña"
                        />
                        @error('password')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col mt-3 col_contraseña_confirmacion">
                        <x-base.form-label for="password">Confirmacion</x-base.form-label>
                        <x-base.form-input
                            class="w-full {{ $errors->has('password_confirmation') ? 'border-red-500' : '' }}"
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="Confirmar Contraseña"
                        />
                        @error('password_confirmation')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>




                <!-- Role -->
                <div class="mt-3">
                    <x-base.form-label for="role_id">Role</x-base.form-label>
                    <x-base.tom-select
                        class="w-full {{ $errors->has('role_id') ? 'border-red-500' : '' }}"
                        id="role_id"
                        name="role_id"
                    >
                        <option></option>
                        @foreach ($roles as $rol)
                            <option value="{{$rol->id}}" {{ old('role_id') == $rol->id ? 'selected' : '' }}>{{ $rol->name }}</option>
                        @endforeach
                    </x-base.tom-select>
                    @error('role_id')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>


                <div class="row row_dptocity">
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
                                <option value="{{$department->id}}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->code_dane }} - {{ $department->name }}</option>
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
                            value="{{old('city_id')}}"
                        >
                            <option></option>
                        </x-base.tom-select>
                        @error('city_id')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="mt-3">
                    <label>Status</label>
                    <x-base.form-switch class="mt-2">
                        <x-base.form-switch.input type="checkbox" name="status_toggle" id="status-toggle" value="1" />
                        <input type="hidden" name="status" id="status-hidden" value="0">
                    </x-base.form-switch>
                    @error('status')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Botón para crear -->
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
                        Guardar
                    </x-base.button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('status-toggle').addEventListener('change', function() {
            document.getElementById('status-hidden').value = this.checked ? '1' : '0';
        });
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


    </script>
@endsection
