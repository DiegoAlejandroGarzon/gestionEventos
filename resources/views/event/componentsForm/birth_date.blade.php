
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
