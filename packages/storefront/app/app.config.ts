export default defineAppConfig({
  ui: {
    colors: {
      primary: 'emerald',
      neutral: 'zinc'
    }
  },

  /**
   * Reyhan Commerce Storefront Customization Schema
   * End-users can safely configure these options to tailor their storefront
   * without ever touching core layout or component files.
   */
  reyhan: {
    brand: {
      name: 'فروشگاه ریحان',
      slogan: 'عطر و طراوت خرید هوشمند با ارسال سریع 🌿',
      logoUrl: '/icon.svg',
      faviconUrl: '/icon.svg'
    },
    header: {
      sticky: true,
      showSearch: true,
      announcementBar: {
        enabled: true,
        text: 'ارسال رایگان برای خریدهای بالای ۱ میلیون تومان ✨',
        link: '/faq'
      }
    },
    footer: {
      showNewsletter: true,
      copyright: 'تمامی حقوق برای فروشگاه ریحان محفوظ است.'
    },
    translations: {
      fa: {},
      en: {}
    }
  }
})
