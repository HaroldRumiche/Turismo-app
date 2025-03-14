<template>
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Enviar Mensaje de Reservación</h1>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Selecciona un Cliente:</label>
            <select v-model="selectedClient" class="border border-gray-300 p-2 w-full rounded">
                <option value="" disabled>Selecciona un cliente</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                    {{ client.name }}
                </option>
            </select>
        </div>

        <button
            @click="sendMessage"
            class="bg-blue-500 text-white px-4 py-2 mt-2 rounded hover:bg-blue-600"
        >
            Enviar Mensaje
        </button>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            clients: [],
            selectedClient: null,
        };
    },
    mounted() {
        this.fetchClients();
    },
    methods: {
        async fetchClients() {
            try {
                const response = await axios.get("/api/clients");
                this.clients = response.data.clients;
            } catch (error) {
                console.error("Error obteniendo clientes:", error);
            }
        },
        async sendMessage() {
            if (!this.selectedClient) {
                alert("Selecciona un cliente antes de enviar el mensaje.");
                return;
            }
            try {
                const response = await axios.get(`/api/send-message/${this.selectedClient}`);
                alert("Mensaje enviado correctamente");
            } catch (error) {
                console.error("Error enviando mensaje:", error);
                alert("Error al enviar el mensaje");
            }
        },
    },
};
</script>

<style scoped>
button:hover {
    background-color: #2563eb;
}
</style>
