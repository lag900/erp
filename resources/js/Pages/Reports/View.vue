<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    type: String,
    assets: Array,
    department: Object,
    categories: Array,
    filters: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const activeFilters = ref({
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
    category_id: props.filters.category_id ?? '',
    status: props.filters.status ?? '',
});

const applyFilters = () => {
    router.get(route('reports.view', props.type), activeFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(activeFilters, () => {
    applyFilters();
}, { deep: true });

const exportReport = (format) => {
    window.open(route('reports.view', { 
        type: props.type, 
        ...activeFilters.value, 
        export: format 
    }));
};

const getArabicTitle = (type) => {
    const titles = {
        inventory: 'تقرير جرد الأصول',
        custody: 'تقرير العهدة',
        movement: 'تقرير حركة الأصول',
        added: 'تقرير الأصول المضافة',
        damaged: 'تقرير التالف والكهنة',
        summary: 'ملخص الأصول',
    };
    return titles[type] || 'تقرير أصول';
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head :title="'Report: ' + type" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between px-1 no-print">
                <div class="flex items-center gap-5">
                    <Link :href="route('reports.index')" class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 transition-all hover:bg-slate-50 hover:text-[#3d4adb] shadow-soft group">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-800 leading-tight">{{ getArabicTitle(type) }}</h2>
                        <p class="mt-1 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ type.replace('_', ' ') }} Precision Audit</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="printReport" class="inline-flex h-11 items-center px-6 bg-white border border-slate-200 rounded-xl font-black text-[10px] text-slate-600 uppercase tracking-widest hover:bg-slate-50 shadow-soft transition-all">
                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Print Review
                    </button>
                    <button @click="exportReport('pdf')" class="inline-flex h-11 items-center px-6 bg-rose-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-rose-700 shadow-premium transition-all active:scale-95">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        Export PDF
                    </button>
                    <button @click="exportReport('excel')" class="inline-flex h-11 items-center px-6 bg-emerald-600 border border-transparent rounded-xl font-black text-[10px] text-white uppercase tracking-widest hover:bg-emerald-700 shadow-premium transition-all active:scale-95">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Spreadsheet
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-100/50 min-h-screen">
            <div class="mx-auto max-w-[1000px] sm:px-6 lg:px-8">
                <!-- Governance Filter Protocol (Hidden on Print) -->
                <div class="bg-white p-8 rounded-[32px] shadow-soft border border-emerald-100/30 mb-10 no-print">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="h-8 w-1 bg-[#3d4adb] rounded-full"></div>
                        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Audit Configuration</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[#3d4adb] block">Temporal Start</label>
                            <input type="date" v-model="activeFilters.date_from" class="w-full text-[13px] font-bold h-12 rounded-xl border-slate-100 bg-slate-50 focus:bg-white focus:border-[#3d4adb]/30 focus:ring-[#3d4adb]/10 transition-all cursor-pointer" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[#3d4adb] block">Temporal End</label>
                            <input type="date" v-model="activeFilters.date_to" class="w-full text-[13px] font-bold h-12 rounded-xl border-slate-100 bg-slate-50 focus:bg-white focus:border-[#3d4adb]/30 focus:ring-[#3d4adb]/10 transition-all cursor-pointer" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[#3d4adb] block">Taxonomy Filter</label>
                            <select v-model="activeFilters.category_id" class="w-full text-[13px] font-bold h-12 rounded-xl border-slate-100 bg-slate-50 focus:bg-white focus:border-[#3d4adb]/30 focus:ring-[#3d4adb]/10 transition-all">
                                <option value="">Universal Classifications</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[#3d4adb] block">Health Status</label>
                            <select v-model="activeFilters.status" class="w-full text-[13px] font-bold h-12 rounded-xl border-slate-100 bg-slate-50 focus:bg-white focus:border-[#3d4adb]/30 focus:ring-[#3d4adb]/10 transition-all">
                                <option value="">All Operational States</option>
                                <option value="active" class="font-bold text-emerald-600">Active (Operational)</option>
                                <option value="maintenance" class="font-bold text-blue-600">Maintenance</option>
                                <option value="damaged" class="font-bold text-rose-600">Damaged (Faulty)</option>
                                <option value="retired" class="font-bold text-slate-500">Retired (Decommissioned)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Official Report Layout -->
                <div class="bg-white shadow-xl ring-1 ring-gray-900/5 print:shadow-none print:ring-0 overflow-hidden print-container" dir="rtl">
                    <div class="p-16 sm:p-20">
                        <!-- Branding Header -->
                        <div class="flex items-start justify-between border-b-4 border-black pb-10 mb-12">
                            <div class="w-32 h-32 flex-shrink-0">
                                <img v-if="department.university_logo_url" :src="department.university_logo_url" class="h-full w-full object-contain" />
                                <div v-else class="h-full w-full bg-gray-100 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300 text-[10px] font-bold text-gray-400">LOGO HERE</div>
                            </div>
                            
                            <div class="text-center flex-1 px-8">
                                <h1 class="text-3xl font-black text-black leading-tight mb-2">المملكة العربية السعودية</h1>
                                <h2 class="text-2xl font-bold text-gray-900 mb-1">جامعة الملك خالد</h2>
                                <h3 class="text-xl font-medium text-gray-700">{{ department.arabic_name || department.name }}</h3>
                                
                                <div class="mt-8">
                                    <span class="inline-block text-2xl font-black bg-black text-white px-8 py-2 rounded-sm transform uppercase tracking-wide decoration-solid underline decoration-4">
                                        {{ getArabicTitle(type) }}
                                    </span>
                                </div>
                            </div>

                            <div class="w-32 h-32 flex-shrink-0">
                                <img v-if="department.department_logo_url" :src="department.department_logo_url" class="h-full w-full object-contain" />
                            </div>
                        </div>

                        <!-- Report Information Row -->
                        <div class="grid grid-cols-4 gap-0 mb-12 border border-gray-200 rounded-lg overflow-hidden text-right">
                            <div class="p-4 border-l border-gray-200 bg-gray-50/50">
                                <label class="block text-[10px] font-black text-gray-400 uppercase pb-1 border-b border-gray-100 mb-2">تاريخ التقرير</label>
                                <span class="text-sm font-bold text-gray-900 leading-none">{{ new Date().toLocaleDateString('ar-SA') }}</span>
                            </div>
                            <div class="p-4 border-l border-gray-200 bg-gray-50/50">
                                <label class="block text-[10px] font-black text-gray-400 uppercase pb-1 border-b border-gray-100 mb-2">القسم المختص</label>
                                <span class="text-sm font-bold text-gray-900 leading-none">{{ department.arabic_name || department.name }}</span>
                            </div>
                            <div class="p-4 border-l border-gray-200 bg-gray-50/50">
                                <label class="block text-[10px] font-black text-gray-400 uppercase pb-1 border-b border-gray-100 mb-2">عدد العهد المسجلة</label>
                                <span class="text-sm font-bold text-gray-900 leading-none">{{ assets.length }} أصول</span>
                            </div>
                            <div class="p-4 bg-gray-50/50">
                                <label class="block text-[10px] font-black text-gray-400 uppercase pb-1 border-b border-gray-100 mb-2">بواسطة الموظف</label>
                                <span class="text-sm font-bold text-gray-900 leading-none">{{ user.name }}</span>
                            </div>
                        </div>

                        <!-- Summary Table -->
                        <div v-if="type === 'summary'" class="overflow-x-auto min-h-[400px]">
                            <table class="w-full text-right border-collapse border border-black">
                                <thead class="bg-gray-50 border-b border-black">
                                    <tr class="text-black text-xs font-black">
                                        <th class="p-3 border border-black text-center w-12">م</th>
                                        <th class="p-3 border border-black text-right">تصنيف الأصول / Asset Category</th>
                                        <th class="p-3 border border-black text-center">إجمالي العدد / Total Count</th>
                                        <th class="p-3 border border-black text-center">النسبة المئوية / Percentage</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm font-medium">
                                    <tr v-for="(item, index) in assets" :key="item.category_id" class="border-b border-gray-300">
                                        <td class="p-3 border border-black text-center text-gray-500">{{ index + 1 }}</td>
                                        <td class="p-3 border border-black font-bold">{{ item.category?.name || 'Uncategorized' }}</td>
                                        <td class="p-3 border border-black text-center font-mono font-bold text-lg">{{ item.total }}</td>
                                        <td class="p-3 border border-black text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden max-w-[100px]">
                                                    <div class="h-full bg-black" :style="{ width: (item.total / assets.reduce((a, b) => a + b.total, 0) * 100) + '%' }"></div>
                                                </div>
                                                <span class="text-xs font-black">{{ ((item.total / assets.reduce((a, b) => a + b.total, 0)) * 100).toFixed(1) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50 font-black">
                                    <tr>
                                        <td colspan="2" class="p-4 border border-black text-right text-base uppercase">الإجمالي العام / GRAND TOTAL</td>
                                        <td class="p-4 border border-black text-center text-xl font-mono">{{ assets.reduce((a, b) => a + b.total, 0) }}</td>
                                        <td class="p-4 border border-black text-center">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Data Table (Standard) -->
                        <div v-else class="overflow-x-auto min-h-[400px]">
                            <table class="w-full text-right border-collapse border border-black">
                                <thead class="bg-gray-50 border-b border-black">
                                    <tr class="text-black text-xs font-black">
                                        <th class="p-3 border border-black text-center w-12">م</th>
                                        <th class="p-3 border border-black text-center w-32">رقم الأصل</th>
                                        <th class="p-3 border border-black">اسم الصنف / المادة</th>
                                        <th class="p-3 border border-black text-center">التصنيف</th>
                                        <th class="p-3 border border-black">الموقع الحالي</th>
                                        <th class="p-3 border border-black text-center">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm font-medium">
                                    <tr v-for="(asset, index) in assets" :key="asset.id" class="border-b border-gray-300 hover:bg-gray-50/50">
                                        <td class="p-3 border border-black text-center text-gray-500">{{ index + 1 }}</td>
                                        <td class="p-3 border border-black font-mono font-bold text-center">{{ asset.asset_code || asset.code }}</td>
                                        <td class="p-3 border border-black font-bold">{{ asset.name }}</td>
                                        <td class="p-3 border border-black text-center">{{ asset.category?.name || '-' }}</td>
                                        <td class="p-3 border border-black">
                                            {{ asset.building?.name }} / {{ asset.room?.name }}
                                        </td>
                                        <td class="p-3 border border-black text-center">
                                            <span class="text-[10px] font-black uppercase">
                                                {{ asset.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="assets.length === 0">
                                        <td colspan="6" class="p-20 text-center text-gray-400 italic">
                                            لا توجد أصول مسجلة حالياً ضمن المعايير المختارة
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Formal Signature Section -->
                        <div class="mt-24 grid grid-cols-3 gap-16">
                            <div class="text-center group">
                                <p class="text-base font-black text-gray-900 mb-16">إعداد الموظف المختص / Prepared By</p>
                                <div class="w-full border-b-2 border-dashed border-gray-400 mb-2"></div>
                                <p class="text-xs text-gray-500">الاسم والتوقيع: ........................................</p>
                            </div>
                            <div class="text-center group">
                                <p class="text-base font-black text-gray-900 mb-16">مراجعة مدير القسم / Reviewed By</p>
                                <div class="w-full border-b-2 border-dashed border-gray-400 mb-2"></div>
                                <p class="text-xs text-gray-500">الاسم والتوقيع: ........................................</p>
                            </div>
                            <div class="text-center group">
                                <p class="text-base font-black text-gray-900 mb-16">اعتماد العميد / Approved By</p>
                                <div class="w-full border-b-2 border-dashed border-gray-400 mb-2"></div>
                                <p class="text-xs text-gray-500">الاسم والختم: ........................................</p>
                            </div>
                        </div>

                        <!-- Portal Footer -->
                        <div class="mt-32 pt-6 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400 font-bold uppercase tracking-widest no-print">
                            <span>نظام إدارة أصول جامعة الملك خالد (ERB)</span>
                            <span>{{ new Date().toLocaleString() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    .no-print { display: none !important; }
    .print\:shadow-none { box-shadow: none !important; }
    body { background: white !important; }
    .py-12 { padding-top: 0 !important; padding-bottom: 0 !important; }
    .mx-auto { max-width: 100% !important; margin: 0 !important; }
    .sm\:p-20 { padding: 1cm !important; }
    .bg-gray-100\/50 { background: white !important; }
}

.print-container {
    background: white;
    min-height: 29.7cm; /* A4 aspect */
}

table {
    page-break-inside: auto;
}
tr {
    page-break-inside: avoid;
    page-break-after: auto;
}
thead {
    display: table-header-group;
}
</style>

