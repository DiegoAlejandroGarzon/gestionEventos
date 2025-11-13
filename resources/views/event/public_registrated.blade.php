@extends('../themes/base')

@section('head')
    <title>PROYECTO EVENTOS</title>
@endsection

@section('content')
<style>
    .custom-border {
        border-top: 2px solid #333;
        border-bottom: 2px solid #333;
    }
</style>

@if($event->color_one !== null)
    <style>
        body {
            overflow-x: hidden;
        }
        .bg-color-one {
            --tw-bg-opacity: 1;
            background-color: {{$event->color_one}};
        }
        .bg-color-two {
            --tw-bg-opacity: 1;
            background-color: {{$event->color_two}};
        }
        .before\:bg-color-two\/20::before {
            --tw-bg-opacity: 1;
            background-color: {{$event->color_two}};
        }
        .after\:bg-color-one::after {
            --tw-bg-opacity: 1;
            background-color: {{$event->color_one}};
        }
        @media (max-width: 1280px) {
            .lg\:overflow-hidden {
                overflow: hidden;
                background-color: {{$event->color_one}};
            }
        }
    </style>
    <div @class([
        'p-3 sm:px-8 relative h-screen bg-primary xl:bg-white dark:bg-darkmode-800 xl:dark:bg-darkmode-600',
        'before:hidden before:xl:block before:content-[\'\'] before:w-[57%] before:-mt-[28%] before:-mb-[16%] before:-ml-[13%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[-4.5deg] before:bg-color-two/20 before:rounded-[100%] before:dark:bg-darkmode-400',
        'after:hidden after:xl:block after:content-[\'\'] after:w-[57%] after:-mt-[20%] after:-mb-[13%] after:-ml-[13%] after:absolute after:inset-y-0 after:left-0 after:transform before:rotate-[-4.5deg] after:bg-color-one after:rounded-[100%] after:dark:bg-darkmode-700',
    ])>
@else
    <div @class([
        'p-3 sm:px-8 relative h-screen bg-primary xl:bg-white dark:bg-darkmode-800 xl:dark:bg-darkmode-600',
        'before:hidden before:xl:block before:content-[\'\'] before:w-[57%] before:-mt-[28%] before:-mb-[16%] before:-ml-[13%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[-4.5deg] before:bg-primary/20 before:rounded-[100%] before:dark:bg-darkmode-400',
        'after:hidden after:xl:block after:content-[\'\'] after:w-[57%] after:-mt-[20%] after:-mb-[13%] after:-ml-[13%] after:absolute after:inset-y-0 after:left-0 after:transform before:rotate-[-4.5deg] after:bg-primary after:rounded-[100%] after:dark:bg-darkmode-700',
    ])>
@endif
        <div class="container relative z-10 sm:px-10">
            <div class="block grid-cols-2 gap-4 xl:grid">
                <!-- BEGIN: Event Info -->
                <div class="hidden min-h-screen flex-col xl:flex">
                    <img class="w-6" src="{{ Vite::asset('resources/images/logo.svg') }}" alt="" />
                    <span class="ml-3 text-lg text-white">ValiApp</span>
                    <div class="my-auto">
                        @if ($event->header_image_path)
                            <img class="-intro-x -mt-16 w-1/2" src="{{ asset('storage/' . $event->header_image_path) }}" alt="Imagen del evento" />
                        @else
                            <img class="-intro-x -mt-16 w-1/2" src="{{ Vite::asset('resources/images/illustration.svg') }}" alt="" />
                        @endif
                        <div class="-intro-x mt-10 text-4xl font-medium leading-tight text-white">
                            PROYECTO EVENTOS
                        </div>
                        <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">
                            Registrar eventos y llevar su gestión
                        </div>
                    </div>
                </div>
                <!-- END: Event Info -->

            <!-- BEGIN: Registration Form -->
            <div class="my-10 flex h-screen py-5 xl:my-0 xl:h-auto xl:py-0 text-center">
                <div class="mx-auto my-auto w-full rounded-md bg-white px-5 py-8 shadow-md dark:bg-darkmode-600 sm:w-3/4 sm:px-8 lg:w-2/4 xl:ml-20 xl:w-auto xl:bg-transparent xl:p-0 xl:shadow-none">
                    <h2 class="intro-x text-center text-2xl font-bold xl:text-left xl:text-3xl text-success">
                        {{ $message }}
                    </h2>
                    @if ($event->generate_qr)
                        <h2 class="intro-x text-center text-2xl font-bold xl:text-left xl:text-3xl">
                            Se ha generado el siguiente código QR para el ingreso al evento:
                            <br> {{ $event->name }}
                        </h2>

                        <div class="block xl:hidden">
                            @if ($event->header_image_path)
                                <img src="{{ asset('storage/' . $event->header_image_path) }}" alt="Imagen del evento" />
                            @else
                                <img src="{{ Vite::asset('resources/images/illustration.svg') }}" alt="" />
                            @endif
                        </div>

                        <p class="intro-x mt-2 text-center text-slate-400 xl:hidden">
                            {{ $event->description }}
                        </p>

                        <p><strong>Recuerda guardar el código QR para poder acceder al evento:</strong></p>
                        <p><strong>Código QR:</strong></p>
                        <p>{{ $userName }}</p>
                        <div class="custom-border">
                            <div class="mt-4 mb-4 inline-block" id="qrContainer">
                                {{ $qrcode }}
                            </div>
                        </div>
                        <p class="intro-x mt-2 text-center">
                            <b>Tu entrada para el evento</b>
                        </p>
                        <p class="intro-x mt-2 text-center">
                            Escanee el código QR para entrar al evento.
                        </p>

                        <button id="downloadQRCode" class="mt-4 inline-block bg-success text-white py-2 px-4 rounded">
                            Descargar Código QR PNG
                        </button>

                        <a href="{{ route('event.download.pdf', ['public_link' => $event->public_link, 'id' => $idEventAssistant]) }}"
                           class="mt-4 inline-block bg-success text-white py-2 px-4 rounded" target="_blank">
                            Descargar Boleta en PDF
                        </a>
                    @else
                        <p class="intro-x mt-5 text-center text-slate-500">
                            Este evento no genera códigos QR para el acceso.
                        </p>
                        <p class="intro-x mt-5 text-center text-slate-500">
                            Recuerda que el Ingreso va a ser con tu Documento de Identidad.
                        </p>
                        <a href="{{ route('event.download.pdf', ['public_link' => $event->public_link, 'id' => $idEventAssistant]) }}"
                           class="mt-4 inline-block bg-success text-white py-2 px-4 rounded" target="_blank">
                            Descargar Boleta en PDF
                        </a>
                    @endif
                </div>
            </div>
            <!-- END: Registration Form -->
        </div>
    </div>
</div>
@if ($event->generate_qr)
    <script>
        document.getElementById('downloadQRCode').addEventListener('click', function() {
            const svg = document.getElementById('qrContainer').querySelector('svg');
            const serializer = new XMLSerializer();
            const svgString = serializer.serializeToString(svg);
            const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);

            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                const width = svg.getAttribute('width') || 300; // Ajustar según el SVG
                const height = svg.getAttribute('height') || 300; // Ajustar según el SVG
                canvas.width = width;
                canvas.height = height;

                context.clearRect(0, 0, width, height); // Limpiar el canvas
                context.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function(blob) {
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = 'codigo_qr_evento_{{ $event->id }}.png'; // Nombre del archivo
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(a.href); // Liberar memoria
                }, 'image/png');
            };
            img.src = url; // Establecer la fuente de la imagen
        });
    </script>
@endif
@endsection
