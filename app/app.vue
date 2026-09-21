<script setup lang="ts">
const authStore = useAuthStore()
const cartStore = useCartStore()
const settingsStore = useSettingsStore()
const { toPersianDigits } = usePersian()

// Fetch public store settings in SSR/initial load
await useAsyncData('app-settings', () => settingsStore.fetchSettings())

useHead({
  htmlAttrs: {
    dir: 'rtl',
    lang: 'fa-IR'
  },
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5' },
    { name: 'description', content: 'خرید آنلاین باکیفیت‌ترین محصولات آرایشی، مراقبت پوست و مو با تضمین اصالت کالا و ارسال سریع' }
  ],
  link: [
    { rel: 'icon', href: settingsStore.settings.store_favicon || '/favicon.ico' }
  ]
})

const title = computed(() => settingsStore.settings.store_name)
const description = computed(() => settingsStore.settings.store_slogan || 'خرید آنلاین باکیفیت‌ترین محصولات آرایشی، مراقبت پوست و مو با تضمین اصالت کالا و ارسال سریع')

useSeoMeta({
  title,
  description,
  ogTitle: title,
  ogDescription: description
})

// Fetch cart and user on mount
onMounted(() => {
  cartStore.fetchCart()
  if (authStore.isAuthenticated) {
    authStore.fetchUser()
  }
})
</script>

<template>
  <UApp>
    <UHeader>
      <template #left>
        <NuxtLink
          to="/"
          class="focus-visible:outline-3 outline-primary/25 rounded-md p-1 -ms-1 flex items-center gap-2"
        >
          <div class="size-8 rounded-lg bg-primary flex items-center justify-center text-white font-bold text-lg">
            {{ settingsStore.settings.store_name.charAt(0) || 'E' }}
          </div>
          <span class="font-bold text-lg text-neutral-900 dark:text-white">
            {{ settingsStore.settings.store_name }}
          </span>
        </NuxtLink>
      </template>

      <template #right>
        <UColorModeButton />

        <!-- Cart Button -->
        <CartSlideover>
          <UButton
            color="neutral"
            variant="ghost"
            icon="i-lucide-shopping-bag"
            class="relative min-h-10 px-2.5"
            aria-label="سبد خرید"
          >
            <UBadge
              v-if="cartStore.itemsCount > 0"
              color="primary"
              size="xs"
              class="absolute -top-1 -right-1 font-bold min-w-5 h-5 flex items-center justify-center rounded-full"
            >
              {{ toPersianDigits(cartStore.itemsCount) }}
            </UBadge>
          </UButton>
        </CartSlideover>

        <!-- User Authentication Button -->
        <template v-if="authStore.isAuthenticated">
          <UDropdownMenu
            :items="[
              [{
                label: authStore.user?.full_name || 'کاربر گرامی',
                icon: 'i-lucide-user',
                disabled: true
              }],
              [{
                label: 'خروج از حساب',
                icon: 'i-lucide-log-out',
                onSelect: () => authStore.logout()
              }]
            ]"
          >
            <UButton
              color="neutral"
              variant="subtle"
              icon="i-lucide-user"
              class="min-h-10 px-3"
            >
              {{ authStore.user?.full_name || 'حساب کاربری' }}
            </UButton>
          </UDropdownMenu>
        </template>
        <template v-else>
          <UButton
            color="primary"
            variant="solid"
            icon="i-lucide-log-in"
            class="min-h-10 px-4 font-medium"
            @click="authStore.openAuthModal"
          >
            ورود / ثبت‌نام
          </UButton>
        </template>
      </template>
    </UHeader>

    <UMain>
      <NuxtPage />
    </UMain>

    <USeparator />

    <UFooter>
      <template #left>
        <p class="text-sm text-neutral-500">
          تمامی حقوق مادی و معنوی این سایت متعلق به فروشگاه اینترنتی ایزیشاپ می‌باشد • © {{ new Date().getFullYear() }}
        </p>
      </template>
    </UFooter>

    <!-- Global Auth Modal -->
    <AuthModal />
  </UApp>
</template>
