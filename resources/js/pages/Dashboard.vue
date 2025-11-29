<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Key, Activity, Zap, Rocket, User, Plus, AlertTriangle, TrendingUp, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface DashboardStats {
    userLicence: {
        wording: string;
        description: string | null;
        validTo: string;
    };
    usedApplications: number;
    maxApplications: number;
    executionsToday: number;
    maxExecutionsPerDay: number;
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

interface AvailableLicence {
    id: number;
    wording: string;
    description: string | null;
    max_apps: number;
    max_executions_per_24h: number;
}

const props = defineProps<{
    stats: DashboardStats;
    applications: Application[];
    availableLicences?: AvailableLicence[];
    currentLicenceId?: number;
    hasCustomLicence?: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const isModalOpen = ref(false);
const isUpgradeModalOpen = ref(false);
const selectedLicence = ref<number | null>(null);
const isCustomLicence = ref(false);

const form = ref({
    wording: '',
    description: '',
});

const customLicenceForm = ref({
    wording: '',
    max_apps: 5,
    max_executions_per_24h: 200,
});

// Vérifier si les limites sont atteintes
const appsLimitReached = computed(() => props.stats.usedApplications >= props.stats.maxApplications);
const executionsLimitReached = computed(() => props.stats.executionsToday >= props.stats.maxExecutionsPerDay);
const shouldShowUpgradeAlert = computed(() => appsLimitReached.value || executionsLimitReached.value);

const openModal = () => {
    if (appsLimitReached.value) {
        openUpgradeModal();
        return;
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.value = {
        wording: '',
        description: '',
    };
};

const submitApplication = () => {
    router.post('/dashboard/applications', form.value, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

const openUpgradeModal = () => {
    isUpgradeModalOpen.value = true;
};

const closeUpgradeModal = () => {
    isUpgradeModalOpen.value = false;
    selectedLicence.value = null;
    isCustomLicence.value = false;
    customLicenceForm.value = {
        wording: '',
        max_apps: 5,
        max_executions_per_24h: 200,
    };
};

const selectCustomLicence = () => {
    // Ne pas permettre de créer une licence personnalisée si l'utilisateur en a déjà créé une
    if (props.hasCustomLicence) {
        return;
    }
    isCustomLicence.value = true;
    selectedLicence.value = null;
};

const selectPredefinedLicence = (licenceId: number) => {
    // Ne pas permettre de sélectionner la licence actuelle
    if (licenceId === props.currentLicenceId) {
        return;
    }
    isCustomLicence.value = false;
    selectedLicence.value = licenceId;
};

const upgradeLicence = () => {
    if (isCustomLicence.value) {
        // Créer et upgrader vers une licence personnalisée
        router.post('/dashboard/upgrade-licence', {
            is_custom: true,
            wording: customLicenceForm.value.wording,
            max_apps: customLicenceForm.value.max_apps,
            max_executions_per_24h: customLicenceForm.value.max_executions_per_24h,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                closeUpgradeModal();
            },
        });
    } else if (selectedLicence.value) {
        // Upgrader vers une licence prédéfinie
        router.post('/dashboard/upgrade-licence', {
            is_custom: false,
            licence_id: selectedLicence.value,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                closeUpgradeModal();
            },
        });
    }
};

const executeJob = (applicationId: number) => {
    router.post(`/dashboard/execute-job/${applicationId}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Le message de succès sera affiché automatiquement via les notifications Laravel
        },
    });
};

const deleteApplication = (applicationId: number, applicationName: string) => {
    if (confirm(`Êtes-vous sûr de vouloir désactiver l'application "${applicationName}" ?`)) {
        router.delete(`/dashboard/applications/${applicationId}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Upgrade Alert -->
            <div
                v-if="shouldShowUpgradeAlert"
                class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20"
            >
                <div class="flex items-start gap-3">
                    <AlertTriangle class="h-5 w-5 flex-shrink-0 text-yellow-600 dark:text-yellow-500" />
                    <div class="flex-1">
                        <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">
                            Limite atteinte !
                        </h3>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                            <span v-if="appsLimitReached">
                                Vous avez atteint la limite d'applications ({{ stats.usedApplications }}/{{ stats.maxApplications }}).
                            </span>
                            <span v-if="executionsLimitReached">
                                Vous avez atteint la limite d'exécutions aujourd'hui ({{ stats.executionsToday }}/{{ stats.maxExecutionsPerDay }}).
                            </span>
                            Mettez à niveau votre licence pour continuer.
                        </p>
                        <button
                            @click="openUpgradeModal"
                            class="mt-3 inline-flex items-center gap-2 rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-700 dark:bg-yellow-500 dark:hover:bg-yellow-600"
                        >
                            <TrendingUp class="h-4 w-4" />
                            Upgrader ma licence
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- User Licence -->
                <div
                    class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Ma Licence</span>
                        <Key class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="space-y-1">
                        <div class="text-2xl font-bold">{{ stats.userLicence.wording }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.userLicence.description || 'Licence active' }}
                        </p>
                    </div>
                </div>

                <!-- Applications Usage -->
                <div
                    class="flex flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Applications</span>
                        <Activity class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="space-y-1">
                        <div class="text-2xl font-bold">
                            {{ stats.usedApplications }}/{{ stats.maxApplications }}
                        </div>
                        <p class="text-xs text-muted-foreground">Applications utilisées</p>
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
                        <div class="text-2xl font-bold">
                            {{ stats.executionsToday }}/{{ stats.maxExecutionsPerDay }}
                        </div>
                        <p class="text-xs text-muted-foreground">Exécutions aujourd'hui</p>
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
                    <button
                        @click="openModal"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        <Plus class="h-4 w-4" />
                        Ajouter application
                    </button>
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

                            <!-- Action Buttons -->
                            <div class="flex gap-2 pt-2">
                                <button
                                    type="button"
                                    @click="executeJob(app.id)"
                                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                >
                                    <Zap class="h-4 w-4" />
                                    Exécution Job
                                </button>
                                <button
                                    type="button"
                                    @click="deleteApplication(app.id, app.wording)"
                                    class="inline-flex items-center justify-center rounded-lg bg-destructive px-3 py-2 text-sm font-medium text-destructive-foreground transition-all hover:bg-destructive/90 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-destructive focus:ring-offset-2"
                                    title="Désactiver l'application"
                                >
                                    <Trash2 class="h-4 w-4" />
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

        <!-- Modal pour créer une application -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeModal"
        >
            <div class="w-full max-w-md rounded-xl bg-card p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold">Nouvelle Application</h3>
                    <button
                        @click="closeModal"
                        class="rounded-lg p-1 hover:bg-muted"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitApplication" class="space-y-4">
                    <div>
                        <label for="wording" class="block text-sm font-medium mb-2">
                            Nom de l'application
                        </label>
                        <input
                            id="wording"
                            v-model="form.wording"
                            type="text"
                            required
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Mon Application"
                        />
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium mb-2">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Description de votre application..."
                        ></textarea>
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

        <!-- Modal pour upgrader la licence -->
        <div
            v-if="isUpgradeModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeUpgradeModal"
        >
            <div class="w-full max-w-2xl rounded-xl bg-card p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold">Upgrader votre licence</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Choisissez une nouvelle licence pour augmenter vos limites
                        </p>
                    </div>
                    <button
                        @click="closeUpgradeModal"
                        class="rounded-lg p-1 hover:bg-muted"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Option Licence Personnalisée -->
                        <div
                            @click="selectCustomLicence"
                            :class="[
                                'rounded-xl border-2 p-4 transition-all relative',
                                hasCustomLicence
                                    ? 'opacity-50 cursor-not-allowed border-sidebar-border/50 bg-muted/20'
                                    : isCustomLicence
                                        ? 'cursor-pointer border-primary bg-primary/5'
                                        : 'cursor-pointer border-sidebar-border/70 hover:border-primary/50'
                            ]"
                        >
                            <!-- Badge si déjà créée -->
                            <div
                                v-if="hasCustomLicence"
                                class="absolute -top-2 -right-2 rounded-full bg-muted px-3 py-1 text-xs font-medium text-muted-foreground border border-sidebar-border/70"
                            >
                                Déjà créée
                            </div>

                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 to-pink-500">
                                        <TrendingUp class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">Licence Personnalisée</h4>
                                        <p class="text-xs text-muted-foreground">
                                            Définissez vos propres limites
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="isCustomLicence && !hasCustomLicence"
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-primary"
                                >
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Licences Prédéfinies -->
                        <div
                            v-for="licence in availableLicences"
                            :key="licence.id"
                            @click="selectPredefinedLicence(licence.id)"
                            :class="[
                                'rounded-xl border-2 p-4 transition-all relative',
                                licence.id === currentLicenceId
                                    ? 'opacity-50 cursor-not-allowed border-sidebar-border/50 bg-muted/20'
                                    : selectedLicence === licence.id && !isCustomLicence
                                        ? 'cursor-pointer border-primary bg-primary/5'
                                        : 'cursor-pointer border-sidebar-border/70 hover:border-primary/50'
                            ]"
                        >
                            <!-- Badge "Licence Actuelle" si c'est la licence en cours -->
                            <div
                                v-if="licence.id === currentLicenceId"
                                class="absolute -top-2 -right-2 rounded-full bg-muted px-3 py-1 text-xs font-medium text-muted-foreground border border-sidebar-border/70"
                            >
                                Licence Actuelle
                            </div>

                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                        <Key class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">{{ licence.wording }}</h4>
                                        <p v-if="licence.description" class="text-xs text-muted-foreground">
                                            {{ licence.description }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="selectedLicence === licence.id && licence.id !== currentLicenceId"
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-primary"
                                >
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>

                            <div class="mt-4 space-y-2 border-t border-sidebar-border/50 pt-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Max Applications:</span>
                                    <span class="font-semibold">{{ licence.max_apps }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Max Exec/24h:</span>
                                    <span class="font-semibold">{{ licence.max_executions_per_24h }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire pour Licence Personnalisée -->
                    <div v-if="isCustomLicence" class="rounded-xl border border-primary/30 bg-primary/5 p-4">
                        <h4 class="mb-3 font-semibold">Configurez votre licence personnalisée</h4>
                        <div class="space-y-3">
                            <div>
                                <label for="custom_wording" class="block text-sm font-medium mb-1">
                                    Nom de la licence <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="custom_wording"
                                    v-model="customLicenceForm.wording"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                    placeholder="Ma Licence Pro"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="custom_max_apps" class="block text-sm font-medium mb-1">
                                        Max Applications <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="custom_max_apps"
                                        v-model.number="customLicenceForm.max_apps"
                                        type="number"
                                        min="1"
                                        required
                                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                    />
                                </div>
                                <div>
                                    <label for="custom_max_exec" class="block text-sm font-medium mb-1">
                                        Max Exec/24h <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="custom_max_exec"
                                        v-model.number="customLicenceForm.max_executions_per_24h"
                                        type="number"
                                        min="1"
                                        required
                                        class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-sidebar-border/50 pt-4">
                        <button
                            type="button"
                            @click="closeUpgradeModal"
                            class="rounded-lg border border-input px-4 py-2 text-sm font-medium hover:bg-muted"
                        >
                            Annuler
                        </button>
                        <button
                            @click="upgradeLicence"
                            :disabled="!selectedLicence && !isCustomLicence"
                            :class="[
                                'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                (selectedLicence || isCustomLicence)
                                    ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                                    : 'bg-muted text-muted-foreground cursor-not-allowed'
                            ]"
                        >
                            <TrendingUp class="h-4 w-4" />
                            Confirmer l'upgrade
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
