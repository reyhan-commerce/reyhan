export default defineAppConfig({
  ui: {
    colors: {
      primary: 'emerald',
      neutral: 'zinc'
    }
  },

  reyhan: {
    brand: {
      name: 'فروشگاه ریحان',
      slogan: 'خرید آنلاین هوشمند، سریع و مطمئن ✨',
      logoUrl: '/icon.svg',
      faviconUrl: '/icon.svg'
    },
    header: {
      sticky: true,
      showSearch: true,
      announcementBar: {
        enabled: true,
        text: 'ارسال رایگان برای خریدهای اول ✨',
        link: '/faq'
      }
    },
    footer: {
      showNewsletter: true,
      copyright: 'تمامی حقوق محفوظ است.'
    }
  }
})
