<!-- BEGIN: Top Bar -->
<div class="relative z-[51] flex h-[67px] items-center border-b border-slate-200">
    <!-- BEGIN: Breadcrumb -->
    <x-base.breadcrumb class="-intro-x mr-auto hidden sm:flex">
        <x-base.breadcrumb.link :index="0">Aplicacion</x-base.breadcrumb.link>
        <x-base.breadcrumb.link
            :index="1"
            :active="true"
        >
            Dashboard
        </x-base.breadcrumb.link>
    </x-base.breadcrumb>
    <!-- END: Breadcrumb -->
    <!-- BEGIN: Search -->
    {{-- <div class="search intro-x relative mr-3 sm:mr-6">
        <div class="relative hidden sm:block">
            <x-base.form-input
                class="w-56 rounded-full border-transparent bg-slate-300/50 pr-8 shadow-none transition-[width] duration-300 ease-in-out focus:w-72 focus:border-transparent dark:bg-darkmode-400/70"
                type="text"
                placeholder="Search..."
            />
            <x-base.lucide
                class="absolute inset-y-0 right-0 my-auto mr-3 h-5 w-5 text-slate-600 dark:text-slate-500"
                icon="Search"
            />
        </div>
        <a
            class="relative text-slate-600 sm:hidden"
            href=""
        >
            <x-base.lucide
                class="h-5 w-5 dark:text-slate-500"
                icon="Search"
            />
        </a>
        <x-base.transition
            class="search-result absolute right-0 z-10 mt-[3px] hidden"
            selector=".show"
            enter="transition-all ease-linear duration-150"
            enterFrom="mt-5 invisible opacity-0 translate-y-1"
            enterTo="mt-[3px] visible opacity-100 translate-y-0"
            leave="transition-all ease-linear duration-150"
            leaveFrom="mt-[3px] visible opacity-100 translate-y-0"
            leaveTo="mt-5 invisible opacity-0 translate-y-1"
        >
            <div class="box w-[450px] p-5">
                <div class="mb-2 font-medium">Pages</div>
                <div class="mb-5">
                    <a
                        class="flex items-center"
                        href=""
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-success/20 text-success dark:bg-success/10">
                            <x-base.lucide
                                class="h-4 w-4"
                                icon="Inbox"
                            />
                        </div>
                        <div class="ml-3">Mail Settings</div>
                    </a>
                    <a
                        class="mt-2 flex items-center"
                        href=""
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-pending/10 text-pending">
                            <x-base.lucide
                                class="h-4 w-4"
                                icon="Users"
                            />
                        </div>
                        <div class="ml-3">Users & Permissions</div>
                    </a>
                    <a
                        class="mt-2 flex items-center"
                        href=""
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary/80 dark:bg-primary/20">
                            <x-base.lucide
                                class="h-4 w-4"
                                icon="CreditCard"
                            />
                        </div>
                        <div class="ml-3">Transactions Report</div>
                    </a>
                </div>
                <div class="mb-2 font-medium">Users</div>
                <div class="mb-5">
                    @foreach (array_slice($fakers, 0, 4) as $faker)
                        <a
                            class="mt-2 flex items-center"
                            href=""
                        >
                            <div class="image-fit h-8 w-8">
                                <img
                                    class="rounded-full"
                                    src="{{ Vite::asset($faker['photos'][0]) }}"
                                    alt="Midone - Tailwind Admin Dashboard Template"
                                />
                            </div>
                            <div class="ml-3">{{ $faker['users'][0]['name'] }}</div>
                            <div class="ml-auto w-48 truncate text-right text-xs text-slate-500">
                                {{ $faker['users'][0]['email'] }}
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mb-2 font-medium">Products</div>
                @foreach (array_slice($fakers, 0, 4) as $faker)
                    <a
                        class="mt-2 flex items-center"
                        href=""
                    >
                        <div class="image-fit h-8 w-8">
                            <img
                                class="rounded-full"
                                src="{{ Vite::asset($faker['images'][0]) }}"
                                alt="Midone - Tailwind Admin Dashboard Template"
                            />
                        </div>
                        <div class="ml-3">{{ $faker['products'][0]['name'] }}</div>
                        <div class="ml-auto w-48 truncate text-right text-xs text-slate-500">
                            {{ $faker['products'][0]['category'] }}
                        </div>
                    </a>
                @endforeach
            </div>
        </x-base.transition>
    </div> --}}
    <!-- END: Search  -->
    <!-- BEGIN: Notifications -->
    {{-- <x-base.popover class="intro-x mr-auto sm:mr-6">
        <x-base.popover.button
            class="relative block text-slate-600 outline-none before:absolute before:right-0 before:top-[-2px] before:h-[8px] before:w-[8px] before:rounded-full before:bg-danger before:content-['']"
        >
            <x-base.lucide
                class="h-5 w-5 dark:text-slate-500"
                icon="Bell"
            />
        </x-base.popover.button>
        <x-base.popover.panel class="mt-2 w-[280px] p-5 sm:w-[350px]">
            <div class="mb-5 font-medium">Notifications</div>
            @foreach (array_slice($fakers, 0, 5) as $fakerKey => $faker)
                <div @class([
                    'cursor-pointer relative flex items-center',
                    'mt-5' => $fakerKey,
                ])>
                    <div class="image-fit relative mr-1 h-12 w-12 flex-none">
                        <img
                            class="rounded-full"
                            src="{{ Vite::asset($faker['photos'][0]) }}"
                            alt="Midone - Tailwind Admin Dashboard Template"
                        />
                        <div
                            class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-success dark:border-darkmode-600">
                        </div>
                    </div>
                    <div class="ml-2 overflow-hidden">
                        <div class="flex items-center">
                            <a
                                class="mr-5 truncate font-medium"
                                href=""
                            >
                                {{ $faker['users'][0]['name'] }}
                            </a>
                            <div class="ml-auto whitespace-nowrap text-xs text-slate-400">
                                {{ $faker['times'][0] }}
                            </div>
                        </div>
                        <div class="mt-0.5 w-full truncate text-slate-500">
                            {{ $faker['news'][0]['short_content'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </x-base.popover.panel>
    </x-base.popover> --}}
    <!-- END: Notifications  -->
    <!-- BEGIN: Account Menu -->

    {{-- <div class="text-center mr-2">
        <a data-tw-merge data-tw-toggle="modal" data-tw-target="#basic-slide-over-preview" href="#" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary">Escaner QR</a>
    </div> --}}

    <!-- BEGIN: Slide Over QR -->
    <div data-tw-backdrop="" aria-hidden="true" tabindex="-1" id="basic-slide-over-preview" class="modal group bg-black/60 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&amp;:not(.show)]:duration-[0s,0.2s] [&amp;:not(.show)]:delay-[0.2s,0s] [&amp;:not(.show)]:invisible [&amp;:not(.show)]:opacity-0 [&amp;.show]:visible [&amp;.show]:opacity-100 [&amp;.show]:duration-[0s,0.4s]">
        <div data-tw-merge class="w-[90%] ml-auto h-screen flex flex-col bg-white relative shadow-md transition-[margin-right] duration-[0.6s] -mr-[100%] group-[.show]:mr-0 dark:bg-darkmode-600 sm:w-[460px]">
            <div data-tw-merge class="flex items-center px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400 p-5">
                <h2 class="mr-auto text-base font-medium">
                    Escaner de Codigo QR
                </h2>
            </div>
            <div data-tw-merge class="p-5 overflow-y-auto flex-1">
                <div id="qr-reader" style="width: 300px;" class="border "></div>
                <div id="qr-reader-results" class="border "></div>
            </div>

        </div>
    </div>
    <!-- END: Slide Over QR -->
    <!-- Incluir la biblioteca de html5-qrcode -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <!-- Contenedor para el lector de QR -->
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Manejar el resultado escaneado aquí
            const resultElement = document.getElementById('qr-reader-results');

            // Verificar si el texto decodificado es una URL
            const urlPattern = new RegExp('^(https?:\\/\\/)?' + // Protocolo
                '((([a-z0-9\\-]+\\.)+[a-z]{2,})|' + // Dominio
                'localhost|' + // localhost
                '\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}|' + // IP
                '\\[?[a-f0-9]*:[a-f0-9:%.]*\\])' + // IPv6
                '(\\:\\d+)?(\\/[-a-z0-9%_.~+]*)*' + // Puerto y ruta
                '(\\?[;&a-z0-9%_.~+=-]*)?' + // Consulta
                '(\\#[-a-z0-9_]*)?$', 'i'); // Fragmento

            if (urlPattern.test(decodedText)) {
                // Si es una URL, crear un enlace
                resultElement.innerHTML = `Codigo QR Detectado: <a href="${decodedText}" target="_blank">${decodedText}</a>`;
            } else {
                // Si no es una URL, mostrar el texto como está
                resultElement.innerText = `Codigo QR Detectado: ${decodedText}`;
            }
        }

        function onScanError(errorMessage) {
            // Manejar el error de escaneo
            console.error(`Error al escanear codigo QR: ${errorMessage}`);
        }

        // Inicializa el lector de QR
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: 250 }
        );

        // Comienza a escanear
        html5QrcodeScanner.render(onScanSuccess, onScanError);
    </script>
    <x-base.menu>
        <x-base.menu.button class="image-fit zoom-in intro-x block h-8 w-8 overflow-hidden rounded-full shadow-lg">
            @if(Auth::user()->profile_photo_path)
            <img
                src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                alt="Foto de Perfil Actual"
            />
            @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            @endif
        </x-base.menu.button>
        <x-base.menu.items class="mt-px w-56 bg-theme-1 text-white">
            <x-base.menu.header class="font-normal">
                <div class="font-medium">{{ Auth::user()->name }} {{ Auth::user()->lastname }}</div>
                <div class="mt-0.5 text-xs text-white/70 dark:text-slate-500">
                    {{ Auth::user()->roles[0]->name }}
                </div>
            </x-base.menu.header>
            <x-base.menu.divider class="bg-white/[0.08]" />
                <x-base.menu.item class="hover:bg-white/5">
                    <a class="mr-3 flex items-center" href="{{ route('profile.edit', ['id' => Auth::user()->id]) }}">
                    <x-base.lucide
                        class="mr-2 h-4 w-4"
                        icon="User"
                    /> Perfil
                </a>
                </x-base.menu.item>
            {{-- <x-base.menu.item class="hover:bg-white/5">
                <x-base.lucide
                    class="mr-2 h-4 w-4"
                    icon="Edit"
                /> Añadir cuenta
            </x-base.menu.item> --}}
            <x-base.menu.item class="hover:bg-white/5">

                <a class="mr-3 flex items-center" href="{{ route('profile.changePassword') }}">
                <x-base.lucide
                    class="mr-2 h-4 w-4"
                    icon="Lock"
                /> Cambiar Contraseña
                </a>
            </x-base.menu.item>
            <x-base.menu.item class="hover:bg-white/5">

                <a class="mr-3 flex items-center" href="{{ route('profile.changeProfilePhoto') }}">
                <x-base.lucide
                    class="mr-2 h-4 w-4"
                    icon="User"
                /> Cambiar Foto Perfil
                </a>
            </x-base.menu.item>
            {{-- <x-base.menu.item class="hover:bg-white/5">
                <x-base.lucide
                    class="mr-2 h-4 w-4"
                    icon="HelpCircle"
                /> Ayuda
            </x-base.menu.item> --}}
            <x-base.menu.divider class="bg-white/[0.08]" />
            <x-base.menu.item class="hover:bg-white/5">
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();"
                   class="flex items-center">
                    <x-base.lucide
                        class="mr-2 h-4 w-4"
                        icon="ToggleRight"
                    />
                    Cerrar sesión
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </x-base.menu.item>

        </x-base.menu.items>
    </x-base.menu>
    <!-- END: Account Menu -->

</div>
<br>
@if(session('success'))
    <div class="text-success">
        {{ session('success') }}
    </div>
@endif
<!-- END: Top Bar -->

@pushOnce('scripts')
    @vite('resources/js/components/themes/rubick/top-bar.js')
@endPushOnce
