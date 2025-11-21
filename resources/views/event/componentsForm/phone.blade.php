
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
