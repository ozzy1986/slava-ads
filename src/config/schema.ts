import { site } from "./site";
import { specialist } from "@/data/content";

/**
 * Shared entity identifiers. The Person @id is anchored on the specialist's
 * original site so both web properties describe one and the same person rather
 * than declaring the sites themselves duplicates. The original stays the
 * person's canonical url; this site is linked as another presence via sameAs.
 */
export const personId = "https://slavaads.ru/#person";
export const organizationId = `${site.url}/#organization`;
export const websiteId = `${site.url}/#website`;

export const personRef = { "@id": personId };
export const organizationRef = { "@id": organizationId };

export const person = {
  "@type": "Person",
  "@id": personId,
  name: specialist.fullName,
  alternateName: ["Слава", specialist.shortName],
  jobTitle: specialist.role,
  description: `Перформанс-маркетолог с ${specialist.experienceYears}-летним опытом: контекстная реклама в Яндекс Директ и Google Ads, продающие сайты и лендинги. Лучший кейс — 200 заявок в неделю в среднем.`,
  url: "https://slavaads.ru",
  image: new URL(specialist.photo, site.url).href,
  sameAs: ["https://slavaads.ru", site.contacts.telegram, site.contacts.whatsapp],
  knowsAbout: [
    "Контекстная реклама",
    "Яндекс Директ",
    "Google Ads",
    "Перформанс-маркетинг",
    "Создание сайтов и лендингов",
    "Веб-аналитика",
    "Яндекс Метрика",
    "Google Analytics 4",
  ],
};

export const organization = {
  "@type": "ProfessionalService",
  "@id": organizationId,
  name: site.name,
  url: site.url,
  image: new URL(site.ogImage, site.url).href,
  description: site.defaultDescription,
  priceRange: "$$",
  areaServed: ["RU", "KZ", "US"],
  telephone: site.contacts.phoneHref.replace("tel:", ""),
  email: site.contacts.email,
  sameAs: [site.contacts.telegram, site.contacts.whatsapp],
  founder: personRef,
};

export const website = {
  "@type": "WebSite",
  "@id": websiteId,
  url: `${site.url}/`,
  name: site.name,
  inLanguage: site.lang,
  publisher: organizationRef,
};