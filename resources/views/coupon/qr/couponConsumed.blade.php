@extends('../themes/base')

@section('head')
    <title>PROYECTO EVENTOS</title>
@endsection

@section('content')
    <div @class([
        'p-3 sm:px-8 relative h-screen lg:overflow-hidden bg-primary xl:bg-white dark:bg-darkmode-800 xl:dark:bg-darkmode-600',
        'before:hidden before:xl:block before:content-[\'\'] before:w-[57%] before:-mt-[28%] before:-mb-[16%] before:-ml-[13%] before:absolute before:inset-y-0 before:left-0 before:transform before:rotate-[-4.5deg] before:bg-primary/20 before:rounded-[100%] before:dark:bg-darkmode-400',
        'after:hidden after:xl:block after:content-[\'\'] after:w-[57%] after:-mt-[20%] after:-mb-[13%] after:-ml-[13%] after:absolute after:inset-y-0 after:left-0 after:transform before:rotate-[-4.5deg] after:bg-primary after:rounded-[100%] after:dark:bg-darkmode-700',
    ])>
        <div class="container relative z-10 sm:px-10">
            <div class="block grid-cols-2 gap-4 xl:grid">
                <!-- BEGIN: Event Info -->
                <div class="hidden min-h-screen flex-col xl:flex">
                    <img class="w-6" src="{{ Vite::asset('resources/images/icono.ico') }}" alt="" />
                    <span class="ml-3 text-lg text-white"> FlorezEventos </span>
                    <div class="my-auto">
                        <img class="-intro-x -mt-16 w-1/2" src="{{ Vite::asset('resources/images/illustration.svg') }}" alt="" />
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
                <div class="my-10 flex h-screen py-5 xl:my-0 xl:h-auto xl:py-0">
                    <div class="mx-auto my-auto w-full rounded-md bg-white px-5 py-8 shadow-md dark:bg-darkmode-600 sm:w-3/4 sm:px-8 lg:w-2/4 xl:ml-20 xl:w-auto xl:bg-transparent xl:p-0 xl:shadow-none">
                        <h2 class="intro-x text-center text-2xl font-bold xl:text-left xl:text-3xl">
                            CUPON CONSUMIDO PARA ENTRAR AL EVENTO: {{ $coupon->event->name }}
                        </h2>
                        <p class="intro-x mt-2 text-center text-slate-400 xl:hidden">
                            {{ $coupon->event->description }}
                        </p>
                        @if (session('success'))
                            <div class="intro-x mt-4 alert alert-success">

                            <div class="status-alert bg-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="intro-x mt-4 alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="section box mt-2">
                            <div class="m-3">
                                <h1>Información del ticket</h1>
                                @if($coupon?->ticketType)
                                    <p><strong>Tipo de Ticket:</strong> {{ $coupon->ticketType->name ?? 'N/A' }}</p>
                                    <ul>
                                        @foreach ($coupon->ticketType->features as $feature)
                                            <li>
                                                <strong>{{ $feature->name }}:</strong>
                                                <span>{{ $feature->consumable ? 'Consumible' : 'Acceso' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <div class="status-alert {{ $coupon->is_consumed ? 'bg-danger' : 'bg-success' }} text-white">
                                    <h3>ESTADO DEL CUPON:</h3>
                                    <p>{{ $coupon->is_consumed ? 'CONSUMIDO' : 'NO CONSUMIDO' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: Registration Form -->
            </div>
        </div>
    </div>
@endsection
