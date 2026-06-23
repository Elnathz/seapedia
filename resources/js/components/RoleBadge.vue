<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Repeat2 } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { select as selectRole } from '@/routes/role';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const roleLabel = (role: string) =>
    role.charAt(0).toUpperCase() + role.slice(1);
</script>

<template>
    <div v-if="auth.activeRole" class="flex items-center gap-2">
        <Badge variant="secondary" class="capitalize">
            {{ roleLabel(auth.activeRole) }}
        </Badge>
        <Link
            v-if="auth.roles.length > 1"
            :href="selectRole.url()"
            class="flex min-h-11 min-w-11 items-center justify-center gap-1 rounded-md text-xs text-muted-foreground hover:text-foreground"
            aria-label="Switch active role"
        >
            <Repeat2 class="size-4" />
            <span class="sr-only">Switch role</span>
        </Link>
    </div>
</template>
