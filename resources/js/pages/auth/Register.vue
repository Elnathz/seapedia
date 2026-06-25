<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
    roles: string[];
}>();

defineOptions({
    layout: {
        title: 'Buat akun baru',
        description: 'Isi data di bawah untuk bergabung ke SEAPEDIA',
    },
});
</script>

<template>
    <Head title="Daftar — SEAPEDIA" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="name">Nama lengkap</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Nama lengkap kamu"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="username">Username</Label>
                <Input
                    id="username"
                    type="text"
                    required
                    :tabindex="2"
                    autocomplete="username"
                    name="username"
                    placeholder="username_kamu"
                />
                <InputError :message="errors.username" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Alamat email</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="3"
                    autocomplete="email"
                    name="email"
                    placeholder="email@kampus.ac.id"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Nomor HP <span class="text-muted-foreground">(opsional)</span></Label>
                <Input
                    id="phone"
                    type="text"
                    :tabindex="4"
                    autocomplete="tel"
                    name="phone"
                    placeholder="08xxxxxxxxxx"
                />
                <InputError :message="errors.phone" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Kata sandi</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="5"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Min. 8 karakter"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Konfirmasi kata sandi</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="6"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Ulangi kata sandi"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="grid gap-2">
                <Label>Pilih peran</Label>
                <p class="text-xs text-muted-foreground">Pilih satu atau lebih — bisa diubah nanti.</p>
                <div class="flex flex-col gap-3">
                    <Label
                        v-for="role in roles"
                        :key="role"
                        :for="`role-${role}`"
                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-border p-3 font-normal capitalize transition-colors hover:bg-muted/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5"
                    >
                        <Checkbox
                            :id="`role-${role}`"
                            name="roles[]"
                            :value="role"
                        />
                        <span>{{ role }}</span>
                    </Label>
                </div>
                <InputError :message="errors.roles" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="7"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" class="mr-2" />
                Buat akun
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="8"
            >Masuk</TextLink>
        </div>
    </Form>
</template>
