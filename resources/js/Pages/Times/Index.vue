<script setup>
import { ref, computed } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const entries = computed(() => {
    const props = usePage().props || {};
    return props.entries || [];
});

const receipts = computed(() => {
    const props = usePage().props || {};
    return props.receipts || [];
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

function formatHours(decimal) {
    if (!decimal) return "0.00";

    const hours = Math.floor(decimal); // parte intera
    const minutes = Math.round((decimal - hours) * 60); // parte in minuti

    return `${hours}.${minutes.toString().padStart(2, "0")}`;
}

const receiptForm = useForm({
    date: "",
    amount: "",
    image: null,
});

function uploadReceipt() {
    receiptForm.post(route("receipts.store"), {
        forceFormData: true,
        onSuccess: () => {
            receiptForm.reset();
        },
    });
}

/* -----------------------------
   MODAL PER LA PREVIEW
----------------------------- */

const showModal = ref(false);
const modalUrl = ref("");

function openModal(receipt) {
    modalUrl.value = "/storage/" + receipt.image_path;
    console.log(modalUrl);
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    modalUrl.value = null;
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="container py-4">
            <!-- ====================== -->
            <!--      TIMESHEET         -->
            <!-- ====================== -->

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

            <table class="table table-bordered mb-5">
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
                        <td>{{ formatHours(e.duration_hours) }}</td>
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

            <!-- ====================== -->
            <!--      RICEVUTE          -->
            <!-- ====================== -->

            <h2 class="mb-3">Ricevute</h2>

            <form @submit.prevent="uploadReceipt" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input
                        class="form-control"
                        type="date"
                        v-model="receiptForm.date"
                        required
                    />
                </div>
                <div class="col-md-3">
                    <input
                        class="form-control"
                        type="number"
                        v-model="receiptForm.amount"
                        placeholder="Importo"
                        required
                    />
                </div>
                <div class="col-md-4">
                    <input
                        class="form-control"
                        type="file"
                        @change="(e) => (receiptForm.image = e.target.files[0])"
                        required
                    />
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100">Carica</button>
                </div>
            </form>

            <table class="table table-bordered mb-5">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Importo</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in receipts" :key="r.id">
                        <td>{{ formatDate(r.date) }}</td>
                        <td>{{ r.amount }} €</td>
                        <td>
                            <a href="#" @click.prevent="openModal(r)">Vedi</a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- ====================== -->
            <!--       MODAL PREVIEW    -->
            <!-- ====================== -->

            <div v-if="showModal" class="modal-backdrop">
                <div class="modal-content">
                    <button @click="closeModal">X</button>

                    <!-- Se è immagine -->
                    <img
                        v-if="
                            modalUrl.endsWith('.jpg') ||
                            modalUrl.endsWith('.png') ||
                            modalUrl.endsWith('.jpeg')
                        "
                        :src="modalUrl"
                        style="max-width: 100%; height: auto"
                    />

                    <!-- Se è PDF -->
                    <iframe
                        v-if="modalUrl.endsWith('.pdf')"
                        :src="modalUrl"
                        style="width: 100%; height: 80vh"
                    >
                    </iframe>
                </div>
            </div>

            <!-- ====================== -->
            <!--     EXPORT PDF         -->
            <!-- ====================== -->

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
<style scoped>
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 90%;
    max-height: 90%;
    overflow: auto;
    position: relative;
}

.modal-content button {
    position: absolute;
    top: 10px;
    right: 10px;
}
</style>
