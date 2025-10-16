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
                    <div class="col-span-9">
                        <label data-tw-merge for="documentInput" class="inline-block mb-2 group-[.form-inline]:mb-2 group-[.form-inline]:sm:mb-0 group-[.form-inline]:sm:mr-5 group-[.form-inline]:sm:text-right">
                            Numero de documento
                        </label>
                        <input data-tw-merge id="documentInput" type="text" placeholder="Escanee o escriba el número de cédula..." class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 [&amp;[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-green-500 focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&amp;:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10" />
                    </div>
                    <div class="mt-5 sm:ml-20 sm:pl-5 col-span-3">
                        <button data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary" id="searchButton">Buscar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenedor del resultado --}}
    <div id="resultContainer" class="text-center mt-4"></div>
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
                // Tarjeta de usuario (verde)
                const userInfo = `
                    <div class="border rounded-md p-4 mb-4 bg-green-50 border-green-400 text-left shadow-sm">
                        <h4 class="font-semibold text-green-700 text-lg mb-2">👤 Información del usuario</h4>
                        <p><strong>Nombre:</strong> ${data.user.name}</p>
                        <p><strong>Documento:</strong> ${data.user.document_number}</p>
                        ${data.user.email ? `<p><strong>Correo:</strong> ${data.user.email}</p>` : ''}
                        ${data.user.phone ? `<p><strong>Teléfono:</strong> ${data.user.phone}</p>` : ''}
                        ${data.user.address ? `<p><strong>Dirección:</strong> ${data.user.address}</p>` : ''}
                        ${data.user.age ? `<p><strong>Edad:</strong> ${data.user.age}</p>` : ''}
                        <p class="text-slate-500 mt-2 text-sm">Verificación realizada: ${data.checked_at}</p>
                    </div>
                `;

                // Cartas de eventos con colores según estado
                let eventsList = data.events.map(e => {
                    let statusColor = e.is_active_now
                        ? 'text-green-600 bg-green-50 border-green-400'
                        : (e.status_message.includes('aún no está')
                            ? 'text-yellow-600 bg-yellow-50 border-yellow-400'
                            : 'text-red-600 bg-red-50 border-red-400');
                    e.is_active_now ? playSound(true) : playSound(false);

                    return `
                        <div class="border ${statusColor} rounded-md p-4 mb-4 text-left bg-white dark:bg-darkmode-600 shadow-sm transition hover:shadow-md">
                            <div class="flex justify-between items-center mb-2">
                                <h5 class="font-semibold text-slate-800 dark:text-slate-200">${e.name}</h5>
                                <span class="text-sm font-medium ${statusColor} px-3 py-1 rounded-md">${e.status_message}</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-300"><strong>Descripción:</strong> ${e.description}</p>
                            <p class="text-slate-600 dark:text-slate-300"><strong>Fecha:</strong> ${e.date}</p>
                            <p class="text-slate-600 dark:text-slate-300"><strong>Hora inicio:</strong> ${e.start_time}</p>
                            <p class="text-slate-600 dark:text-slate-300"><strong>Hora fin:</strong> ${e.end_time}</p>
                            <p class="text-slate-600 dark:text-slate-300"><strong>Lugar:</strong> ${e.place}</p>
                        </div>
                    `;
                }).join('');

                // Mostrar en el contenedor
                showAlert(
                    'success',
                    'alert-triangle',
                    `${userInfo}<h4 class="font-semibold mb-3 text-left text-slate-700">🎟️ Eventos registrados:</h4>${eventsList}`
                );
            } else {
                showAlert('danger', 'alert-octagon', `❌ ${data.message}`);
                playSound(false);
            }

            input.value = '';
            input.focus();
        })
        .catch(error => {
            console.error(error);
            showAlert('danger', 'alert-octagon', '⚠️ Ocurrió un error al consultar. Intente nuevamente.');
            playSound(false);
        });
    }

    // Permitir Enter (pistola o teclado)
    input.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchByDocument();
        }
    });

    // Botón manual
    button.addEventListener('click', searchByDocument);

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
