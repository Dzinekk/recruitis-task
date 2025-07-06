<script setup>
//get job offers through axios from API /api/jobs/list
import axios from 'axios';
import {onMounted, ref, watch} from "vue";
import Pagination from "./Pagination.vue";

const perPage = 10;
const totalPages = 5; //just for testing purposes
const currentPage = ref(1);
const loading = ref(false);
const jobs = ref([]);
const error = ref(null);

onMounted(async () => {
    loading.value = true;
    await fetchJobs();
    loading.value = false;
});

watch(currentPage, async (newPage) => {
    loading.value = true;
    await fetchJobs();
    loading.value = false;
});

async function fetchJobs() {
    error.value = null;
    try {
        const response = await axios.get(`/api/jobs?page=${currentPage.value}&limit=${perPage}`);
        jobs.value = response.data;
    } catch (e) {
        error.value = `Chyba při načítání nabídek práce: ${e.response.data.message || e.message}`;
    }
}

</script>

<template>
<div>
    <div v-if="!loading && !error" class="flex flex-col gap-4">
        <div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-8">
            <a v-for="job in jobs" :href="`/${job.job_id}`"
                 class="flex flex-col gap-4 p-4 border border-gray-300 rounded-lg mb-4 hover:bg-gray-800 cursor-pointer"
                 :key="job.job_id">
                <h3 class="text-2xl">{{ job.title }}</h3>

                <div v-if="job.addresses && job.addresses.length">
                    <div class="mb-1">Místo:</div>
                    <div class="flex flex-wrap gap-4 text-base" >
                        <div v-for="address in job.addresses" class="border border-gray-500 px-2 py-2">{{ address.city }}</div>
                    </div>
                </div>

                <div class="mb-1">Typ úvazku: <strong>{{ job?.employment?.name || 'N/A' }}</strong></div>
            </a>
        </div>

        <div class="text-center text-gray-500" v-if="jobs.length === 0">Žádné nabídky práce k zobrazení.</div>

        <pagination :total-pages="totalPages" v-model="currentPage"></pagination>
    </div>
    <div v-else-if="loading" class="flex items-center justify-center h-64">
        <div class="text-gray-500">Načítání nabídek práce...</div>
    </div>
    <div v-else-if="error" class="text-red-500 text-center">
        <p>{{ error }}</p>
    </div>
</div>
</template>
