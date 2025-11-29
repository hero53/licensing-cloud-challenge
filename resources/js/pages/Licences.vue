<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Key, Calendar, Users, Plus, CheckCircle, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

interface Licence {
    id: number;
    uld: string;
    wording: string;
    slug: string;
    description: string | null;
    max_apps: number;
    max_executions_per_24h: number;
    valid_from: string;
    valid_to: string;
    status: string;
    is_active: boolean;
    users_count: number;
}

interface LicenceStats {
    totalLicences: number;
    activeLicences: number;
    totalUsers: number;
}

defineProps<{
    stats: LicenceStats;
    licences: Licence[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Licences',
        href: '/licences',
    },
];

const isModalOpen = ref(false);
const form = ref({
    wording: '',
    description: '',
    max_apps: 3,
    max_executions_per_24h: 100,
    valid_from: '',
    valid_to: '',
});

const openModal = () => {
    const today = new Date().toISOString().split('T')[0];
    const nextYear = new Date();
    nextYear.setFullYear(nextYear.getFullYear() + 1);

    form.value.valid_from = today;
    form.value.valid_to = nextYear.toISOString().split('T')[0];
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.value = {
        wording: '',
        description: '',
        max_apps: 3,
        max_executions_per_24h: 100,
        valid_from: '',
        valid_to: '',
    };
};

const submitLicence = () => {
    router.post('/licences', form.value, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'ACTIVE':
            return 'text-green-600 bg-green-100 dark:bg-green-900/20';
        case 'EXPIRED':
            return 'text-red-600 bg-red-100 dark:bg-red-900/20';
        case 'PENDING':
            return 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/20';
        default:
            return 'text-gray-600 bg-gray-100 dark:bg-gray-900/20';
    }
};
</script>

<template>
    <Head title="Licences" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- Total Licences -->
                <div
                    class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Total Licences</span>
                        <Key class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="space-y-1">
                        <div class="text-2xl font-bold">{{ stats.totalLicences }}</div>
                        <p class="text-xs text-muted-foreground">Toutes les licences</p>
                    </div>
                </div>

                <!-- Active Licences -->
                <div
                    class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Licences Actives</span>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="space-y-1">
                        <div class="text-2xl font-bold">{{ stats.activeLicences }}</div>
                        <p class="text-xs text-muted-foreground">Actuellement valides</p>
                    </div>
                </div>

                <!-- Total Users -->
                <div
                    class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Utilisateurs</span>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="space-y-1">
                        <div class="text-2xl font-bold">{{ stats.totalUsers }}</div>
                        <p class="text-xs text-muted-foreground">Utilisateurs avec licence</p>
                    </div>
                </div>
            </div>

            <!-- Licences Grid -->
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight">Licences</h2>
                        <p class="text-sm text-muted-foreground">Gérez les licences de votre système</p>
                    </div>
                    <button
                        @click="openModal"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        <Plus class="h-4 w-4" />
                        Ajouter licence
                    </button>
                </div>

                <!-- Licences Cards -->
                <div v-if="licences.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="licence in licences"
                        :key="licence.id"
                        class="group relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-6 transition-all hover:border-primary/50 hover:shadow-lg dark:border-sidebar-border"
                    >
                        <!-- Gradient overlay on hover -->
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>

                        <div class="relative space-y-4">
                            <!-- Header with icon and status -->
                            <div class="flex items-start justify-between">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary transition-all group-hover:scale-110">
                                    <Key class="h-6 w-6" />
                                </div>
                                <div :class="['flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium', getStatusColor(licence.status)]">
                                    <CheckCircle v-if="licence.status === 'ACTIVE'" class="h-3.5 w-3.5" />
                                    <XCircle v-else class="h-3.5 w-3.5" />
                                    <span>{{ licence.status }}</span>
                                </div>
                            </div>

                            <!-- Licence Info -->
                            <div class="space-y-2">
                                <h3 class="line-clamp-1 text-lg font-semibold tracking-tight">
                                    {{ licence.wording }}
                                </h3>
                                <p v-if="licence.description" class="line-clamp-2 text-sm text-muted-foreground">
                                    {{ licence.description }}
                                </p>
                                <p v-else class="text-sm text-muted-foreground/60 italic">
                                    Aucune description
                                </p>
                            </div>

                            <!-- Limits -->
                            <div class="space-y-2 border-t border-sidebar-border/50 pt-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Max Apps:</span>
                                    <span class="font-semibold">{{ licence.max_apps }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Max Exec/24h:</span>
                                    <span class="font-semibold">{{ licence.max_executions_per_24h }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Utilisateurs:</span>
                                    <span class="font-semibold">{{ licence.users_count }}</span>
                                </div>
                            </div>

                            <!-- Validity Period -->
                            <div class="space-y-2 border-t border-sidebar-border/50 pt-4">
                                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                    <Calendar class="h-3.5 w-3.5 flex-shrink-0" />
                                    <span>Du {{ formatDate(licence.valid_from) }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                    <Calendar class="h-3.5 w-3.5 flex-shrink-0" />
                                    <span>Au {{ formatDate(licence.valid_to) }}</span>
                                </div>
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
                        <Key class="mx-auto h-12 w-12 text-muted-foreground/50" />
                        <h3 class="mt-4 text-lg font-semibold">Aucune licence</h3>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Créez votre première licence pour commencer
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour créer une licence -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeModal"
        >
            <div class="w-full max-w-md rounded-xl bg-card p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold">Nouvelle Licence</h3>
                    <button
                        @click="closeModal"
                        class="rounded-lg p-1 hover:bg-muted"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitLicence" class="space-y-4">
                    <div>
                        <label for="wording" class="block text-sm font-medium mb-2">
                            Nom de la licence <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="wording"
                            v-model="form.wording"
                            type="text"
                            required
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Licence Premium"
                        />
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium mb-2">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="2"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Description de la licence..."
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="max_apps" class="block text-sm font-medium mb-2">
                                Max Applications <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="max_apps"
                                v-model.number="form.max_apps"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label for="max_executions" class="block text-sm font-medium mb-2">
                                Max Exec/24h <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="max_executions"
                                v-model.number="form.max_executions_per_24h"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="valid_from" class="block text-sm font-medium mb-2">
                                Date début <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="valid_from"
                                v-model="form.valid_from"
                                type="date"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label for="valid_to" class="block text-sm font-medium mb-2">
                                Date fin <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="valid_to"
                                v-model="form.valid_to"
                                type="date"
                                required
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeModal"
                            class="rounded-lg border border-input px-4 py-2 text-sm font-medium hover:bg-muted"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            <Plus class="h-4 w-4" />
                            Créer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
