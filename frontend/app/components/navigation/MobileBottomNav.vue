<script setup lang="ts">
const route = useRoute()
const cartStore = useCartStore()
const authStore = useAuthStore()
const { toPersianDigits } = usePersian()

const isHome = computed(() => route.path === '/')
const isCategories = computed(() => route.path.startsWith('/categories'))
const isProducts = computed(() => route.path.startsWith('/products'))
const isCart = computed(() => route.path === '/cart')

function handleUserClick() {
  if (!authStore.isAuthenticated) {
    authStore.openAuthModal()
  }
}
</script>

<template>
  <nav
    class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/90 dark:bg-neutral-900/90 backdrop-blur-lg border-t border-neutral-200/80 dark:border-neutral-800 shadow-lg px-2 py-1.5 transition-all"
    aria-label="ناوبری موبایل"
  >
    <div class="grid grid-cols-5 items-center max-w-md mx-auto">
      <!-- 1. Home -->
      <NuxtLink
        to="/"
        class="flex flex-col items-center justify-center min-h-12 py-1 rounded-xl transition-colors"
        :class="isHome ? 'text-primary' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'"
      >
        <UIcon
          :name="isHome ? 'i-lucide-home' : 'i-lucide-home'"
          class="size-5.5"
        />
        <span class="text-[10px] font-bold mt-1">خانه</span>
      </NuxtLink>

      <!-- 2. Categories -->
      <NuxtLink
        to="/categories"
        class="flex flex-col items-center justify-center min-h-12 py-1 rounded-xl transition-colors"
        :class="isCategories ? 'text-primary' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'"
      >
        <UIcon
          name="i-lucide-layout-grid"
          class="size-5.5"
        />
        <span class="text-[10px] font-bold mt-1">دسته‌ها</span>
      </NuxtLink>

      <!-- 3. Catalog -->
      <NuxtLink
        to="/products"
        class="flex flex-col items-center justify-center min-h-12 py-1 rounded-xl transition-colors"
        :class="isProducts ? 'text-primary' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'"
      >
        <UIcon
          name="i-lucide-store"
          class="size-5.5"
        />
        <span class="text-[10px] font-bold mt-1">محصولات</span>
      </NuxtLink>

      <!-- 4. Cart -->
      <NuxtLink
        to="/cart"
        class="relative flex flex-col items-center justify-center min-h-12 py-1 rounded-xl transition-colors"
        :class="isCart ? 'text-primary' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'"
      >
        <div class="relative">
          <UIcon
            name="i-lucide-shopping-bag"
            class="size-5.5"
          />
          <UBadge
            v-if="cartStore.itemsCount > 0"
            color="primary"
            size="xs"
            class="absolute -top-1.5 -right-2.5 font-bold min-w-4.5 h-4.5 flex items-center justify-center rounded-full text-[10px] px-1 shadow-xs"
          >
            {{ toPersianDigits(cartStore.itemsCount) }}
          </UBadge>
        </div>
        <span class="text-[10px] font-bold mt-1">سبد خرید</span>
      </NuxtLink>

      <!-- 5. Profile / Auth -->
      <template v-if="authStore.isAuthenticated">
        <UDropdownMenu
          v-if="authStore.user"
          :items="[
            [{
              label: authStore.user.full_name,
              icon: 'i-lucide-user',
              disabled: true
            }],
            [{
              label: 'پنل کاربری',
              icon: 'i-lucide-layout-dashboard',
              onSelect: () => navigateTo('/profile')
            }],
            [{
              label: 'خروج از حساب',
              icon: 'i-lucide-log-out',
              onSelect: () => authStore.logout()
            }]
          ]"
        >
          <button
            type="button"
            class="flex flex-col items-center justify-center min-h-12 py-1 rounded-xl text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors"
          >
            <UIcon
              name="i-lucide-user-check"
              class="size-5.5 text-primary"
            />
            <span class="text-[10px] font-bold mt-1 truncate max-w-16">
              {{ authStore.user.first_name || 'پروفایل' }}
            </span>
          </button>
        </UDropdownMenu>
        <div
          v-else
          class="flex flex-col items-center justify-center min-h-12 py-1"
        >
          <USkeleton class="size-5.5 rounded-full" />
          <USkeleton class="w-8 h-2 rounded mt-1.5" />
        </div>
      </template>

      <template v-else>
        <button
          type="button"
          class="flex flex-col items-center justify-center min-h-12 py-1 rounded-xl text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors"
          @click="handleUserClick"
        >
          <UIcon
            name="i-lucide-user"
            class="size-5.5"
          />
          <span class="text-[10px] font-bold mt-1">ورود</span>
        </button>
      </template>
    </div>
  </nav>
</template>
