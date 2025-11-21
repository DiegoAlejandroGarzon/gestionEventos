
                                    <div>
                                        <x-base.form-label for="email">Email</x-base.form-label>
                                        <x-base.form-input id="email" name="email" type="email" class="w-full"
                                            placeholder="Correo Electrónico" value="{{ old('email') }}" required />
                                        @error('email')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
