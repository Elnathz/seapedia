<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Users } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    is_admin: boolean;
    roles: Role[];
    created_at: string;
}

interface Paginated {
    data: User[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    users: Paginated;
    filters: { q: string };
}>();

const search = ref(props.filters.q ?? '');

watch(search, (val) => {
    router.get('/admin/users', { q: val }, { preserveState: true, replace: true });
});
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <Users class="size-5" />
                </div>
                <div>
                    <h1 class="text-xl font-bold">Pengguna</h1>
                    <p class="text-sm text-muted-foreground">Total {{ users.total }} pengguna</p>
                </div>
            </div>

            <div class="mb-4">
                <Input v-model="search" placeholder="Cari nama atau email..." class="max-w-xs" />
            </div>

            <div class="overflow-x-auto rounded-lg border border-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Nama</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Peran</TableHead>
                            <TableHead>Daftar</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="users.data.length === 0">
                            <TableCell colspan="5" class="py-8 text-center text-muted-foreground">
                                Tidak ada pengguna ditemukan.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="user in users.data" :key="user.id">
                            <TableCell class="font-mono text-xs">{{ user.id }}</TableCell>
                            <TableCell class="font-medium">{{ user.name }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ user.email }}</TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <Badge v-if="user.is_admin" variant="destructive">admin</Badge>
                                    <Badge v-for="role in user.roles" :key="role.id" variant="secondary">{{ role.name }}</Badge>
                                </div>
                            </TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{ user.created_at?.slice(0, 10) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="mt-4 flex gap-1">
                <template v-for="link in users.links" :key="link.label">
                    <button
                        v-if="link.url"
                        type="button"
                        class="rounded border px-3 py-1 text-sm"
                        :class="link.active ? 'border-primary bg-primary text-white' : 'border-border hover:bg-muted'"
                        @click="router.get(link.url)"
                        v-html="link.label"
                    />
                    <span v-else class="rounded border border-border px-3 py-1 text-sm opacity-40" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
