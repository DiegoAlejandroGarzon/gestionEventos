
                                    <div>
                                        <x-base.form-label for="name">Nombre</x-base.form-label>
                                        <x-base.form-input id="name" name="name" type="text" class="w-full"
                                            placeholder="Nombre" value="{{ old('name') }}" required />
                                        @error('name')
                                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
