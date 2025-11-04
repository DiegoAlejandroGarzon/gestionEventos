@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Eventos</title>
@endsection

@section('subcontent')
<div class="container py-5">
    <h2 class="intro-y mt-10 text-lg font-medium text-center">Verificación de Entrada por Cédula</h2>

    <!-- Barra superior: selector de evento y acceso al modo offline (modal) -->
    <div class="mt-4 p-3 box flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="w-full sm:w-2/3">
            <x-base.form-label for="eventSelect">Seleccionar Evento Activo</x-base.form-label>
            <x-base.tom-select id="eventSelect" name="eventSelect" class="w-full">
                <option value="">Seleccione un evento</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}">{{ $event->name }}</option>
                @endforeach
            </x-base.tom-select>
            <!-- Estado de la base local -->
            <p id="localDbStatus" class="text-sm text-slate-500 mt-2">Base local: comprobando...</p>
        </div>

        <div class="w-full sm:w-1/3 flex items-end justify-end">
            <!-- Botón que abre modal de modo offline -->
            <button id="openOfflineModalBtn" type="button" class="btn btn-secondary w-full sm:w-auto box p-2">💾 Modo Offline</button>
        </div>
    </div>

    <!-- Modal: Modo Offline -->
    <div id="offlineModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50" id="offlineModalBackdrop"></div>
        <div class="bg-white dark:bg-darkmode-700 rounded-lg shadow-lg p-6 z-10 w-full max-w-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Modo Offline — Descargar registros</h3>
                <button id="closeOfflineModalBtn" class="text-slate-500">✕</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>
                    <x-base.form-label for="offlineDate">Seleccionar Fecha</x-base.form-label>
                    <input type="date" id="offlineDate" class="form-control w-full">
                </div>
                <div class="flex items-end">
                    <button id="downloadLocalBtn" class="btn btn-primary w-full box p-2" type="button">💾 Descargar Registros de Forma Local</button>
                </div>
                <div class="flex items-end mt-2 sm:mt-0 sm:ml-2">
                    <button id="clearLocalBtn" class="btn btn-danger w-full box p-2" type="button">🗑️ Vaciar base local</button>
                </div>
             </div>
            <p id="offlineStatus" class="text-sm text-slate-500 mt-2"></p>
        </div>
    </div>

    <div class="mt-5 p-3 box">
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
document.addEventListener('DOMContentLoaded', async  function () {
    const documentSelect = document.getElementById('documentSelect');
    const eventSelect = document.getElementById('eventSelect');
    const resultDiv = document.getElementById('resultContainer');
    if (documentSelect.tomselect) {
        documentSelect.tomselect.destroy();
    }

    // =======================================
    // 🧠 IndexedDB — Modo Offline
    // =======================================
    const DB_NAME = 'eventVerificationDB';
    const STORE_NAME = 'assistants';
    // incrementar versión para actualizar esquema y permitir múltiples registros por documento
    const DB_VERSION = 2;
     const SECURITY_KEY = "123"; // Clave de seguridad local
     let db = null;

     async function getDB() {
         if (db) return db; // si ya está abierta
         db = await initDB(); // si no, inicializar
         return db;
     }

     // Inicializar base local
     function initDB() {
         return new Promise((resolve, reject) => {
             const request = indexedDB.open(DB_NAME, DB_VERSION);
             request.onupgradeneeded = function (e) {
                 const database = e.target.result;
                 // Si existe con schema antiguo, eliminar y recrear con nuevo keyPath 'id'
                 if (database.objectStoreNames.contains(STORE_NAME)) {
                     try { database.deleteObjectStore(STORE_NAME); } catch (err) { console.warn('deleteObjectStore', err); }
                 }
                 // Crear object store con keyPath 'id' (id único por evento+ticket+document)
                 const store = database.createObjectStore(STORE_NAME, { keyPath: 'id' });
                 // Índices para búsqueda eficiente
                 store.createIndex('by_document', 'document_number', { unique: false });
                 store.createIndex('by_event_document', ['event_id', 'document_number'], { unique: false });
                 store.createIndex('by_event', 'event_id', { unique: false });
             };
             request.onsuccess = function (e) {
                 console.log("✅ IndexedDB inicializada correctamente");
                 resolve(e.target.result);
             };
             request.onerror = () => reject('❌ Error inicializando IndexedDB');
         });
     }

    // 🔹 Autocompletado con TomSelect
    const cedulaSelect = new TomSelect(documentSelect, {
        create: false,
        maxOptions: 10,
        valueField: 'document_number',
        labelField: 'display',
        searchField: 'document_number',
        load: async function (query, callback) {
            if (query.length < 4) return callback();

            const eventId = eventSelect.value;
            if (!eventId) return callback();

            this.clearOptions();
            console.log("🔍 Buscando cédula:", query);
            const localMatches = await searchLocalByCedula(eventId, query);
            // console.log("🔍 Resultados locales para", query, localMatches);

            if (localMatches.length > 0) {
                console.log("✅ Resultados obtenidos desde IndexedDB:", localMatches);
                localMatches.forEach(r => r.display = `${r.document_number} — ${r.name}`);
                callback(localMatches);// --- LÓGICA PARA AUTOSERLECCIÓN INTELIGENTE ---
                const distinctDocs = [...new Set(localMatches.map(r => r.document_number))];

                // 🟢 Si todos los resultados pertenecen a la misma cédula, autoseleccionamos
                if (distinctDocs.length === 1) {
                    const docNumber = distinctDocs[0];
                    const recordsForDoc = localMatches.filter(r => r.document_number === docNumber);

                    // --- Opción 1: solo un registro -> seleccionar directamente
                    if (recordsForDoc.length === 1) {
                        const unico = recordsForDoc[0];
                        setTimeout(() => {
                            try {
                                cedulaSelect.clear(true);
                                cedulaSelect.addItem(unico.document_number);
                                verifyDocumentOfflineFirst(unico.document_number);
                            } catch (e) { console.warn('Auto-selección local falló', e); }
                        }, 100);
                        return;
                    }

                    // --- Opción 2: varios registros (mismo documento pero distintos tickets)
                    // verificamos si alguno está activo por horario
                    const now = new Date();
                    const activeCandidates = recordsForDoc.filter(rec => {
                        try {
                            const ticket = rec.ticket || {};
                            if (!ticket.entry_date) return false;
                            const start = ticket.entry_start_time ? new Date(`${ticket.entry_date}T${ticket.entry_start_time}`) : null;
                            const end   = ticket.entry_end_time   ? new Date(`${ticket.entry_date}T${ticket.entry_end_time}`)   : null;
                            return start && end && now >= start && now <= end;
                        } catch {
                            return false;
                        }
                    });

                    // Si hay uno activo, seleccionar ese
                    if (activeCandidates.length === 1) {
                        const activo = activeCandidates[0];
                        setTimeout(() => {
                            try {
                                cedulaSelect.clear(true);
                                cedulaSelect.addItem(activo.document_number);
                                verifyDocumentOfflineFirst(activo.document_number);
                            } catch (e) { console.warn('Auto-selección local por horario falló', e); }
                        }, 100);
                        return;
                    }

                    // Si todos son de la misma cédula pero ninguno está activo, igual seleccionamos el primero
                    // (opcional: podrías abrir un modal para mostrar los tickets disponibles)
                    if (recordsForDoc.length > 1) {
                        const primero = recordsForDoc[0];
                        setTimeout(() => {
                            try {
                                cedulaSelect.clear(true);
                                cedulaSelect.addItem(primero.document_number);
                                verifyDocumentOfflineFirst(primero.document_number);
                            } catch (e) { console.warn('Auto-selección local múltiple falló', e); }
                        }, 100);
                        return;
                    }
                }

                // 🔸 Si hay más de una cédula distinta, no se autoselecciona
                console.log("ℹ️ Múltiples documentos distintos en los resultados, no se autoselecciona.");
                return;

            }


            fetch(`{{ route('event.buscarCedulas') }}?event_id=${eventId}&query=${query}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.results.forEach(r => {
                            r.display = `${r.document_number} — ${r.name} ${r.lastname}`;
                        });
                        callback(data.results);

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
            console.log("verificando offline si esta:", value);
            if (value) {
                verifyDocumentOfflineFirst(value);
            }
        },
        onFocus: function () {
            // aplicar estilo verde al control visual de TomSelect
            try {
                const control = this.wrapper || this.control || (this.input && this.input.closest('.ts-control')) || this.input;
                // intentar encontrar el wrapper correcto
                const target = control && (control.closest ? (control.closest('.ts-control') || control) : control);
                if (target && target.classList) {
                    // clases Tailwind esperadas
                    target.classList.add('ring-2', 'ring-green-400', 'border-green-400', 'bg-success', 'bg-opacity-20');
                    // fallback inline por si las clases no aplican por especificidad
                    target.style.boxShadow = '0 0 0 4px rgba(34,197,94,0.12)';
                    target.style.borderColor = '#34D399';
                }
            } catch (e) { console.warn(e); }
        },
        onBlur: function () {
            try {
                const control = this.wrapper || this.control || (this.input && this.input.closest('.ts-control')) || this.input;
                const target = control && (control.closest ? (control.closest('.ts-control') || control) : control);
                if (target && target.classList) {
                    target.classList.remove('ring-2', 'ring-green-400', 'border-green-400', 'bg-success', 'bg-opacity-20');
                    // limpiar estilos inline de fallback
                    target.style.boxShadow = '';
                    target.style.borderColor = '';
                }
            } catch (e) { console.warn(e); }
        }
    });

    // 🚫 Deshabilitar select de cédulas al inicio
    const openOfflineModalBtn = document.getElementById('openOfflineModalBtn');
    cedulaSelect.disable();
    // Deshabilitar botón Offline hasta que se seleccione un evento
    openOfflineModalBtn.disabled = true;
    openOfflineModalBtn.classList.add('opacity-50', 'cursor-not-allowed');
    openOfflineModalBtn.setAttribute('aria-disabled', 'true');

    // Actualizar estado de la base local al iniciar
    // try { updateLocalDbStatus(); } catch (e) { console.warn('updateLocalDbStatus init error', e); }
    getDB().then(() => updateLocalDbStatus());

    // 🎯 Escuchar cambios del select de evento
    eventSelect.addEventListener('change', function () {
        cedulaSelect.clear(true);
        cedulaSelect.clearOptions();
        resultDiv.innerHTML = '';

        if (this.value) {
            cedulaSelect.enable();
            // habilitar botón offline
            openOfflineModalBtn.disabled = false;
            openOfflineModalBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            openOfflineModalBtn.removeAttribute('aria-disabled');
            cedulaSelect.focus();
        } else {
            cedulaSelect.disable();
            // mantener botón offline deshabilitado
            openOfflineModalBtn.disabled = true;
            openOfflineModalBtn.classList.add('opacity-50', 'cursor-not-allowed');
            openOfflineModalBtn.setAttribute('aria-disabled', 'true');
        }
    });

    // Abrir y cerrar modal offline
    const closeOfflineModalBtn = document.getElementById('closeOfflineModalBtn');
    const offlineModal = document.getElementById('offlineModal');
    const offlineModalBackdrop = document.getElementById('offlineModalBackdrop');

    function showOfflineModal() { offlineModal.classList.remove('hidden'); }
    function hideOfflineModal() { offlineModal.classList.add('hidden'); }

    openOfflineModalBtn.addEventListener('click', showOfflineModal);
    closeOfflineModalBtn.addEventListener('click', hideOfflineModal);
    offlineModalBackdrop.addEventListener('click', hideOfflineModal);

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
                // 👥 Información del guardián (si existe)
                let guardianInfo = '';
                const firstEventGuardian = data.events.find(e => e.guardian); // toma el primero con guardián

                if (firstEventGuardian && firstEventGuardian.guardian) {
                    const g = firstEventGuardian.guardian;
                    guardianInfo = `
                        <div class="border rounded-md p-4 mb-4 text-left shadow-sm w-full transition bg-blue-50 border-blue-300">
                            <h4 class="font-semibold text-lg mb-2 text-blue-700">👥 Acompañante</h4>
                            <p class="text-sm"><strong>Nombre:</strong> ${g.name}</p>
                            <p class="text-sm"><strong>Documento:</strong> ${g.document_number}</p>
                        </div>
                    `;
                }


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
                    ${guardianInfo}
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


    // Guardar registros en local
    async function saveToLocal(records) {
        const dbInstance = await getDB();
        return new Promise((resolve) => {
            const tx = dbInstance.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);

            store.clear(); // Limpiar antes de guardar

            // Asegurarse de que cada registro tenga un id único para no sobrescribir
            records.forEach(r => {
                // intentar localizar ticket id en distintas estructuras
                const ticketId = r.ticket?.id ?? r.ticket_id ?? r.ticket?.ticket_id ?? '0';
                r.id = `${r.event_id}_${ticketId}_${r.document_number}`;
                store.put(r);
            });

            tx.oncomplete = () => resolve(true);
            tx.onerror = () => resolve(false);
        });
    }

    // Buscar en local
    async function findInLocal(documentNumber) {
        const dbInstance = await getDB();
        return new Promise((resolve) => {
            const tx = dbInstance.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            // Usar índice compuesto [event_id, document_number] para obtener el registro correcto
            try {
                const index = store.index('by_event_document');
                const eventId = eventSelect.value || null;
                if (!eventId) return resolve(null);
                const req = index.get([eventId, documentNumber]);
                req.onsuccess = () => resolve(req.result);
                req.onerror = () => resolve(null);
            } catch (e) {
                // Fallback: buscar manualmente
                const req = store.openCursor();
                req.onsuccess = (ev) => {
                    const cursor = ev.target.result;
                    if (cursor) {
                        const value = cursor.value;
                        if (value.document_number == documentNumber && value.event_id == eventSelect.value) {
                            resolve(value);
                            return;
                        }
                        cursor.continue();
                    } else {
                        resolve(null);
                    }
                };
                req.onerror = () => resolve(null);
            }
        });
    }

    async function searchLocalByCedula(eventId, query) {
        const dbInstance = await getDB()
        return new Promise((resolve) => {
            const tx = dbInstance.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const results = [];

            const req = store.openCursor();
            req.onsuccess = (e) => {
                const cursor = e.target.result;
                if (cursor) {
                    const value = cursor.value;
                    if (
                        value.document_number &&
                        value.document_number.startsWith(query) &&
                        value.event_id == eventId
                    ) {
                        results.push(value);
                    }
                    cursor.continue();
                } else {
                    resolve(results);
                }
            };
            req.onerror = () => resolve([]);
        });
    }

    // Descargar registros desde el servidor
    async function downloadLocalData() {
        console.log("⬇️ Descargando registros para modo offline...");
        const eventId = eventSelect.value;
        const date = document.getElementById('offlineDate').value;
        const statusEl = document.getElementById('offlineStatus');

        if (!eventId || !date) {
            statusEl.textContent = "⚠️ Debe seleccionar un evento y una fecha.";
            return;
        }

        const key = prompt("Ingrese la clave de seguridad para continuar:");
        if (key !== SECURITY_KEY) {
            alert("❌ Clave incorrecta.");
            return;
        }

        statusEl.textContent = "⏳ Descargando registros...";

        // Reiniciar DB
        indexedDB.deleteDatabase(DB_NAME);

        await initDB();

        fetch(`{{ route('event.getLocalRecords') }}?event_id=${eventId}&date=${date}`)
            .then(res => res.json())
            .then(async data => {
                if (data.success) {
                    await saveToLocal(data.records);
                    statusEl.textContent = `✅ ${data.records.length} registros guardados localmente.`;

                    const saved = await getAllFromLocal();
                    console.log("📦 Registros actualmente en IndexedDB:", saved);
                    // actualizar indicador visual
                    try { updateLocalDbStatus(); } catch(e){console.warn(e)}
                } else {
                    statusEl.textContent = "⚠️ No se pudo descargar la información.";
                }
            })
            .catch(() => {
                statusEl.textContent = "❌ Error de conexión con el servidor.";
            });
    }

    async function getAllFromLocal() {
        const dbInstance = await getDB();
        return new Promise((resolve) => {
            const tx = dbInstance.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const req = store.openCursor();
            const results = [];

            req.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    results.push(cursor.value);
                    cursor.continue();
                } else {
                    resolve(results);
                }
            };
            req.onerror = () => {
                console.error("❌ Error al leer la base local");
                resolve([]);
            };
        });
    }

    // Actualiza el indicador de la base local (presencia y número de registros)
    async function updateLocalDbStatus() {
        const statusEl = document.getElementById('localDbStatus');
        if (!statusEl) return;

        try {
            // 🧠 Asegura que la base esté inicializada antes de intentar leer
            const dbInstance = await getDB();
            if (!dbInstance) throw new Error('DB no inicializada');

            const records = await getAllFromLocal();

            // 🧩 Filtrar documentos únicos por número de cédula
            const uniqueDocs = new Set(records.map(r => r.document_number));
            const uniqueCount = uniqueDocs.size;

            if (uniqueCount > 0) {
                statusEl.textContent = `Base local: ${uniqueCount} registro(s) únicos disponibles`;
                statusEl.classList.remove('text-slate-500');
                statusEl.classList.add('text-green-600');
            } else {
                statusEl.textContent = 'Base local: sin registros';
                statusEl.classList.remove('text-green-600');
                statusEl.classList.add('text-slate-500');
            }
        } catch (e) {
            console.warn("❌ Error al actualizar estado local:", e);
            statusEl.textContent = 'Base local: error de lectura';
            statusEl.classList.remove('text-green-600');
            statusEl.classList.add('text-red-500');
            console.error("Detalles del error IndexedDB:", e);
        }
    }

    // Asociar al botón
    document.getElementById('downloadLocalBtn').addEventListener('click', downloadLocalData);
    // Asociar botón para vaciar la base local
    document.getElementById('clearLocalBtn').addEventListener('click', clearLocalData);

    // =======================================
    // 🔍 Verificación: primero local, luego servidor
    // =======================================

    // Vaciar la base local IndexedDB
    async function clearLocalData() {
        const confirmClear = confirm('¿Desea vaciar la base local? Esta acción eliminará todos los registros guardados localmente.');
        const statusEl = document.getElementById('offlineStatus');
        if (!confirmClear) return;
        try {
            statusEl.textContent = '⏳ Eliminando base local...';
            // Eliminar la base de datos
            const deleteRequest = indexedDB.deleteDatabase(DB_NAME);
            deleteRequest.onsuccess = async function () {
                // Re-inicializar DB vacía
                await initDB();
                // Actualizar indicador visual
                try { await updateLocalDbStatus(); } catch(e){console.warn(e)}
                statusEl.textContent = '✅ Base local vaciada correctamente.';
            };
            deleteRequest.onerror = function () {
                console.error('Error al eliminar IndexedDB');
                statusEl.textContent = '❌ No se pudo vaciar la base local.';
            };
        } catch (e) {
            console.error(e);
            statusEl.textContent = '❌ Error al vaciar la base local.';
        }
    }

    async function verifyDocumentOfflineFirst(documentNumber) {
        const eventId = eventSelect.value;
        const localRecord = await findInLocal(documentNumber);

        // ============================================
        // 🧠 Caso 1: Registro encontrado en la base local
        // ============================================
        if (localRecord && localRecord.event_id == eventId) {
            console.log("✅ Encontrado en base local:", localRecord);
            let sound = false;

            const resultDiv = document.getElementById('resultContainer');
            const record = localRecord;

            // 🎨 Estilos según horario
            const now = new Date();
            let statusMessage = "";
            let statusColor = "text-slate-600 border-slate-300 bg-slate-50";

            if (record.ticket.entry_date) {
                const entryDate = new Date(record.ticket.entry_date);
                const start = record.ticket.entry_start_time ? new Date(`${record.ticket.entry_date}T${record.ticket.entry_start_time}`) : null;
                const end   = record.ticket.entry_end_time ? new Date(`${record.ticket.entry_date}T${record.ticket.entry_end_time}`) : null;

                const isToday = entryDate.toDateString() === now.toDateString();
                const isWithin = start && end && now >= start && now <= end;

                if (isWithin) {
                    statusMessage = "🟢 El evento está activo en este momento.";
                    statusColor = "text-green-600 border-green-400 bg-green-50";
                    sound = true;
                } else if (isToday) {
                    statusMessage = "🕓 El evento es hoy, pero aún no está en su rango horario.";
                    statusColor = "text-yellow-600 border-yellow-400 bg-yellow-50";
                } else {
                    statusMessage = "🔴 Este evento no está activo en la fecha actual.";
                    statusColor = "text-red-600 border-red-400 bg-red-50";
                }
            } else {
                statusMessage = "⚠️ No se ha definido una fecha para este evento.";
            }

            playSound(sound);
            // 👶 Sección de menores (si existen)
            const minors = record.minors || [];
            let minorsSection = "";
            if (minors.length > 0) {
                minorsSection = `
                    <div class="border rounded-md p-4 mb-4 bg-slate-50 dark:bg-darkmode-700 shadow-sm">
                        <h4 class="font-semibold text-lg mb-2 text-slate-800 dark:text-slate-100">👶 Menores asociados</h4>
                        <ul class="list-disc ml-5 text-sm text-slate-700 dark:text-slate-300">
                            ${minors.map(m => `<li><strong>${m.full_name}</strong> — ${m.age} años</li>`).join('')}
                        </ul>
                    </div>
                `;
            }

            // 🧾 Información del evento (desde el ticket)
            const eventCard = `
                <article class="w-full bg-white dark:bg-darkmode-600 border ${statusColor} rounded-md p-4 mb-4 shadow-sm">
                    <h5 class="font-semibold text-slate-800 dark:text-slate-200 text-base">${record.ticket.name}</h5>
                    <p class="text-xs mt-1 ${statusColor} font-medium">${statusMessage}</p>
                    <p class="text-sm mt-2"><strong>Fecha:</strong> ${record.ticket.entry_date ?? 'Sin fecha'}</p>
                    <p class="text-sm"><strong>Hora:</strong> ${record.ticket.entry_start_time ?? 'No especificada'} — ${record.ticket.entry_end_time ?? 'No especificada'}</p>
                </article>
            `;

            // 🧍 Información del usuario
            const userInfo = `
                <div class="border rounded-md p-4 mb-4 ${statusColor} text-left shadow-sm w-full transition">
                    <h4 class="font-semibold text-lg mb-2">👤 Información del usuario</h4>
                    <p class="text-sm"><strong>Nombre:</strong> ${record.name}</p>
                    <p class="text-sm"><strong>Documento:</strong> ${record.document_number}</p>
                    ${record.email ? `<p class="text-sm"><strong>Correo:</strong> ${record.email}</p>` : ''}
                    <p class="mt-2 text-xs text-slate-700 dark:text-slate-400">
                        <strong>Verificación:</strong> ${new Date().toLocaleString()}
                    </p>
                </div>
            `;

            let guardianInfo = '';
            if (record.guardian) {
                guardianInfo = `
                    <div class="border rounded-md p-4 mb-4 text-left shadow-sm w-full transition bg-blue-50 border-blue-300">
                        <h4 class="font-semibold text-lg mb-2 text-blue-700">👥 Acompañante</h4>
                        <p class="text-sm"><strong>Nombre:</strong> ${record.guardian.name}</p>
                        <p class="text-sm"><strong>Documento:</strong> ${record.guardian.document_number}</p>
                    </div>
                `;
            }

            resultDiv.innerHTML = `
                ${eventCard}
                ${minorsSection}
                ${userInfo}
                ${guardianInfo}
            `;

            if (window.lucide?.createIcons) window.lucide.createIcons();
            return;
        }

        // ============================================
        // 🚨 Caso 2: No está en local y sin conexión
        // ============================================
        if (!navigator.onLine) {
            showSimpleAlert('warning', '⚠️ No hay conexión y el documento no está en la base local.');
            playSound(false);
            return;
        }

        // ============================================
        // 🌐 Caso 3: Consultar al servidor si hay red
        // ============================================
        executeVerification(documentNumber);
    }

    // 🔔 Pequeña función auxiliar para alertas simples
    function showSimpleAlert(type, message) {
        const colors = {
            success: 'text-green-600 border-green-400 bg-green-50',
            warning: 'text-yellow-600 border-yellow-400 bg-yellow-50',
            danger:  'text-red-600 border-red-400 bg-red-50'
        };
        const color = colors[type] || colors.warning;

        resultDiv.innerHTML = `
            <div class="border rounded-md p-4 mb-4 ${color} text-left shadow-sm w-full transition">
                <h4 class="font-semibold text-lg mb-2">🔎 Verificación</h4>
                <p>${message}</p>
            </div>
        `;
    }


    async function logAllLocalData() {
        const dbInstance = await getDB();
        return new Promise((resolve) => {
            const tx = dbInstance.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const req = store.openCursor();
            const all = [];

            req.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    all.push(cursor.value);
                    cursor.continue();
                } else {
                    console.log("📦 Contenido completo de IndexedDB:", all);
                    resolve(all);
                }
            };

            req.onerror = () => {
                console.error("❌ Error leyendo IndexedDB");
                resolve([]);
            };
        });
    }
});
</script>
@endsection
