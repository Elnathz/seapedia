<script setup lang="ts">
import Logo from '@/components/brand/Logo.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-md bg-sidebar-primary p-1"
    >
        <Logo variant="mark" class="size-full" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-semibold">SEAPEDIA</span>
    </div>
</template>
