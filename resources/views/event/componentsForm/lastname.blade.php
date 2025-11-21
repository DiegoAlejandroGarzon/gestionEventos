
                                    <div>
                                        <x-base.form-label for="lastname">Apellidos</x-base.form-label>
                                        <x-base.form-input id="lastname" name="lastname" type="text" class="w-full"
                                            placeholder="Apellidos" value="{{ old('lastname') }}" required />
                                        @error('lastname')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
