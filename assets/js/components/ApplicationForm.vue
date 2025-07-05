<script setup>
import {ref} from 'vue';
import axios from 'axios';

const props = defineProps({
    jobId: {
        type: Number,
        required: true,
    },
});

const successMessage = ref('');
const errorMessage = ref('');

async function handleSubmit(formData) {
    successMessage.value = '';
    errorMessage.value = '';

    const data = {
        name: formData.name,
        email: formData.email,
        phone: formData.phone,
        message: formData.message,
    };

    try {
        await axios.post(`/api/jobs/${props.jobId}/reply`, data);
        successMessage.value = 'Vaše odpověď byla úspěšně odeslána!';
    } catch (error) {
        errorMessage.value = 'Při odesílání došlo k chybě. Zkuste to prosím znovu.';
        console.error('Chyba při odesílání odpovědi:', error.response?.data?.message);
    }
}
</script>

<template>
<div class="mt-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Odpovědět na pozici</h2>

    <div v-if="successMessage" class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
        {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
        {{ errorMessage }}
    </div>

    <FormKit type="form" @submit="handleSubmit" #default="{ value }" v-if="!successMessage">
        <FormKit
            type="text"
            name="name"
            label="Jméno a příjmení"
            validation="required"
        />
        <FormKit
            type="email"
            name="email"
            label="E-mail"
            validation="required|email"
        />
        <FormKit
            type="tel"
            name="phone"
            label="Telefon"
            validation="required"
        />
        <FormKit
            type="textarea"
            name="message"
            label="Vaše zpráva"
            rows="5"
            validation="required"
        />
    </FormKit>
</div>
</template>
