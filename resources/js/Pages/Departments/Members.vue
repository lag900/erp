<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import GlobalCheckbox from '@/Components/GlobalCheckbox.vue';

const props = defineProps({
    department: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    memberships: {
        type: Object,
        required: true,
    },
});

const search = ref('');
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (permission) => permissions.value.includes(permission);

const membersForm = useForm({
    members: props.users.map((user) => {
        const membership = props.memberships[user.id];
        return {
            user_id: user.id,
            is_member: Boolean(membership),
            role_id: membership?.role_id ?? props.roles[0]?.id ?? null,
            is_default: membership?.is_default ?? false,
        };
    }),
});

const usersById = computed(() => {
    return props.users.reduce((acc, user) => {
        acc[user.id] = user;
        return acc;
    }, {});
});

const filteredMembers = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return membersForm.members;
    }

    return membersForm.members.filter((member) => {
        const user = usersById.value[member.user_id];
        if (!user) {
            return false;
        }
        return (
            user.name.toLowerCase().includes(term) ||
            user.email.toLowerCase().includes(term)
        );
    });
});

const toggleMember = (member) => {
    member.is_member = !member.is_member;
    if (!member.is_member) {
        member.is_default = false;
    }
};

const submit = () => {
    const payload = membersForm.members
        .filter((member) => member.is_member)
        .map((member) => ({
            user_id: member.user_id,
            role_id: member.role_id,
            is_default: member.is_default,
        }));

    membersForm
        .transform(() => ({ members: payload }))
        .put(route('departments.members.update', props.department.id), {
            preserveScroll: true,
            onFinish: () => membersForm.transform((data) => data),
        });
};
</script>

<template>
    <Head :title="`Members - ${department.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Members - {{ department.name }}
                </h2>
                <Link
                    :href="route('departments.index')"
                    class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                >
                    Back to Departments
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <TextInput
                        v-model="search"
                        type="text"
                        class="mt-1 block w-full sm:max-w-sm"
                        placeholder="Search users..."
                    />
                    <PrimaryButton
                        v-if="can('department-assign-users')"
                        :disabled="membersForm.processing"
                        @click="submit"
                    >
                        Save Members
                    </PrimaryButton>
                </div>

                <div class="overflow-hidden rounded bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3 font-medium">Member</th>
                                <th class="px-4 py-3 font-medium">Role</th>
                                <th class="px-4 py-3 font-medium">Default</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="member in filteredMembers"
                                :key="member.user_id"
                                :class="member.is_member ? 'bg-white' : 'bg-gray-50'"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <GlobalCheckbox
                                            :checked="member.is_member"
                                            :id="'member-' + member.user_id"
                                            @update:checked="toggleMember(member)"
                                        />
                                        <div>
                                            <p class="text-gray-800">
                                                {{ usersById[member.user_id]?.name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ usersById[member.user_id]?.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="max-w-xs">
                                        <InputLabel
                                            :for="`role-${member.user_id}`"
                                            value="Role"
                                            class="sr-only"
                                        />
                                        <select
                                            :id="`role-${member.user_id}`"
                                            v-model="member.role_id"
                                            :disabled="!member.is_member || !can('department-assign-users')"
                                            class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                        >
                                            <option value="" disabled>Select role</option>
                                            <option
                                                v-for="role in roles"
                                                :key="role.id"
                                                :value="role.id"
                                            >
                                                {{ role.name }}
                                            </option>
                                        </select>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <label class="flex items-center gap-2 text-sm text-gray-600">
                                        <GlobalCheckbox
                                            v-model:checked="member.is_default"
                                            :id="'default-' + member.user_id"
                                        />
                                        Default for this user
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
