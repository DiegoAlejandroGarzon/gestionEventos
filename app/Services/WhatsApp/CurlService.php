<?php

namespace App\Services\WhatsApp;


class CurlService
{
    private $__token;
    private $__numberWhatssAppId;
    public function __construct($numberWhatssAppId = null) {
        if($numberWhatssAppId != null){
            $this->__numberWhatssAppId = $numberWhatssAppId;
            $token = "EAATBOfopFqQBPtosxnqJJFYNT98oaU4IO62SA0AuujnUCnzFk1mMqEYHHjZC8liZAMaVGNvrZAmDPF9seecb1ZCCFCZAp6aHbfGSiuv5sDCxt0n6eEi37Pns1Pc3I2OMKW08lt8mE7aoQMt72chz4e2Brd4582h220f5v70K4R3gygaQsj96x623QwXVD4zfnjQZDZD";
            $this->__token = $token;
        }
    }

    public function curlFacebookApi($data) {
        $url = "https://graph.facebook.com/v21.0/".$this->__numberWhatssAppId."/messages";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->__token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        file_put_contents(storage_path().'/logs/log_webhook.txt', "<<== RESPONSE curl ==>>" . serialize($response) . PHP_EOL, FILE_APPEND);
        if (curl_errno($ch) || strpos($response, 'error') !== false) {
            file_put_contents(storage_path().'/logs/log_webhook.txt', "<<== Error curl ==>>" . serialize($response) . PHP_EOL, FILE_APPEND);
        }
        curl_close($ch);

        return $response;
    }

