<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { store } from '@/routes/role';

const props = defineProps<{
    roles: string[];
}>();

const form = useForm({ role: '' });

function selectRole(role: string) {
    form.role = role;
    form.post(store().url);
}
</script>

<template>
    <Head title="Choose your role" />

    <div
        class="flex min-h-svh items-center justify-center bg-background p-4"
    >
        <Dialog :open="true">
            <DialogContent
                class="sm:max-w-md"
                :show-close-button="false"
                @interact-outside.prevent
                @escape-key-down.prevent
            >
                <DialogHeader>
                    <DialogTitle>Choose how you want to continue</DialogTitle>
                    <DialogDescription>
                        Your account owns multiple roles. Pick one to
                        continue — you can switch later from the role
                        switcher.
                    </DialogDescription>
                </DialogHeader>

                <div class="flex flex-col gap-2">
                    <Button
                        v-for="role in props.roles"
                        :key="role"
                        type="button"
                        variant="outline"
                        class="justify-start capitalize"
                        :disabled="form.processing"
                        @click="selectRole(role)"
                    >
                        {{ role }}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
