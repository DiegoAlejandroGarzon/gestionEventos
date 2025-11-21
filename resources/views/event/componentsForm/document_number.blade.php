
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
