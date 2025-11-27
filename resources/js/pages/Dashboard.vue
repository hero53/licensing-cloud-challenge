<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Key, Activity, Zap } from 'lucide-vue-next';

interface DashboardStats {
    activeLicences: number;
    totalApplications: number;
    executionsToday: number;
}

defineProps<{
    stats: DashboardStats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- Active Licences -->
                <div
                    class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Active Licences</span>
                        <Key class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="space-y-1">
                        <div class="text-2xl font-bold">{{ stats.activeLicences }}</div>
                        <p class="text-xs text-muted-foreground">Valid & active</p>
                    </div>
                </div>

                <!-- Total Applications -->
                <div
                    class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Applications</span>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="space-y-1">
                        <div class="text-2xl font-bold">{{ stats.totalApplications }}</div>
                        <p class="text-xs text-muted-foreground">Registered apps</p>
                    </div>
                </div>

                <!-- Executions Today -->
                <div
                    class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Executions Today</span>
                        <Zap class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="space-y-1">
                        <div class="text-2xl font-bold">{{ stats.executionsToday }}</div>
                        <p class="text-xs text-muted-foreground">Job executions</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div
                class="min-h-[60vh] flex-1 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
            >
                <div class="flex h-full flex-col">
                    <h2 class="mb-4 text-lg font-semibold">Recent Activity</h2>
                    <div class="flex flex-1 items-center justify-center text-muted-foreground">
                        <p>Activity feed will be displayed here</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
