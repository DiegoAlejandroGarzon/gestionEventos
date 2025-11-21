
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

<script>
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
</script>
