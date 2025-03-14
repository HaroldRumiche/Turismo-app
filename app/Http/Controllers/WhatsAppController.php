<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Client;

class WhatsAppController extends Controller
{
    private $apiUrl;
    private $apiToken;

    public function __construct()
    {
        $this->apiUrl = env('QR_API_URL', 'https://demo.qr.buho.la') . '/api/message/send-text';
        $this->apiToken = env('QR_API_TOKEN');
    }

    public function sendMessage($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        if (!$client->phone) {
            return response()->json(['error' => 'El cliente no tiene un número de teléfono registrado'], 400);
        }

        // Verificar si la URL y el Token están configurados
        if (!$this->apiUrl || !$this->apiToken) {
            Log::error('La URL o el token de la API no están configurados en .env.');
            return response()->json(['error' => 'Configuración de API incorrecta'], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'number' => $client->phone,
                'message' => "Hola, {$client->name}. Bienvenido a nuestra plataforma de turismo!"
            ]);

            if ($response->successful()) {
                return response()->json(['success' => 'Mensaje enviado correctamente', 'data' => $response->json()]);
            } else {
                Log::error("Error al enviar mensaje: " . $response->body());
                return response()->json(['error' => 'No se pudo enviar el mensaje'], 500);
            }
        } catch (\Exception $e) {
            Log::error("Error de conexión con la API: " . $e->getMessage());
            return response()->json(['error' => 'Error de conexión con la API'], 500);
        }
    }
}
