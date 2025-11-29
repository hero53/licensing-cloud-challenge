<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Key, Activity, Zap, Rocket, User } from 'lucide-vue-next';

interface DashboardStats {
    activeLicences: number;
    totalApplications: number;
    executionsToday: number;
}

interface Application {
    id: number;
    uld: string;
    wording: string;
    slug: string;
    description: string | null;
    executions_24h: number;
    licence: {
        wording: string;
        user: {
            name: string;
        };
    };
}

defineProps<{
    stats: DashboardStats;
    applications: Application[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const executeJob = (applicationId: number) => {
    router.post(`/dashboard/execute-job/${applicationId}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Le message de succès sera affiché automatiquement via les notifications Laravel
        },
    });
};
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

            <!-- Applications Grid -->
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight">Applications</h2>
                        <p class="text-sm text-muted-foreground">Monitor your application executions in real-time</p>
                    </div>
                </div>

                <!-- Applications Cards -->
                <div v-if="applications.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="app in applications"
                        :key="app.id"
                        class="group relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-6 transition-all hover:border-primary/50 hover:shadow-lg dark:border-sidebar-border"
                    >
                        <!-- Gradient overlay on hover -->
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>

                        <div class="relative space-y-4">
                            <!-- Header with icon -->
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary transition-all group-hover:scale-110">
                                    <Rocket class="h-6 w-6" />
                                </div>
                                <div class="flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1.5 text-xs font-medium text-primary">
                                    <Zap class="h-3.5 w-3.5" />
                                    <span>{{ app.executions_24h }}</span>
                                </div>
                            </div>

                            <!-- Application Info -->
                            <div class="space-y-2">
                                <h3 class="line-clamp-1 text-lg font-semibold tracking-tight">
                                    {{ app.wording }}
                                </h3>
                                <p v-if="app.description" class="line-clamp-2 text-sm text-muted-foreground">
                                    {{ app.description }}
                                </p>
                                <p v-else class="text-sm text-muted-foreground/60 italic">
                                    No description available
                                </p>
                            </div>

                            <!-- Footer Info -->
                            <div class="space-y-2 border-t border-sidebar-border/50 pt-4">
                                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                    <Key class="h-3.5 w-3.5 flex-shrink-0" />
                                    <span class="line-clamp-1">{{ app.licence.wording }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                    <User class="h-3.5 w-3.5 flex-shrink-0" />
                                    <span class="line-clamp-1">{{ app.licence.user.name }}</span>
                                </div>
                            </div>

                            <!-- Execution count label -->
                            <div class="text-xs text-muted-foreground">
                                <span class="font-medium">Executions:</span> Last 24 hours
                            </div>

                            <!-- Execution Job Button -->
                            <div class="flex justify-center pt-2">
                                <button
                                    type="button"
                                    @click="executeJob(app.id)"
                                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                >
                                    <Zap class="h-4 w-4" />
                                    Exécution Job
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-else
                    class="flex min-h-[40vh] items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 bg-card dark:border-sidebar-border"
                >
                    <div class="text-center">
                        <Rocket class="mx-auto h-12 w-12 text-muted-foreground/50" />
                        <h3 class="mt-4 text-lg font-semibold">No applications yet</h3>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Applications will appear here once they are registered
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
