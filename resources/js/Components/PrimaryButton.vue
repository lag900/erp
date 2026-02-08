<template>
    <button
        class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white shadow-sm shadow-primary/30 transition-all duration-200 
               hover:bg-primary-hover hover:shadow-md hover:shadow-primary/40 hover:-translate-y-0.5
               focus:outline-none focus:ring-4 focus:ring-primary/20 focus:bg-primary-hover
               active:scale-[0.98] active:shadow-sm"
    >
        <slot />
    </button>
</template>
