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
