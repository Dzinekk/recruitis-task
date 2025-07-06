<script setup>
import {computed, defineProps, defineModel} from "vue";

const currentPage = defineModel({required: true})

const props = defineProps({
    totalPages: {
        type: Number,
        required: true,
    },
    maxPageLinks: {
        type: Number,
        required: false,
        default: 5,
    },
})

const classes = 'flex items-center justify-center text-lg w-[32px] h-[32px] text-sky-950 rounded hover:no-underline hover:bg-sky-200 transition-colors duration-200 bg-gray-100';
const activeClasses = '!bg-sky-600 !text-white';
const disabledClasses = 'opacity-50 pointer-events-none';

const pagesRange = computed(() => {
    let maxPages = Math.min(props.totalPages, props.maxPageLinks);

    let range = Array.from( { length: maxPages},
    (_, i) => ((currentPage.value - Math.floor(maxPages / 2)) + i));

    let maxPageInRange = Math.max(...range);
    let minPageInRange = Math.min(...range);

    if (minPageInRange < 1) {
        range = Array.from( { length: maxPages}, (_, i) => (i + 1));
    }

    if (maxPageInRange > props.totalPages) {
        range = Array.from( { length: maxPages},
    (_, i) => (props.totalPages - maxPages + i + 1));
    }
    return range;
})

</script>

<template>
<ul class="flex flex-wrap list-none gap-1" v-if="totalPages > 1">
    <li>
        <a href="#" :class="{[classes]: true, [disabledClasses]: currentPage === 1}" @click.prevent="currentPage = 1">
            <<
        </a>
    </li>

    <li>
        <a href="#" :class="{[classes]: true, [disabledClasses]: currentPage === 1}" @click.prevent="currentPage = Math.max(currentPage-1, 1)">
            <
        </a>
    </li>

    <li v-if="pagesRange.length > 0 && pagesRange[0] != 1" class="opacity-50 flex items-end">
        ...
    </li>

    <li v-for="i in pagesRange" :key="i">
        <a href="#" @click.prevent="currentPage = i" :class="{[classes]: true, [activeClasses]: i === currentPage}">{{ i }}</a>
    </li>

    <li v-if="pagesRange.length > 0 && pagesRange[pagesRange.length - 1] != totalPages" class="opacity-50 flex items-end">
        ...
    </li>

    <li>
        <a href="#" :class="{[classes]: true, [disabledClasses]: currentPage === totalPages}" @click.prevent="currentPage = Math.min(currentPage+1, totalPages)">
            >
        </a>
    </li>

    <li>
        <a href="#" :class="{[classes]: true, [disabledClasses]: currentPage === totalPages}" @click.prevent="currentPage = totalPages">
            >>
        </a>
    </li>
</ul>
</template>
