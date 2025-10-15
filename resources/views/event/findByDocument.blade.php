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
                <input
                    type="text"
                    id="documentInput"
                    class="form-control text-center"
                    placeholder="Escanee o escriba el número de cédula..."
                    autofocus
                >
                <button id="searchButton" class="btn btn-primary">Buscar</button>
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
                let eventsList = data.events.map(e => `<li class="ml-5 list-disc">${e}</li>`).join('');

                showAlert(
                    'success',
                    'alert-triangle',
                    `<strong>${data.user_name}</strong> está registrada en los siguientes eventos:<ul class="mt-2 text-left">${eventsList}</ul>`
                );

                playSound(true);
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
