@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Eventos</title>
@endsection

@section('subcontent')
<div class="container py-5">
    <h2 class="intro-y mt-10 text-lg font-medium text-center">Verificación de Entrada por Cédula</h2>
    <div class="mt-5 box">
        <!-- Selector de Evento -->
        <div class="m-2">
            <x-base.form-label for="eventSelect">Seleccionar Evento Activo</x-base.form-label>
            <x-base.tom-select id="eventSelect" name="eventSelect" class="w-full">
                <option value="">Seleccione un evento</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}">
                        {{ $event->name }}
                    </option>
                @endforeach
            </x-base.tom-select>
        </div>

        <!-- Selector de Cédula -->
        <div class="m-2">
            <x-base.form-label for="documentSelect">Buscar Cédula</x-base.form-label>
            <x-base.tom-select id="documentSelect" name="documentSelect" class="w-full" placeholder="Escriba al menos 4 dígitos..."></x-base.tom-select>
        </div>
    </div>

    {{-- Contenedor del resultado --}}
    <div id="resultContainer" class="mt-4 px-3 sm:px-0 w-full max-w-4xl mx-auto"></div>
</div>

<script src="https://unpkg.com/lucide@^0.267.0/dist/lucide.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const documentSelect = document.getElementById('documentSelect');
    const eventSelect = document.getElementById('eventSelect');
    const resultDiv = document.getElementById('resultContainer');
    if (documentSelect.tomselect) {
        documentSelect.tomselect.destroy();
    }

    // 🔹 Autocompletado con TomSelect
    const cedulaSelect = new TomSelect(documentSelect, {
        create: false,
        maxOptions: 10,
        valueField: 'document_number',
        labelField: 'display',
        searchField: 'document_number',
        load: function (query, callback) {
            if (query.length < 4) return callback();

            const eventId = eventSelect.value;
            if (!eventId) return callback();

            // 🔹 limpiar resultados previos del autocomplete
            this.clearOptions();
            fetch(`{{ route('event.buscarCedulas') }}?event_id=${eventId}&query=${query}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mostrar “documento — nombre”
                        data.results.forEach(r => {
                            r.display = `${r.document_number} — ${r.name} ${r.lastname}`;
                        });
                        callback(data.results);

                        // ✅ Autoseleccionar si hay solo una coincidencia
                        if (data.results.length === 1) {
                            cedulaSelect.addItem(data.results[0].document_number);
                            executeVerification(data.results[0].document_number);
                        }
                    } else {
                        callback();
                    }
                })
                .catch(() => callback());
        },
        onChange: function (value) {
            if (value) {
                executeVerification(value);
            }
        },
    });

    // 🚫 Deshabilitar select de cédulas al inicio
    cedulaSelect.disable();

    // 🎯 Escuchar cambios del select de evento
    eventSelect.addEventListener('change', function () {
        cedulaSelect.clear(true);
        cedulaSelect.clearOptions();
        resultDiv.innerHTML = '';

        if (this.value) {
            cedulaSelect.enable();
            cedulaSelect.focus();
        } else {
            cedulaSelect.disable();
        }
    });



    // 🔎 Verificación de ingreso
    function executeVerification(documentNumber) {
        const eventId = eventSelect.value;
        if (!eventId) {
            showAlert('warning', 'alert-circle', '⚠️ Seleccione primero un evento activo.');
            return;
        }

        resultDiv.innerHTML = '<div class="text-slate-500">🔎 Verificando...</div>';

        fetch('{{ route('event.findByDocumentStore') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                document_number: documentNumber,
                event_id: eventId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let firstEvent = data.events[0];
                let statusColor = firstEvent.is_active_now
                    ? 'text-green-600 border-green-400 bg-green-50'
                    : (firstEvent.status_message.includes('aún no está')
                        ? 'text-yellow-600 border-yellow-400 bg-yellow-50'
                        : 'text-red-600 border-red-400 bg-red-50');


                // Tarjeta de usuario con el mismo color de estado
                const userInfo = `
                    <div class="border rounded-md p-4 mb-4 ${statusColor} text-left shadow-sm w-full transition">
                        <h4 class="font-semibold text-lg mb-2">👤 Información del usuario</h4>
                        <p class="text-sm"><strong>Nombre:</strong> ${data.user.name}</p>
                        <p class="text-sm"><strong>Documento:</strong> ${data.user.document_number}</p>
                        ${data.user.email ? `<p class="text-sm"><strong>Correo:</strong> ${data.user.email}</p>` : ''}
                        ${data.user.phone ? `<p class="text-sm"><strong>Teléfono:</strong> ${data.user.phone}</p>` : ''}
                        ${data.user.address ? `<p class="text-sm"><strong>Dirección:</strong> ${data.user.address}</p>` : ''}
                        ${data.user.age ? `<p class="text-sm"><strong>Edad:</strong> ${data.user.age}</p>` : ''}
                        <p class="mt-2 text-xs text-slate-700 dark:text-slate-400"><strong>Verificación:</strong> ${data.checked_at}</p>
                    </div>
                `;

                // Cartas de eventos con clases responsivas y layout móvil optimizado
                let eventsList = data.events.map(e => {
                    let statusColor = e.is_active_now
                        ? 'text-green-600 border-green-400'
                        : (e.status_message.includes('aún no está')
                            ? 'text-yellow-600 border-yellow-400'
                            : 'text-red-600 border-red-400');

                    // Si tiene un evento activo, marcar como ingresado
                        if (e.is_active_now) {
                            playSound(true);
                            // Petición para registrar ingreso

                            fetch(`{{ url('event-assistant-2') }}/${e.event_assistant_id}/register-entry`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                const alertContainerId = 'alertContainer';
                                let alertContainer = document.getElementById(alertContainerId);

                                // Si no existe, crearlo justo debajo del input
                                if (!alertContainer) {
                                    alertContainer = document.createElement('div');
                                    alertContainer.id = alertContainerId;
                                    alertContainer.classList.add('mt-4', 'max-w-4xl', 'mx-auto', 'text-center', 'register-entry-message');
                                    resultDiv.parentNode.insertBefore(alertContainer, resultDiv);
                                }

                                // Mostrar mensaje sin borrar resultDiv
                                if (data.success) {
                                    if (data.error) {
                                        alertContainer.innerHTML = `
                                            <div class="alert border border-red-400 bg-red-50 text-red-600 rounded-md p-3 flex items-center justify-center">
                                                <i data-lucide="alert-octagon" class="w-5 h-5 mr-2"></i>
                                                ${data.error}
                                            </div>`;
                                    } else if (data.warning) {
                                        alertContainer.innerHTML = `
                                            <div class="alert border border-yellow-400 bg-yellow-50 text-yellow-600 rounded-md p-3 flex items-center justify-center">
                                                <i data-lucide="alert-triangle" class="w-5 h-5 mr-2"></i>
                                                ${data.warning}
                                            </div>`;
                                    } else {
                                        alertContainer.innerHTML = `
                                            <div class="alert border border-green-400 bg-green-50 text-green-600 rounded-md p-3 flex items-center justify-center">
                                                <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                                                ${data.message || '✅ Ingreso registrado correctamente.'}
                                            </div>`;
                                    }
                                } else {
                                    alertContainer.innerHTML = `
                                        <div class="alert border border-red-400 bg-red-50 text-red-600 rounded-md p-3 flex items-center justify-center">
                                            <i data-lucide="alert-octagon" class="w-5 h-5 mr-2"></i>
                                            ${data.message || '⚠️ No se pudo registrar el ingreso.'}
                                        </div>`;
                                }

                                // Renderiza íconos Lucide
                                if (typeof window.lucide !== 'undefined' && typeof window.lucide.createIcons === 'function') {
                                    window.lucide.createIcons();
                                }
                            })

                        }else{
                            playSound(false);
                        }

                    return `
                        <article class="w-full bg-white dark:bg-darkmode-600 border ${statusColor} rounded-md p-4 mb-4 shadow-sm flex flex-col sm:flex-row gap-3">
                            <div class="flex-none w-full sm:w-1/3">
                                <h5 class="font-semibold text-slate-800 dark:text-slate-200 text-base">${e.name}</h5>
                                <p class="text-xs mt-1 ${statusColor} font-medium">${e.status_message}</p>
                            </div>
                            <div class="flex-1 text-sm text-slate-600 dark:text-slate-300">
                                <p class="mb-1"><strong>Fecha:</strong> ${e.date}</p>
                                <p class="mb-1"><strong>Hora:</strong> ${e.start_time} — ${e.end_time}</p>
                                <p class="mb-0"><strong>Lugar:</strong> ${e.place}</p>
                            </div>
                        </article>
                    `;
                }).join('');

                // Layout final: eventos primero (en una columna en móvil, dos en pantallas mayores si quieres)
                const eventsWrapper = `<div class="grid grid-cols-1 gap-4">${eventsList}</div>`;

                // 👶 Sección de menores (si existen)
                let minorsSection = "";
                const minors = firstEvent.minors || [];

                if (minors.length > 0) {
                    minorsSection = `
                        <div class="border rounded-md p-4 mb-4 bg-slate-50 dark:bg-darkmode-700 shadow-sm">
                            <h4 class="font-semibold text-lg mb-2 text-slate-800 dark:text-slate-100">👶 Menores asociados</h4>
                            <ul class="list-disc ml-5 text-sm text-slate-700 dark:text-slate-300">
                                ${minors.map(m => `
                                    <li><strong>${m.full_name}</strong> — ${m.age} años</li>
                                `).join('')}
                            </ul>
                        </div>
                    `;
                }

                // Render: eventos, menores, usuario
                resultDiv.innerHTML = `
                    <div class="mb-3">${eventsWrapper}</div>
                    ${minorsSection}
                    <div>${userInfo}</div>
                `;


                // render lucide icons inside resultDiv if available
                if (typeof window.lucide !== 'undefined' && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }


            } else {
                showAlert('danger', 'alert-octagon', `❌ ${data.message}`);
                playSound(false);
            }

            setTimeout(() => {
                cedulaSelect.clear(true);      // limpia el valor
                cedulaSelect.clearOptions();   // borra las opciones cargadas
                cedulaSelect.focus();          // vuelve a enfocar el campo
            }, 300);
        })
        .catch(error => {
            console.error(error);
            showAlert('danger', 'alert-octagon', '⚠️ Ocurrió un error al consultar. Intente nuevamente.');
            playSound(false);
        });
    }

    // 🔊 Sonido de confirmación
    function playSound(success) {
        const audio = new Audio(success
            ? 'https://actions.google.com/sounds/v1/cartoon/wood_plank_flicks.ogg'
            : 'https://actions.google.com/sounds/v1/cartoon/concussive_hit_guitar_boing.ogg'
        );
        audio.play();
    }
});
</script>
@endsection
