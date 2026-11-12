/**
 * Global site configuration.
 * Single source of truth for domain, brand and lead-routing meta.
 */
export const site = {
  domain: "slava-ads.ru",
  url: "https://slava-ads.ru",
  name: "Острополец — интернет-маркетолог",
  brand: "Вячеслав Острополец",
  role: "Интернет-маркетолог",
  defaultTitle: "Контекстная реклама: сайт, заявки и аналитика",
  defaultDescription:
    "Яндекс Директ и Google Ads, посадочные страницы и аналитика в одном проекте. Разбор задачи, запуск и оптимизация по стоимости и качеству заявок.",
  locale: "ru_RU",
  lang: "ru",
  experienceYears: 8,
  /** Endpoint that receives lead submissions (PHP on the same host). */
  leadEndpoint: "/api/lead.php",
  ogImage: "/og/cover.jpg",
  contacts: {
    phone: "+7 988 995 27-33",
    phoneHref: "tel:+79889952733",
    email: "v.ostropolets@gmail.com",
    telegram: "https://t.me/slavaads",
    whatsapp: "https://wa.me/79889952733",
  },
  resume: "/resume.pdf",
} as const;

export type Site = typeof site;
