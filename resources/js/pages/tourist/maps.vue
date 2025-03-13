<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import GoogleMap from '@/components/GoogleMap.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Maps',
        href: '/tourist.maps',
    },
];
</script>

<template>
    <Head title="Maps" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <GoogleMap />
                </div>
            </div>
        </div>
    </AppLayout>
</template>