<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Connectez-vous à votre compte "
        description="Entrez votre email et mot de passe ci-dessous pour vous connecter"
    >
        <Head title="Connexion" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <!-- Identifiants de test -->
        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20">
            <h3 class="mb-2 text-sm font-semibold text-blue-800 dark:text-blue-200">
                🔑 Identifiants de test
            </h3>
            <div class="space-y-1.5 text-xs text-blue-700 dark:text-blue-300">
                <div class="rounded bg-white/50 px-2 py-1.5 dark:bg-black/20">
                    <p class="font-medium mb-1">Admin :</p>
                    <p class="font-mono">Login:admin@example.com / Password: admin</p>
                </div>
                <div class="rounded bg-white/50 px-2 py-1.5 dark:bg-black/20">
                    <p class="font-medium mb-1">Client :</p>
                    <p class="font-mono">Login: client@example.com / Password: client</p>
                </div>
            </div>
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Adresse email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@exemple.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Mot de passe</Label>
                        <!-- <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            Mot de passe oublié ?
                        </TextLink> -->
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Mot de passe"
                    />
                    <InputError :message="errors.password" />
                </div>

                <!-- <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Se souvenir de moi</span>
                    </Label>
                </div> -->

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Se connecter
                </Button>
            </div>

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                Vous n'avez pas de compte ?
                <TextLink :href="register()" :tabindex="5">S'inscrire</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
