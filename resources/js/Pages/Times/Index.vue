<script setup>
import { ref, computed } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const entries = computed(() => {
    const props = usePage().props || {};
    return props.entries || [];
});

const form = useForm({
    date: "",
    start_time: "",
    end_time: "",
    notes: "",
});
function submit() {
    form.post(route("times.store"), form.value);
}
function remove(id) {
    if (!confirm("Eliminare questa voce?")) return;
    form.delete(route("times.destroy", id));
}
const month = ref(null);
const year = ref(null);

function download() {
    let y = parseInt(year.value);
    let m = null;

    // Se è stato selezionato il type="month" (es: "2025-11")
    if (month.value) {
        const parts = month.value.split("-");
        y = parseInt(parts[0]); // sovrascrive l'anno con quello scelto nel month
        m = parseInt(parts[1]); // mese
    }

    // Se il mese non arriva dal type="month", usa il mese corrente
    if (!m) {
        m = new Date().getMonth() + 1;
    }

    // Se l'anno non è valido → usa quello corrente
    if (!y || y < 2000 || y > 2100) {
        y = new Date().getFullYear();
    }

    // Chiamata a Inertia / route con valori finali e puliti
    window.open(route("report.monthly", { year: y, month: m }), "_blank");
}

function formatDate(dateStr) {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    const day = String(d.getDate()).padStart(2, "0");
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}
</script>
<template>
    <AuthenticatedLayout>
        <div class="container py-4">
            <h1 class="mb-4">Timesheet</h1>
            <form @submit.prevent="submit" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input
                        class="form-control"
                        v-model="form.date"
                        type="date"
                        required
                    />
                </div>
                <div class="col-md-2">
                    <input
                        class="form-control"
                        v-model="form.start_time"
                        type="time"
                        required
                    />
                </div>
                <div class="col-md-2">
                    <input
                        class="form-control"
                        v-model="form.end_time"
                        type="time"
                        required
                    />
                </div>
                <div class="col-md-3">
                    <input
                        class="form-control"
                        v-model="form.notes"
                        placeholder="Note"
                    />
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Aggiungi</button>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Inizio</th>
                        <th>Fine</th>
                        <th>Ore</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="e in entries" :key="e.id">
                        <td>{{ formatDate(e.date) }}</td>
                        <td>{{ e.start_time }}</td>
                        <td>{{ e.end_time }}</td>
                        <td>{{ e.duration_hours }}</td>
                        <td>
                            <button
                                class="btn btn-danger btn-sm"
                                @click="remove(e.id)"
                            >
                                X
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="d-flex gap-3 mt-4">
                <input
                    class="form-control w-auto"
                    type="month"
                    v-model="month"
                    placeholder="Mese"
                />
                <input
                    class="form-control w-auto"
                    type="number"
                    v-model="year"
                    min="2000"
                    max="2100"
                    placeholder="Anno"
                />
                <button class="btn btn-secondary" @click="download">
                    Esporta PDF
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