    public function curlFacebookApiFile($mediaId) {
        $url = "https://graph.facebook.com/v18.0/" . $mediaId;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->__token
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        // Verifica si hubo error de conexión
        if ($curlError || strpos($response, 'error') !== false) {
            file_put_contents(storage_path() . '/logs/log_webhook.txt',
                "<<== Error Curl File ==>> " . json_encode($curlError) . PHP_EOL, FILE_APPEND);
            return null;
        }
        // Intenta decodificar la respuesta como JSON
        $decoded = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded; // Devuelve array asociativo si es JSON válido
        } else {
            // Si no es JSON, guarda log de respuesta inesperada
            file_put_contents(storage_path() . '/logs/log_webhook.txt',
                "<<== Respuesta no JSON o error HTTP ($httpCode) ==>> " . $response . PHP_EOL, FILE_APPEND);
            return null;
        }
    }

    public function curlFacebookApiDownloadFile($mediaUrl, $archivoDestino) {
        $ch = curl_init($mediaUrl);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->__token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        // Manejo de errores de cURL
        if ($curlError) {
            file_put_contents(storage_path() . '/logs/log_webhook.txt',
                "<<== Error al descargar media ==>> " . $curlError . PHP_EOL, FILE_APPEND);
            return false;
        }

        // Verifica código HTTP y guarda el archivo si es válido
        if ($httpCode === 200 && $data) {
            if (file_put_contents($archivoDestino, $data)) {
                return true; // Éxito
            } else {
                file_put_contents(storage_path() . '/logs/log_webhook.txt',
                    "<<== Error al guardar archivo ==>> " . $archivoDestino . PHP_EOL, FILE_APPEND);

                return false;
            }
        } else {
            file_put_contents(storage_path() . '/logs/log_webhook.txt',
                "<<== Error HTTP ($httpCode) al descargar media ==>>" . PHP_EOL, FILE_APPEND);

            return false;
        }

        return false;
    }

    public function getCurlActionApiExterna($url, $data) {
        //$url = "https://nuevageneracion.com.co";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded"
        ]);

        $response = curl_exec($ch);

        $response = curl_exec($ch);
        file_put_contents(storage_path().'/logs/log_webhook.txt', "<<==RSP Error getCurlActionApiExterna ==>>" . serialize($response) . PHP_EOL, FILE_APPEND);
        if (curl_errno($ch)) {
            file_put_contents(storage_path().'/logs/log_webhook.txt', "<<==ERROR Error getCurlActionApiExterna ==>>" . serialize($response) . PHP_EOL, FILE_APPEND);
        }
        curl_close($ch);
        return json_decode($response, true);
    }

    function consultarOpenAI(string $prompt, string $modelo = 'gpt-4', float $temperatura = 0.7): ?string {
        $apiKey = env('OPENAI_API_KEY');
        $ch = curl_init();

        $data = [
            "model" => $modelo,
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => $temperatura,
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $apiKey,
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        file_put_contents(storage_path().'/logs/log_webhook.txt', "<<== OPENAI ==>>" . serialize($response) . PHP_EOL, FILE_APPEND);

        if (!$response) {
            return null;
        }

        $resultado = json_decode($response, true);

        return $resultado["choices"][0]["message"]["content"] ?? null;
    }

    public function buildNarrativePrompt($chapter, $scene, $player, $lastEvents = [], $worldState = null)
{
    // 🌀 1️⃣ Obtener últimos eventos
    $recentEvents = array_slice($lastEvents->toArray(), -3);
    $eventsText = "";

    if (!empty($recentEvents)) {
        $eventsText = "📖 *Últimos eventos recientes en la historia:*\n\n";
        foreach ($recentEvents as $index => $e) {
            $eventData = $e['last_event'];
            if (is_string($eventData)) {
                $eventData = json_decode($eventData, true);
            }
            $narrativa = $eventData['narrativa'] ?? 'Evento sin narrativa';
            $opciones = $eventData['opciones'] ?? [];

            $eventsText .= "🌀 *Evento " . ($index + 1) . ":*\n";
            $eventsText .= "🗣️ _{$narrativa}_\n";
            if (!empty($opciones)) {
                $eventsText .= "🔸 *Opciones presentadas:*\n";
                foreach ($opciones as $opcion) {
                    $eventsText .= "   • " . ($opcion['texto'] ?? '') . "\n";
                }
            }
            $eventsText .= "\n";
        }
    }

    // 🌍 2️⃣ Fecha del mundo (si existe)
    $worldYear = $worldState->current_year ?? 11130;
    $worldDay = $worldState->current_day ?? 1;

    // ⚙️ 3️⃣ Tono narrativo adaptativo según rol
    $tone = match ($player->role) {
        'Guardián del Alba' => 'épico y protector, con sensación de deber y esperanza',
        'Sombra Errante' => 'sigiloso, oscuro y calculador, con tintes de misterio',
        'Oráculo del Destino' => 'místico, sabio y enigmático, con toques proféticos',
        'Forjador del Caos' => 'intenso, impredecible y poderoso, con energía peligrosa',
        default => 'neutral y descriptivo',
    };

    // 🎮 4️⃣ Generar dinámicamente las opciones
    $numOptions = max(1, intval($scene->number_options));
    $options = [];
    for ($i = 1; $i <= $numOptions; $i++) {
        $options[] = ["id" => $i, "texto" => "Opción {$i}"];
    }
    $optionsJson = json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // 🧠 5️⃣ Construir el prompt completo
    return <<<EOT
Eres el narrador de un juego de rol conversacional en WhatsApp llamado *"Another Life"*.
Tu tarea es escribir la narrativa inicial de la escena actual, en un tono {$tone}.
El jugador interactúa mediante texto y emojis, por lo que el formato debe ser visual, breve y emocionante.

--- 🌍 CONTEXTO GENERAL DEL JUEGO ---
Capítulo: {$chapter->chapter_number} - {$chapter->name}
Descripción del capítulo: {$chapter->description}
Objetivo global del capítulo: {$chapter->goal}
Fecha del mundo: Año {$worldYear}, Día {$worldDay}

--- 👤 CONTEXTO DEL JUGADOR ---
Nombre del jugador: {$player->name}
Linaje: {$player->house->name}
Region: {$player->house->region}
Rol: {$player->role}
Nivel de experiencia: {$player->trait}
Energia: {$player->energy}/100
Inventario actual: {$player->inventory}
Progreso: Escena {$scene->scene_number} de {$chapter->total_scenes}

--- 📜 HISTORIAL DE LA AVENTURA ---
{$eventsText}

--- 🎭 ESCENA ACTUAL ---
Título de la escena: {$scene->title}
Descripción base: {$scene->description}
Objetivo de la escena: {$scene->goal}

--- ⚡ INSTRUCCIONES PARA LA IA ---
Responde **únicamente con un JSON válido**, con la siguiente estructura exacta:

{
  "narrativa": "Texto breve (máx. 6 frases) que describa la escena con tono {$tone}. Usa negritas en nombres, frases clave y emojis inmersivos.",
  "opciones": {$optionsJson}
}

🎯 REGLAS:
1. Escribe *un solo párrafo breve y emocionante* (máx. 6 frases).
2. Usa *negritas*, *emojis inmersivos* (ej: 👣📜🕯️🔮🔥⚔️), y formato visual estilo WhatsApp.
3. No desarrolles aún el puzzle o combate; solo genera la narrativa y bifurcación de caminos.
4. Cada opción debe tener un gancho psicológico (riesgo, recompensa o misterio).
5. No incluyas ningún texto fuera del JSON. No repitas estas instrucciones.

EOT;
}


    public function buildPuzzlePromptTypeAcertijoNarrativo($chapter, $scene, $player, $lastEvents = [], $worldState = null)
    {
        $recentEvents = array_slice($lastEvents->toArray(), -3);
        $eventsText = "";

        if (!empty($recentEvents)) {
            $eventsText = "📖 *Últimos eventos recientes en la historia:*\n\n";
            foreach ($recentEvents as $index => $e) {
                $eventData = $e['last_event'];
                // Convertir a array si es JSON string
                if (is_string($eventData)) {
                        $eventData = json_decode($eventData, true);
                }
                // Asegurar estructura
                $narrativa = $eventData['narrativa'] ?? 'Evento sin narrativa';
                $opciones = $eventData['opciones'] ?? [];
                // Numerar el evento
                $eventsText .= "🌀 *Evento ".($index + 1).":*\n";
                $eventsText .= "🗣️ _{$narrativa}_\n";
                if (!empty($opciones)) {
                        $eventsText .= "🔸 *Opciones:*\n";
                        foreach ($opciones as $opcion) {
                                $eventsText .= "   • ".$opcion['texto']."\n";
                        }
                }
                $eventsText .= "\n"; // Espacio entre eventos
            }
        }

        // 🌍 2️⃣ Fecha del mundo (si existe)
        $worldYear = $worldState->current_year ?? 11130;
        $worldDay = $worldState->current_day ?? 1;

        // ⚙️ 3️⃣ Tono narrativo adaptativo según rol
        $tone = match ($player->role) {
            'Guardián del Alba' => 'épico y protector, con sensación de deber y esperanza',
            'Sombra Errante' => 'sigiloso, oscuro y calculador, con tintes de misterio',
            'Oráculo del Destino' => 'místico, sabio y enigmático, con toques proféticos',
            'Forjador del Caos' => 'intenso, impredecible y poderoso, con energía peligrosa',
            default => 'neutral y descriptivo',
        };

        // --- Generar dinámicamente las opciones ---
        // Generar dinámicamente las opciones como JSON plano
        $options = [];
        for ($i = 1; $i <= $scene->number_options; $i++) {
            $options[] = ["id" => $i, "texto" => "Opción {$i}"];
        }
        $optionsJson = json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<EOT
        Eres el narrador de un juego de rol conversacional en WhatsApp llamado "Another Life".
        Ahora debes generar un **puzzle tipo ACERTIJO NARRATIVO**, en un tono {$tone}. Debe ser un desafío textual que el jugador debe resolver.

        --- CONTEXTO GENERAL DEL JUEGO ---
        Capítulo: {$chapter->chapter_number} - {$chapter->name}
        Objetivo global del capítulo: {$chapter->goal}
        Descripción del capítulo: {$chapter->description}
        Fecha del mundo: Año {$worldYear}, Día {$worldDay}

        --- CONTEXTO DEL JUGADOR ---
        Nombre del jugador: {$player->name}
        Linaje: {$player->house->name}
        Region: {$player->house->region}
        Rol: {$player->role}
        Nivel de experiencia: {$player->trait}
        Energia: {$player->energy}/100
        Inventario actual: {$player->inventory}
        Progreso: Escena {$scene->scene_number} de {$chapter->total_scenes}

        --- HISTORIAL DE LA AVENTURA ---
        {$eventsText}

        --- ESCENA ACTUAL ---
        Título de la escena: {$scene->title}
        Objetivo de la escena: {$scene->goal}
        Tipo de puzzle: ACERTIJO NARRATIVO
        Objetivo específico del puzzle: {$scene->puzzle_goal}

        --- INSTRUCCIONES PARA LA IA ---
        Devuelve la respuesta en formato **JSON válido** con esta estructura exacta:

        {
          "narrativa": "Texto narrativo que describe cómo aparece el acertijo en la escena (máx. 1 párrafo, con emojis y misterio).",
          "acertijo": "El enunciado del acertijo, adivinanza o enigma en forma de pregunta clara.",
          "pista": "Una pista breve que ayude al jugador a orientarse sin dar la respuesta directamente.",
          "exp_respuesta": "Una revelación narrativa que explique por qué la opción correcta es la adecuada. Debe sonar como parte de la historia, usando un tono místico o simbólico, conectando los elementos del acertijo con su significado (máx. 3 frases).",
          "opciones": {$optionsJson},
          "respuesta_correcta": X
        }

        --- REGLAS IMPORTANTES ---
        1. La clave *narrativa* debe ser descriptiva, inmersiva y con emojis.
        2. La clave *acertijo* debe contener una adivinanza o enigma concreto.
        3. La clave *pista* debe ser corta (1 frase como máximo) y dar una ligera orientación.
        4. En *opciones*, genera exactamente {$scene->number_options} posibles respuestas (todas plausibles).
        5. *respuesta_correcta* debe indicar el número de la opción correcta.
        6. Devuelve únicamente el JSON. Nada más.
        EOT;
    }

    public function buildPuzzlePromptTypeAcertijoEmojis($chapter, $scene, $player, $lastEvents = [], $worldState = null)
    {
        $recentEvents = array_slice($lastEvents->toArray(), -3);
		$eventsText = "";

		if (!empty($recentEvents)) {
			$eventsText = "📖 *Últimos eventos recientes en la historia:*\n\n";
			foreach ($recentEvents as $index => $e) {
				$eventData = $e['last_event'];
				// Convertir a array si es JSON string
				if (is_string($eventData)) {
					$eventData = json_decode($eventData, true);
				}
				// Asegurar estructura
				$narrativa = $eventData['narrativa'] ?? 'Evento sin narrativa';
				$opciones = $eventData['opciones'] ?? [];
				// Numerar el evento
				$eventsText .= "🌀 *Evento ".($index + 1).":*\n";
				$eventsText .= "🗣️ _{$narrativa}_\n";
				if (!empty($opciones)) {
					$eventsText .= "🔸 *Opciones:*\n";
					foreach ($opciones as $opcion) {
						$eventsText .= "   • ".$opcion['texto']."\n";
					}
				}
				$eventsText .= "\n"; // Espacio entre eventos
			}
		}

        // --- Generar dinámicamente las opciones ---
        $options = [];
        for ($i = 1; $i <= $scene->number_options; $i++) {
            $options[] = ["id" => $i, "texto" => "Opción {$i}"];
        }
        $optionsJson = json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<EOT
        Eres el narrador de un juego de rol conversacional en WhatsApp llamado "Another Life".
        Ahora debes generar un **puzzle tipo ACERTIJO CON EMOJIS**, donde el enigma debe representarse principalmente con emojis.

        --- CONTEXTO GENERAL DEL JUEGO ---
        Capítulo: {$chapter->chapter_number} - {$chapter->name}
        Objetivo global del capítulo: {$chapter->goal}
        Descripción del capítulo: {$chapter->description}

        --- CONTEXTO DEL JUGADOR ---
        Nombre del jugador: {$player->name}
        Rol: {$player->role}
        Inventario actual: {$player->inventory}
        Progreso: Escena {$scene->scene_number} de {$chapter->total_scenes}

        --- HISTORIAL DE LA AVENTURA ---
        {$eventsText}

        --- ESCENA ACTUAL ---
        Título de la escena: {$scene->title}
        Objetivo de la escena: {$scene->goal}
        Tipo de puzzle: ACERTIJO CON EMOJIS
        Objetivo específico del puzzle: {$scene->puzzle_goal}

        --- INSTRUCCIONES PARA LA IA ---
        Devuelve la respuesta en formato **JSON válido** con esta estructura exacta:

        {
          "narrativa": "Texto narrativo inmersivo que introduce el acertijo (máx. 1 párrafo, con emojis).",
          "acertijo": "El acertijo expresado principalmente con emojis (puede incluir una o dos palabras de apoyo si es necesario).",
          "pista": "Una pista breve (máx. 1 frase, opcionalmente con emojis).",
          "exp_respuesta": "Una revelación narrativa que explique por qué la opción correcta es la adecuada. Debe sonar como parte de la historia, usando un tono místico o simbólico, conectando los elementos del acertijo con su significado (máx. 3 frases).",
          "opciones": {$optionsJson},
          "respuesta_correcta": X
        }

        --- REGLAS IMPORTANTES ---
        1. La clave **narrativa** debe ser inmersiva, misteriosa y contener emojis.
        2. La clave **acertijo** debe representarse con **emojis como base principal**. Puede incluir mínimas palabras solo si es indispensable.
        3. La clave **pista** debe ser muy breve (máx. 1 frase) y opcionalmente puede usar emojis.
        4. En **opciones**, usa exactamente las {$scene->number_options} opciones ya dadas en el JSON (no inventes nuevas ni modifiques el texto).
        5. **respuesta_correcta** debe ser un número entero entre 1 y {$scene->number_options}.
        6. Devuelve únicamente el JSON. No incluyas explicaciones, introducciones ni texto adicional.
        EOT;
    }

}
