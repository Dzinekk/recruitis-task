<script setup>
//get job offers through axios from API /api/jobs/list
import axios from 'axios';
import {onMounted, ref} from "vue";

const currentPage = ref(1);
const perPage = ref(10);
const loading = ref(false);
const jobs = ref([]);

onMounted(async () => {
    loading.value = true;
    await fetchJobs();
    loading.value = false;
});

async function fetchJobs() {
    const response = await axios.get(`/api/jobs?page=${currentPage.value}&limit=${perPage.value}`);
    jobs.value = response.data.payload;
    console.log(jobs.value);
}
</script>

<template>
<div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-8" v-if="!loading">
    <div v-for="job in jobs" class="flex flex-col gap-4 p-4 border border-gray-300 rounded-lg mb-4" :key="job.job_id">
        <h3 class="text-2xl">{{ job.title }}</h3>

        <div v-if="job.addresses && job.addresses.length">
            <div class="mb-1">Místo:</div>
            <div class="flex flex-wrap gap-4 text-base" >
                <div v-for="address in job.addresses" class="border border-gray-500 px-2 py-2">{{ address.city }}</div>
            </div>
        </div>

        <div class="mb-1">Typ úvazku: <strong>{{ job.employment?.name || 'N/A' }}</strong></div>
    </div>
</div>
<div v-else class="flex items-center justify-center h-64">
    <div class="text-gray-500">Načítání nabídek práce...</div>
</div>
</template>
