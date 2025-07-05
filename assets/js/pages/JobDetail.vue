<script setup>
import {onMounted, ref} from "vue";
import axios from "axios";
import ApplicationForm from "../components/ApplicationForm.vue";

const props = defineProps({
    jobId: {
        type: Number,
        required: true,
    }
})

const loading = ref(false);
const job = ref({});
const error = ref(null);

onMounted(async () => {
    loading.value = true;
    await fetchJob();
    loading.value = false;
});

async function fetchJob() {
    error.value = null;
    try {
        const response = await axios.get(`/api/jobs/${props.jobId}`);
        job.value = response.data;
    } catch (e) {
        error.value = `Chyba při načítání nabídky práce: ${e.response.data.message || e.message}`;
    }
}

</script>

<template>
<div v-if="job && !loading && !error">
    <h2 class="text-3xl mb-2">{{ job.title }}</h2>

    <div class="mb-4 flex gap-4 items-center">
        <div v-if="job.addresses && job.addresses.length">
            <div class="flex flex-wrap gap-4 text-base">
                <div v-for="address in job.addresses" class="border border-gray-500 px-2 py-2">{{ address.city }}</div>
            </div>
        </div>

        <div class="mb-1">{{ job.employment?.name || 'N/A' }}</div>
    </div>

    <div class="text-justify">{{ job.description }}</div>

    <div class="mt-8">
        <application-form :job-id="props.jobId" />
    </div>
</div>
<div v-else-if="loading" class="flex items-center justify-center h-64">
    <div class="text-gray-500">Načítání detailu nabídky práce...</div>
</div>
<div v-else-if="error" class="text-red-500 text-center">
    <p>{{ error }}</p>
</div>
</template>
