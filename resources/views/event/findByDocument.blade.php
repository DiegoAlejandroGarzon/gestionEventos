@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Eventos</title>
@endsection

@section('subcontent')
<div class="container py-5">
    <h2 class="intro-y mt-10 text-lg font-medium text-center">Verificación de Entrada por Cédula</h2>

    {{-- Input para escanear o digitar la cédula --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <div class="input-group input-group-lg">
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-12">
                        <label data-tw-merge for="documentInput" class="inline-block mb-2 group-[.form-inline]:mb-2 group-[.form-inline]:sm:mb-0 group-[.form-inline]:sm:mr-5 group-[.form-inline]:sm:text-right">
                            Numero de documento
                        </label>
                        <input data-tw-merge id="documentInput" type="text" placeholder="Escanee o escriba el número de cédula..." class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 [&amp;[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-green-500 focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&amp;:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenedor del resultado --}}
    <div id="resultContainer" class="mt-4 px-3 sm:px-0 w-full max-w-4xl mx-auto"></div>
</div>

<!-- en resources/views/layouts/app.blade.php o tu layout principal -->
<script src="https://unpkg.com/lucide@^0.267.0/dist/lucide.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('documentInput');
    const resultDiv = document.getElementById('resultContainer');
    const button = document.getElementById('searchButton');

    input.addEventListener('focus', function () {
        input.classList.remove('bg-slate-200');
        input.classList.add('bg-green-100');
    });

    input.addEventListener('blur', function () {
        input.classList.remove('bg-green-100');
        input.classList.add('bg-slate-200');
    });
    // Función para mostrar alertas con tu estilo
    function showAlert(type, icon, message) {
        const alertHTML = `
            <div role="alert"
                class="alert relative border rounded-md px-5 py-4 bg-${type} border-${type} bg-opacity-20 border-opacity-5 text-${type}
                dark:border-${type} dark:border-opacity-20 mb-2 flex items-center"
            >
                <i data-lucide="${icon}" class="stroke-1.5 w-6 h-6 mr-2"></i>
                ${message}
            </div>
        `;
        resultDiv.innerHTML = alertHTML;

        // Solo crear íconos si la librería está disponible
        if (typeof window.lucide !== 'undefined' && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    // Función de búsqueda
    function searchByDocument() {
        const documentNumber = input.value.trim();

        if (!documentNumber) {
            showAlert('warning', 'alert-circle', '⚠️ Por favor, escanee o escriba una cédula válida.');
            return;
        }

        // Limpia el resultado anterior
        resultDiv.innerHTML = '<div class="text-slate-500">🔎 Buscando...</div>';

        // Llamada AJAX
        fetch('{{ route('event.findByDocumentStore') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ document_number: documentNumber })
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

                // Render: eventos arriba, info usuario abajo
                resultDiv.innerHTML = `
                    <div class="mb-3">${eventsWrapper}</div>
                    <div>${userInfo}</div>
                `;

                // render lucide icons inside resultDiv if available
                if (typeof window.lucide !== 'undefined' && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }

                // play sound once if any event is active
                const anyActive = data.events.some(ev => ev.is_active_now);
                playSound(anyActive);
            } else {
                showAlert('danger', 'alert-octagon', `❌ ${data.message}`);
                playSound(false);
            }

            setTimeout(() => {
                input.value = '';
                input.focus();
            }, 300);
        })
        .catch(error => {
            console.error(error);
            showAlert('danger', 'alert-octagon', '⚠️ Ocurrió un error al consultar. Intente nuevamente.');
            playSound(false);
        });
    }

    // Detección automática: cuando se detecta un número completo, se lanza la búsqueda
    let searchTimeout;

    input.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const value = input.value.trim();

        // Si tiene más de 5 caracteres, lanza búsqueda automáticamente (ajusta si deseas)
        if (value.length >= 5) {
            searchTimeout = setTimeout(() => {
                searchByDocument();
            }, 600); // espera 0.6s después de dejar de escribir o escanear
        }
    });


    // 🔊 Sonidos (éxito/error)
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
