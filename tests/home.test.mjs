import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import test from "node:test";

const html = await readFile(new URL("../dist/index.html", import.meta.url), "utf8");
const hero = html.match(/<section\b[^>]*\bid="top"[^>]*>([\s\S]*?)<\/section>/)?.[1];
assert.ok(hero, "The built homepage must contain the hero section");

const text = (markup) => markup.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();

function attribute(tag, name) {
  return tag.match(new RegExp(`\\b${name}="([^"]*)"`))?.[1];
}

const evidence = html.match(/<section\b[^>]*\bid="evidence"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const audience = html.match(/<section\b[^>]*\bid="audience"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const servicesSection = html.match(/<section\b[^>]*\bid="services"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const workflowSection = html.match(/<section\b[^>]*\bid="workflow"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const casesSection = html.match(/<section\b[^>]*\bid="cases"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const economicsSection = html.match(/<section\b[^>]*\bid="economics"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const reviewsSection = html.match(/<section\b[^>]*\bid="reviews"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const qualificationSection = html.match(/<section\b[^>]*\bid="qualification"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const aboutSection = html.match(/<section\b[^>]*\bid="about"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const faqSection = html.match(/<section\b[^>]*\bid="faq"[^>]*>([\s\S]*?)<\/section>/)?.[1];
const ctaSection = html.match(/<section\b[^>]*\bid="cta"[^>]*>([\s\S]*?)<\/section>/)?.[1];

test("the homepage has one commercial H1", () => {
  const headings = [...html.matchAll(/<h1\b[^>]*>([\s\S]*?)<\/h1>/g)];
  assert.equal(headings.length, 1);
  assert.equal(text(headings[0][1]), "Заявки из контекстной рекламы под ключ");
});

test("the hero explains the advertising, landing page and measurement offer", () => {
  assert.ok(text(hero).includes(
    "Запускаю и оптимизирую Яндекс Директ и Google Ads, дорабатываю посадочные страницы и настраиваю учёт обращений. Решения по рекламе принимаем с учётом стоимости и качества заявок.",
  ));
});

test("the primary CTA preserves the existing lead source", () => {
  const button = hero.match(/<button\b[^>]*\bdata-lead="hero-primary"[^>]*>[\s\S]*?<\/button>/)?.[0];
  assert.ok(button);
  assert.equal(text(button), "Получить разбор проекта");
  assert.equal([...hero.matchAll(/\bdata-lead="/g)].length, 1);
});

test("the results CTA links to the homepage cases", () => {
  const link = hero.match(/<a\b[^>]*\bhref="#cases"[^>]*>[\s\S]*?<\/a>/)?.[0];
  assert.ok(link);
  assert.equal(text(link), "Смотреть результаты");
  const target = html.match(/<section\b[^>]*\bid="cases"[^>]*>/)?.[0];
  assert.ok(target);
  assert.match(attribute(target, "class") ?? "", /\bscroll-mt-28\b/, "The fixed header must not cover the anchor target");
});

test("the hero retains geography without unsupported numeric promises", () => {
  assert.ok(text(hero).includes("Россия, СНГ и США"));
  assert.doesNotMatch(text(hero), /50\s?000|20\s?%|200|8 лет|несколько недель|лет опыта/);
  assert.doesNotMatch(hero, /class="[^"]*\bstats\b/);
});

test("the portrait keeps the same identity and explicit image dimensions", () => {
  const image = hero.match(/<img\b[^>]*>/)?.[0];
  assert.ok(image);
  assert.equal(attribute(image, "src"), "/images/portrait.png");
  assert.equal(attribute(image, "alt"), "Вячеслав Острополец");
  assert.equal(attribute(image, "width"), "680");
  assert.equal(attribute(image, "height"), "680");
  assert.equal(attribute(image, "loading"), "eager");
  assert.equal(attribute(image, "fetchpriority"), "high");
});

test("existing sections follow the commercial order without a standalone quiz", () => {
  const main = html.match(/<main\b[^>]*>([\s\S]*?)<\/main>/)?.[1];
  assert.ok(main);
  const ids = [...main.matchAll(/<section\b[^>]*\bid="([^"]+)"/g)].map((match) => match[1]);
  assert.deepEqual(ids, [
    "top", "evidence", "audience", "services", "workflow", "cases", "economics", "reviews",
    "qualification", "about", "faq", "cta",
  ]);
  const allIds = [...html.matchAll(/\bid="([^"]+)"/g)].map((match) => match[1]);
  assert.equal(new Set(allIds).size, allIds.length, "Element IDs must be unique");
});

test("the evidence section showcases three verified project results", () => {
  assert.ok(evidence, "The homepage must contain the evidence section");
  const heading = evidence.match(/<h2\b[^>]*>([\s\S]*?)<\/h2>/)?.[1];
  assert.ok(heading);
  assert.equal(text(heading), "Результаты в разных нишах");

  const cards = [...evidence.matchAll(/<article\b[^>]*>[\s\S]*?<\/article>/g)].map((m) => m[0]);
  assert.equal(cards.length, 3, "Exactly three project cards");

  const expected = [
    { id: "tmm", title: "TV Mount Master", niche: "Установка ТВ на стену", platform: "Google Ads", geo: "США, Калифорния", value: "200", price: "$19" },
    { id: "hhn", title: "Handy Help Now", niche: "Услуги ремонта по дому", platform: "Google Ads", geo: "США, Калифорния", value: "50", price: "$18" },
    { id: "sarmat", title: "Сармат", niche: "Речные прогулки по Дону на катерах", platform: "Яндекс Директ", geo: "Ростов-на-Дону", value: "20", price: "407 ₽" },
  ];
  expected.forEach((e, i) => {
    const card = text(cards[i]);
    for (const fragment of [e.title, e.niche, e.platform, e.geo, e.value, "заявок в неделю", e.price, "цена заявки"]) {
      assert.ok(card.includes(fragment), `Card ${e.id} must show "${fragment}"`);
    }
    assert.ok(cards[i].includes(`href="/cases/${e.id}"`), `Card ${e.id} must link to its case`);
  });
});

test("the evidence section carries the project-results disclaimer", () => {
  assert.ok(evidence);
  assert.ok(text(evidence).includes(
    "Результаты отдельных проектов. Стоимость и количество обращений зависят от ниши, региона, предложения и периода работы.",
  ));
});

test("the evidence section links to the full case catalog as a ghost pill", () => {
  assert.ok(evidence);
  const links = [...evidence.matchAll(/<a\b[^>]*\bhref="\/cases"[^>]*>[\s\S]*?<\/a>/g)].map((m) => m[0]);
  assert.equal(links.length, 1, "Exactly one catalog link in the evidence header");
  assert.equal(text(links[0]), "Все кейсы");
  const cls = attribute(links[0], "class") ?? "";
  assert.match(cls, /\bbtn\b/, "Reuses the pill button, consistent with the Cases block");
  assert.match(cls, /\bbtn-ghost\b/);
});

test("the audience section helps visitors recognise their situation", () => {
  assert.ok(audience, "The homepage must contain the audience section");
  const heading = audience.match(/<h2\b[^>]*>([\s\S]*?)<\/h2>/)?.[1];
  assert.ok(heading);
  assert.equal(text(heading), "Когда стоит объединить рекламу, сайт и аналитику");

  const cards = [...audience.matchAll(/<article\b[^>]*>[\s\S]*?<\/article>/g)].map((m) => text(m[0]));
  assert.equal(cards.length, 4, "Exactly four situations");
  for (const fragment of [
    "системного привлечения пока нет",
    "какие заявки действительно полезны",
    "плохо переводит его в обращения",
    "сохраняя приемлемую стоимость привлечения",
  ]) {
    assert.ok(cards.some((c) => c.includes(fragment)), `A situation must mention "${fragment}"`);
  }

  assert.ok(text(audience).includes(
    "Для работы нужны готовое предложение, возможность обрабатывать обращения и обратная связь по качеству заявок.",
  ));
});

test("the services block presents the four directions as one solution in order", () => {
  assert.ok(servicesSection, "The homepage must contain the services section");
  const target = html.match(/<section\b[^>]*\bid="services"[^>]*>/)?.[0];
  assert.match(attribute(target, "class") ?? "", /\bscroll-mt-28\b/, "The fixed header must not cover the services anchor");
  const cards = [...servicesSection.matchAll(/<article\b[^>]*>[\s\S]*?<\/article>/g)].map((m) => m[0]);
  assert.equal(cards.length, 4);
  const slugs = cards.map((c) => c.match(/href="\/([a-z-]+)"/)?.[1]);
  assert.deepEqual(slugs, ["kontekstnaya-reklama", "sozdanie-saytov", "targetirovannaya-reklama", "dizayn"]);
});

test("the services block drops unsupported conversion, payback and payment claims", () => {
  assert.ok(servicesSection);
  const body = text(servicesSection);
  assert.doesNotMatch(body, /Оплата за результат/i);
  assert.doesNotMatch(body, /конверси[яи] около 20|10[–-]20\s?%|до 20\s?%/i);
  assert.doesNotMatch(body, /окупаем|окупалась|рост вашей выручки/i);
});

test("each service direction keeps the plan framing", () => {
  assert.ok(servicesSection);
  const cards = [...servicesSection.matchAll(/<article\b[^>]*>[\s\S]*?<\/article>/g)].map((m) => text(m[0]));
  assert.ok(/спросом/.test(cards[0]) && /запросами/.test(cards[0]) && /объявлениями/.test(cards[0]) && /бюджетом/.test(cards[0]) && /качеств/.test(cards[0]));
  assert.ok(/рекламному предложению/.test(cards[1]) && /путь до обращения/.test(cards[1]) && /конверсии/.test(cards[1]));
  assert.ok(/дополнительный канал/i.test(cards[2]) && /оправдан/.test(cards[2]));
  assert.ok(/гипотезы/.test(cards[3]) && /CTR/.test(cards[3]) && /диагностическая/.test(cards[3]));
});

test("analytics is shown as a cross-cutting part of the work", () => {
  assert.ok(servicesSection);
  assert.ok(text(servicesSection).includes("Аналитика — сквозная часть работы, а не отдельная услуга"));
});

test("the four service destinations remain available", async () => {
  assert.ok(servicesSection);
  for (const slug of ["kontekstnaya-reklama", "sozdanie-saytov", "targetirovannaya-reklama", "dizayn"]) {
    assert.ok(servicesSection.includes(`href="/${slug}"`));
    const page = await readFile(new URL(`../dist/${slug}/index.html`, import.meta.url), "utf8");
    assert.ok(page.includes(`href="https://slava-ads.ru/${slug}"`));
  }
});

test("the workflow block presents four checkpoints in order", () => {
  assert.ok(workflowSection, "The homepage must contain the workflow section");
  const cards = [...workflowSection.matchAll(/<article\b[^>]*>[\s\S]*?<\/article>/g)].map((m) => m[0]);
  assert.equal(cards.length, 4);
  const titles = cards.map((c) => text(c.match(/<h3\b[^>]*>([\s\S]*?)<\/h3>/)?.[1] ?? ""));
  assert.deepEqual(titles, ["Разбираем задачу", "Согласуем измерение", "Готовим и запускаем", "Оптимизируем"]);
  const target = html.match(/<section\b[^>]*\bid="workflow"[^>]*>/)?.[0];
  assert.match(attribute(target, "class") ?? "", /\bscroll-mt-28\b/, "The fixed header must not cover the workflow anchor");
});

test("each checkpoint separates actions from the client outcome", () => {
  assert.ok(workflowSection);
  const cards = [...workflowSection.matchAll(/<article\b[^>]*>[\s\S]*?<\/article>/g)].map((m) => text(m[0]));
  for (const c of cards) {
    assert.ok(c.includes("Что делаем"), "Each checkpoint must label the actions");
    assert.ok(c.includes("Что получает заказчик"), "Each checkpoint must label the client outcome");
  }
  assert.ok(/Предложение, спрос, прошлые результаты, ограничения/.test(cards[0]) && /Приоритеты и план проверки гипотез/.test(cards[0]));
  assert.ok(/Что считаем обращением/.test(cards[1]) && /как различаем качество/.test(cards[1]) && /Понятные критерии оценки/.test(cards[1]));
  assert.ok(/Посадочная, объявления, кампании/.test(cards[2]) && /проверка передачи заявок/.test(cards[2]) && /Рабочую связку и первые данные/.test(cards[2]));
  assert.ok(/Сопоставляем затраты, обращения/.test(cards[3]) && /менять, продолжать или масштабировать/.test(cards[3]));
});

test("the workflow heading and forecast framing avoid guaranteed timelines", () => {
  assert.ok(workflowSection);
  const h2 = text(workflowSection.match(/<h2\b[^>]*>([\s\S]*?)<\/h2>/)?.[1] ?? "");
  assert.equal(h2, "Четыре контрольные точки");
  const body = text(workflowSection);
  assert.doesNotMatch(body, /Три шага|через несколько недель|гарантирую срок|за \d+ дней/i);
  assert.ok(/прогноз/i.test(body) && /предпосылками/.test(body) && /не гаранти/.test(body));
});

test("the cases section drops the repeated general statistics strip", () => {
  assert.ok(casesSection);
  const body = text(casesSection);
  assert.doesNotMatch(body, /компаний на долгом ведении/);
  assert.doesNotMatch(body, /2 страны/);
  assert.doesNotMatch(body, /до 20\s?%/);
  assert.doesNotMatch(body, /8 лет/);
  assert.doesNotMatch(casesSection, /class="[^"]*\bstats\b/);
});

test("all six case destinations remain available", async () => {
  for (const id of ["tmm", "hhn", "morskaya", "proxamam", "kzmdgrp", "sarmat"]) {
    assert.ok(html.includes(`href="/cases/${id}"`));
    const page = await readFile(new URL(`../dist/cases/${id}/index.html`, import.meta.url), "utf8");
    assert.ok(page.includes(`href="https://slava-ads.ru/cases/${id}"`));
  }
});

const caseArticles = [...(casesSection ?? "").matchAll(/<article\b[^>]*>[\s\S]*?<\/article>/g)].map((m) => m[0]);
const caseArticle = (id) => caseArticles.find((a) => a.includes(`href="/cases/${id}"`));
const stepLabels = (art) =>
  [...art.matchAll(/<p\b[^>]*\bclass="[^"]*\bcase-step-label\b[^"]*"[^>]*>([\s\S]*?)<\/p>/g)].map((m) => text(m[1]));

test("the cases section presents each project as task → solution → result → takeaway", () => {
  assert.ok(casesSection);
  assert.equal(caseArticles.length, 6, "All six projects stay on the homepage");
  for (const art of caseArticles) {
    assert.deepEqual(stepLabels(art), ["Задача", "Решение", "Результат", "Вывод"]);
  }
});

test("each case solution carries its differentiating accent", () => {
  const expected = [
    { id: "tmm", fragments: ["отдельный бренд", "услуг по дому"] },
    { id: "hhn", fragments: ["многостраничный", "под каждую услугу"] },
    { id: "morskaya", fragments: ["действующие кампании", "позиционирование", "семейный курорт"] },
    { id: "proxamam", fragments: ["без переделки сайта", "квиз"] },
    { id: "kzmdgrp", fragments: ["существующим сайтом", "цену обращения"] },
    { id: "sarmat", fragments: ["новый сайт", "локальной услуги"] },
  ];
  for (const e of expected) {
    const art = caseArticle(e.id);
    assert.ok(art, `Missing case article ${e.id}`);
    const t = text(art);
    for (const f of e.fragments) assert.ok(t.includes(f), `${e.id} solution must mention "${f}"`);
  }
});

test("each numeric result clarifies what was counted and over which period", () => {
  const expected = [
    { id: "tmm", fragments: ["В среднем", "цена заявки"] },
    { id: "hhn", fragments: ["В среднем", "цена заявки"] },
    { id: "morskaya", fragments: ["цена конверсии", "не заявки", "одинаковых периодов"] },
    { id: "proxamam", fragments: ["10.07.2023", "10.02.2025", "за период"] },
    { id: "kzmdgrp", fragments: ["полтора месяца", "76", "7,71"] },
    { id: "sarmat", fragments: ["цена заявки", "10 000"] },
  ];
  for (const e of expected) {
    const art = caseArticle(e.id);
    assert.ok(art, `Missing case article ${e.id}`);
    const t = text(art);
    for (const f of e.fragments) assert.ok(t.includes(f), `${e.id} result must clarify "${f}"`);
  }
});

test("the cases section frames results honestly without unconfirmed timelines", () => {
  assert.ok(casesSection);
  const body = text(casesSection);
  assert.doesNotMatch(body, /через несколько недель/i);
  assert.doesNotMatch(body, /гарантирую результат|гарантированн/i);
  assert.ok(body.includes("задача → решение → результат → вывод"));
  assert.ok(/не гарантируют таких же/.test(body));
});

test("the economics block states the central lead-economics distinction", () => {
  assert.ok(economicsSection, "The homepage must contain the economics section");
  const h2 = text(economicsSection.match(/<h2\b[^>]*>([\s\S]*?)<\/h2>/)?.[1] ?? "");
  assert.equal(h2, "Дешёвая заявка ещё не означает выгодного клиента");
  const target = html.match(/<section\b[^>]*\bid="economics"[^>]*>/)?.[0];
  assert.match(attribute(target, "class") ?? "", /\bscroll-mt-28\b/, "The fixed header must not cover the economics anchor");
});

test("the economics block explains conversion, CPL quality and scaling", () => {
  assert.ok(economicsSection);
  const cards = [...economicsSection.matchAll(/<article\b[^>]*>[\s\S]*?<\/article>/g)].map((m) => text(m[0]));
  assert.equal(cards.length, 3, "Exactly three explanation points");
  const body = cards.join(" ");
  assert.ok(/какое именно действие учитывается/.test(body), "conversion is not always a lead");
  assert.ok(/качество обращений/.test(body) && /переход в продажи/.test(body), "low CPL needs quality and sales");
  assert.ok(/Увеличение бюджета само по себе не делает рекламу выгоднее/.test(body), "scaling needs economics");
});

test("the economics block shows the allowable-CPL formula as a scheme", () => {
  assert.ok(economicsSection);
  const body = text(economicsSection);
  assert.ok(body.includes("Допустимый CPL"));
  assert.ok(/допустимая стоимость привлечения клиента/i.test(body));
  assert.ok(/конверсия заявки в продажу/i.test(body));
  assert.ok(body.includes("=") && body.includes("×"), "The scheme must show the operators");
});

test("the economics block qualifies payback honestly and avoids a calculator", () => {
  assert.ok(economicsSection);
  const body = text(economicsSection);
  assert.ok(/с учётом маржи/.test(body));
  assert.ok(/Без данных о продажах нельзя достоверно заявлять окупаемость/.test(body));
  assert.doesNotMatch(economicsSection, /<input\b|<form\b|<select\b|type="range"/i, "No interactive calculator");
  assert.doesNotMatch(body, /\d+\s?₽|\d+\s?%|калькулятор/i, "No invented financial indicators");
});

test("the reviews block keeps client words verbatim and drops the screenshot promise", () => {
  assert.ok(reviewsSection, "The homepage must contain the reviews section");
  const target = html.match(/<section\b[^>]*\bid="reviews"[^>]*>/)?.[0];
  assert.match(attribute(target, "class") ?? "", /\bscroll-mt-28\b/, "The fixed header must not cover the reviews anchor");
  const body = text(reviewsSection);
  assert.ok(body.includes("Слова из реальных переписок в WhatsApp и Telegram."), "Intro must not promise screenshots");
  assert.doesNotMatch(body, /скриншот/i, "Screenshots are not shown, so they must not be promised");
  const figures = [...reviewsSection.matchAll(/<figure\b[^>]*>[\s\S]*?<\/figure>/g)];
  assert.equal(figures.length, 5, "All five client reviews stay");
  for (const fragment of [
    "заявки идут каждый день",
    "буквально с первых дней",
    "+500% посещений при том же бюджете",
    "никакой головной боли с нашей стороны",
    "Цифры стабильно растут",
    "Роман", "Игорь", "Марина", "Наталья",
    "TV Mount Master", "ProXamam", "Сармат", "Станция Морская",
  ]) {
    assert.ok(body.includes(fragment), `Reviews must keep "${fragment}" verbatim`);
  }
});

test("the qualification block merges education and certificates with access to originals", () => {
  assert.ok(qualificationSection, "The homepage must contain the qualification section");
  const h2 = text(qualificationSection.match(/<h2\b[^>]*>([\s\S]*?)<\/h2>/)?.[1] ?? "");
  assert.equal(h2, "Подтверждённая экспертиза");
  const target = html.match(/<section\b[^>]*\bid="qualification"[^>]*>/)?.[0];
  assert.match(attribute(target, "class") ?? "", /\bscroll-mt-28\b/, "The fixed header must not cover the qualification anchor");

  const docs = [...qualificationSection.matchAll(/<article\b[^>]*>[\s\S]*?<\/article>/g)].map((m) => m[0]);
  assert.equal(docs.length, 11, "Seven education documents plus four certificates");
  const opens = [...qualificationSection.matchAll(/Открыть оригинал/g)].length;
  assert.equal(opens, 11, "Every document must link to its original");
  assert.ok(/открывайте оригиналы/i.test(text(qualificationSection)));

  const body = text(qualificationSection);
  for (const title of [
    "Интернет-маркетолог", "Яндекс Директ", "Google Ads", "Веб-аналитик",
    "Повышение конверсии сайта", "Разработка на WordPress", "SMM-менеджер",
    "Яндекс Директ. Продвинутый", "Яндекс Метрика", "Медийная реклама", "Яндекс Директ. Базовый",
  ]) {
    assert.ok(body.includes(title), `Qualification must list "${title}"`);
  }
  assert.ok(qualificationSection.includes('href="/images/edu/1-yandex.jpg"'), "Local diploma original must be reachable");
  assert.ok(qualificationSection.includes("302d9c36-a470-45c2-ac55-df06abccf14d.jpg"), "Remote certificate original must be reachable");
});

test("the standalone education and certificates sections are merged away", () => {
  assert.doesNotMatch(html, /<section\b[^>]*\bid="education"/, "Education is merged into qualification");
  assert.doesNotMatch(html, /<section\b[^>]*\bid="certificates"/, "Certificates are merged into qualification");
});

test("the about block is two short paragraphs on specialization and interaction", () => {
  assert.ok(aboutSection, "The homepage must contain the about section");
  const target = html.match(/<section\b[^>]*\bid="about"[^>]*>/)?.[0];
  assert.match(attribute(target, "class") ?? "", /\bscroll-mt-28\b/, "The fixed header must not cover the about anchor");
  const body = text(aboutSection);
  assert.ok(body.includes("Об авторе"), "Micro-label reframed to author");
  assert.ok(body.includes("Привет, я Вячеслав"));
  assert.ok(body.includes("Маркетолог, который живёт трафиком и цифрами."));

  const aboutBody = aboutSection.match(/<div\b[^>]*\bclass="[^"]*\babout-body\b[^"]*"[^>]*>([\s\S]*?)<\/div>/)?.[1];
  assert.ok(aboutBody, "About paragraphs must live in a dedicated body container");
  assert.equal([...aboutBody.matchAll(/<p\b/g)].length, 2, "Exactly two short paragraphs");
  assert.ok(/контекстной рекламой и сайтами/.test(text(aboutBody)));
  assert.ok(/ограниченное число проектов/.test(text(aboutBody)));
  assert.ok(/Telegram и WhatsApp/.test(text(aboutBody)));

  assert.doesNotMatch(body, /Библию Google Ads|Клиентов на всю жизнь|мемы|YouTube|Ивицким|Бошниковым|вебинарчики|сноуборд|скейт|парашют|After Effects|Premiere|Canva|3ds Max/i, "Long book/channel/hobby lists removed");
  assert.doesNotMatch(aboutSection, /Увлекаюсь дизайном|resume-divider/, "Design-skills and hobbies blocks removed");
  const cta = aboutSection.match(/<button\b[^>]*\bdata-lead="about"[^>]*>[\s\S]*?<\/button>/)?.[0];
  assert.ok(cta, "The about lead source is preserved");
  assert.ok(aboutSection.includes('src="/images/workspace.jpg"'), "Workspace photo stays");
});

const faqItems = [...(faqSection ?? "").matchAll(/<details\b[^>]*\bclass="[^"]*\bfaq-item\b[^"]*"[^>]*>[\s\S]*?<\/details>/g)].map((m) => m[0]);
const faqQuestion = (d) => text(d.match(/<summary[\s\S]*?<span\b[^>]*>([\s\S]*?)<\/span>/)?.[1] ?? "");
const faqAnswer = (d) => text(d.match(/<\/summary>\s*<div\b[^>]*>([\s\S]*?)<\/div>/)?.[1] ?? "");
const norm = (s) => s.replace(/\s+/g, " ").trim();

test("the FAQ asks six intent-focused questions instead of a service listing", () => {
  assert.ok(faqSection, "The homepage must contain the FAQ section");
  assert.equal(faqItems.length, 6, "Six questions per the plan");
  assert.deepEqual(faqItems.map(faqQuestion), [
    "Из чего складывается бюджет: реклама, работа специалиста, сайт и дополнительные инструменты?",
    "Можно ли начать с существующим сайтом?",
    "Что считается заявкой и как оценивается её качество?",
    "Какие данные нужны для оценки стоимости привлечения?",
    "Когда можно принимать решение об увеличении бюджета?",
    "Можно ли гарантировать количество заявок и окупаемость?",
  ]);
  const body = text(faqSection);
  for (const old of [
    "Какие услуги вы предоставляете?", "Сколько стоят ваши услуги?",
    "Как понять, что реклама работает?", "Можете ли вы гарантировать результат?",
    "Нужен ли мне маркетинг", "Какие инструменты вы используете?",
    "Будете ли вы следить за результатами?", "С кем вы уже работали?",
  ]) {
    assert.ok(!body.includes(old), `The old generic question must be gone: "${old}"`);
  }
});

test("the FAQ keeps the source-confirmed personal-best conversion, not a universal rate", () => {
  assert.ok(faqSection);
  const body = text(faqSection);
  assert.ok(/лучший результат по конверсии/.test(body), "The personal-best conversion statement is kept");
  assert.ok(/около 20%/.test(body), "The confirmed ~20% personal best stays");
  assert.ok(/личный рекорд конкретного проекта/.test(body) && /не универсальная норма/.test(body), "Framed as a personal best, not a universal rate");
  const answer = faqItems.map(faqAnswer).find((a) => /около 20%/.test(a));
  assert.ok(answer && /гарантировать нельзя/.test(answer), "The personal best lives in the guarantees answer, which denies universal promises");
});

test("the FAQ answers avoid unconfirmed prices, timelines, CRM and contract terms", () => {
  assert.ok(faqSection);
  const body = text(faqSection);
  assert.doesNotMatch(body, /50\s?000/, "No invented budget threshold");
  assert.doesNotMatch(body, /через несколько недель|за \d+ дней|гарантирую срок/i, "No timing promises");
  assert.doesNotMatch(body, /\bCRM\b|amoCRM|Bitrix|битрикс/i, "No unconfirmed CRM integrations");
  assert.doesNotMatch(body, /условия договора|по договору|предоплат|рассрочк/i, "No contract terms");
  assert.doesNotMatch(body, /\d+\s?(₽|руб|\$|€)/i, "No invented prices");
  assert.doesNotMatch(body, /окупается|гарантирую (количество|результат|окупаем)|гарантированн/i, "No universal payback or result guarantees");
  assert.doesNotMatch(body, /10[–-]20\s?%|до 20\s?%/i, "No universal conversion range");
});

test("the FAQ JSON-LD stays in sync with the six visible questions and answers", () => {
  const schemas = [...html.matchAll(/<script\b[^>]*\btype="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/g)].map((m) => JSON.parse(m[1]));
  const faqPage = schemas.find((s) => s["@type"] === "FAQPage");
  assert.ok(faqPage, "The homepage must emit FAQPage structured data");
  assert.equal(faqPage.mainEntity.length, 6, "Schema mirrors the six visible questions");
  faqPage.mainEntity.forEach((q, i) => {
    assert.equal(q["@type"], "Question");
    assert.equal(norm(q.name), norm(faqQuestion(faqItems[i])), `Schema question ${i} matches the visible one`);
    assert.equal(q.acceptedAnswer["@type"], "Answer");
    assert.equal(norm(q.acceptedAnswer.text), norm(faqAnswer(faqItems[i])), `Schema answer ${i} matches the visible one`);
  });
});

test("the FAQ anchor clears the fixed header for the footer link", () => {
  const target = html.match(/<section\b[^>]*\bid="faq"[^>]*>/)?.[0];
  assert.ok(target);
  assert.match(attribute(target, "class") ?? "", /\bscroll-mt-28\b/, "The footer links to /#faq, so the header must not cover it");
  assert.ok(html.includes('href="/#faq"'), "The footer link to the FAQ exists");
});

test("the final CTA invites a project review with the plan's heading and copy", () => {
  assert.ok(ctaSection, "The homepage must contain the final CTA section");
  const h2 = text(ctaSection.match(/<h2\b[^>]*>([\s\S]*?)<\/h2>/)?.[1] ?? "");
  assert.equal(h2, "Обсудим, как привлекать заявки в ваш проект");
  const body = text(ctaSection);
  assert.ok(body.includes(
    "Расскажите, что продаёте, в каком регионе работаете и что уже пробовали. Начнём с разбора текущей ситуации и возможных следующих шагов.",
  ), "The CTA copy asks for the offer, region and prior attempts");
  assert.ok(!body.includes("Готовы получать заявки?"), "The old heading is replaced");
  assert.ok(!body.includes("Оставьте контакты"), "The old subtitle is replaced");
});

test("the final CTA button reads 'Получить разбор проекта' and reuses the existing modal", () => {
  assert.ok(ctaSection);
  const button = ctaSection.match(/<button\b[^>]*\bdata-lead="cta-section"[^>]*>[\s\S]*?<\/button>/)?.[0];
  assert.ok(button, "The CTA button keeps its lead source and opens the existing modal");
  assert.equal(text(button), "Получить разбор проекта");
  assert.ok(!text(ctaSection).includes("Получить консультацию"), "The old CTA label is replaced");
  assert.equal([...ctaSection.matchAll(/\bdata-lead="/g)].length, 1, "Only the primary button triggers the modal");
  assert.doesNotMatch(ctaSection, /<form\b|data-lead-form|<input\b/i, "The CTA must not introduce a second form or handler");
  const forms = [...html.matchAll(/<form\b[^>]*\bdata-lead-form[^>]*>/g)];
  assert.equal(forms.length, 1, "Exactly one lead form on the page — the existing modal");
  const link = ctaSection.match(/<a\b[^>]*\bhref="\/cases"[^>]*>[\s\S]*?<\/a>/)?.[0];
  assert.ok(link && text(link) === "Изучить кейсы", "The secondary cases link stays");
});

test("the canonical and Open Graph URL identify this homepage", () => {
  const canonicals = [...html.matchAll(/<link\b[^>]*\brel="canonical"[^>]*>/g)];
  assert.equal(canonicals.length, 1);
  assert.equal(attribute(canonicals[0][0], "href"), "https://slava-ads.ru/");
  const ogUrl = html.match(/<meta\b[^>]*\bproperty="og:url"[^>]*>/)?.[0];
  assert.ok(ogUrl);
  assert.equal(attribute(ogUrl, "content"), "https://slava-ads.ru/");
  assert.doesNotMatch(html, /<meta\b[^>]*\bname="robots"[^>]*\bcontent="[^"]*noindex/);
});

test("structured data keeps one specialist rather than inventing a second person", () => {
  const schemas = [...html.matchAll(/<script\b[^>]*\btype="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/g)]
    .map((match) => JSON.parse(match[1]));
  const people = schemas.filter((schema) => schema["@type"] === "Person");
  assert.equal(people.length, 1);
  assert.equal(people[0]["@id"], "https://slavaads.ru/#person");
});

test("the existing lead form retains attribution, anti-spam and its endpoint", () => {
  const forms = [...html.matchAll(/<form\b[^>]*\bdata-lead-form[^>]*>([\s\S]*?)<\/form>/g)];
  assert.equal(forms.length, 1);
  for (const name of [
    "name", "phone", "message", "company_site", "source", "quiz", "page", "referrer",
    "utm_source", "utm_medium", "utm_campaign", "utm_content", "utm_term",
    "form_token", "captcha_token", "captcha_answer",
  ]) {
    assert.ok(forms[0][1].includes(`name="${name}"`), `Missing form field: ${name}`);
  }
  assert.ok(html.includes("/api/lead.php"));
  assert.match(html, /id="lead-modal"/);
});

// ---- §4 Metadata & technical signals ----
const metaContent = (key) => {
  const tag = html.match(new RegExp(`<meta\\b[^>]*\\b(?:name|property)="${key}"[^>]*>`))?.[0];
  return tag ? attribute(tag, "content") : undefined;
};
const ldNodes = [...html.matchAll(/<script\b[^>]*\btype="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/g)].map((m) => JSON.parse(m[1]));
const ldByType = (t) => ldNodes.filter((n) => n["@type"] === t);
const PLAN_TITLE = "Контекстная реклама: сайт, заявки и аналитика";
const PLAN_DESC = "Яндекс Директ и Google Ads, посадочные страницы и аналитика в одном проекте. Разбор задачи, запуск и оптимизация по стоимости и качеству заявок.";

test("the homepage title and description carry the approved lead-acquisition positioning", () => {
  assert.equal(html.match(/<title>([\s\S]*?)<\/title>/)?.[1], PLAN_TITLE);
  assert.equal(metaContent("description"), PLAN_DESC);
});

test("OG and Twitter metadata mirror the title, description and self canonical", () => {
  assert.equal(metaContent("og:title"), PLAN_TITLE);
  assert.equal(metaContent("og:description"), PLAN_DESC);
  assert.equal(metaContent("twitter:title"), PLAN_TITLE);
  assert.equal(metaContent("twitter:description"), PLAN_DESC);
  assert.equal(metaContent("twitter:card"), "summary_large_image");
  assert.equal(metaContent("og:type"), "website");
  assert.equal(metaContent("og:url"), "https://slava-ads.ru/");
  assert.equal(metaContent("og:image"), "https://slava-ads.ru/og/cover.jpg");
  assert.equal(metaContent("twitter:image"), "https://slava-ads.ru/og/cover.jpg");
});

test("the OG cover is a dedicated 1200x630 JPEG shipped with the site", async () => {
  const buf = await readFile(new URL("../dist/og/cover.jpg", import.meta.url));
  let dims = null;
  for (let i = 2; i < buf.length - 9; i++) {
    if (buf[i] !== 0xff) continue;
    const m = buf[i + 1];
    if (m >= 0xc0 && m <= 0xcf && m !== 0xc4 && m !== 0xc8 && m !== 0xcc) {
      dims = { h: buf.readUInt16BE(i + 5), w: buf.readUInt16BE(i + 7) };
      break;
    }
    i += 1 + buf.readUInt16BE(i + 2);
  }
  assert.ok(dims, "The OG cover must be a readable JPEG");
  assert.equal(dims.w, 1200, "OG cover width");
  assert.equal(dims.h, 630, "OG cover height");
});

test("structured data declares this domain's WebSite and WebPage identifiers", () => {
  const sites = ldByType("WebSite");
  assert.equal(sites.length, 1);
  assert.equal(sites[0]["@id"], "https://slava-ads.ru/#website");
  assert.equal(sites[0].url, "https://slava-ads.ru/");
  assert.equal(sites[0].inLanguage, "ru");
  const pages = ldByType("WebPage");
  assert.equal(pages.length, 1);
  assert.equal(pages[0]["@id"], "https://slava-ads.ru/#webpage");
  assert.equal(pages[0].url, "https://slava-ads.ru/");
  assert.equal(pages[0].isPartOf["@id"], "https://slava-ads.ru/#website");
  assert.equal(pages[0].about["@id"], "https://slavaads.ru/#person");
  assert.equal(pages[0].inLanguage, "ru");
});

test("unconfirmed star ratings are removed while the review texts stay", () => {
  const serialized = JSON.stringify(ldNodes);
  assert.ok(!serialized.includes("AggregateRating"), "No aggregate rating");
  assert.ok(!serialized.includes("reviewRating"), "No per-review rating");
  assert.ok(!serialized.includes("ratingValue"), "No invented rating values");
  assert.ok(!serialized.includes("bestRating"), "No best-rating scale");
  const allReviews = [];
  const walk = (o) => {
    if (!o || typeof o !== "object") return;
    if (o["@type"] === "Review") allReviews.push(o);
    for (const k of Object.keys(o)) walk(o[k]);
  };
  ldNodes.forEach(walk);
  assert.equal(allReviews.length, 5, "All five text reviews stay in the schema");
  for (const r of allReviews) {
    assert.ok(typeof r.reviewBody === "string" && r.reviewBody.length > 20, "Review keeps its text");
    assert.ok(r.author && r.author.name, "Review keeps its author");
    assert.equal(r.reviewRating, undefined, "Review carries no star rating");
  }
  assert.ok(serialized.includes("заявки идут каждый день") && serialized.includes("Цифры стабильно растут"), "Verbatim review text is preserved");
});

test("the page keeps a self canonical without hreflang or sitewide links to the second site", () => {
  const canonicals = [...html.matchAll(/<link\b[^>]*\brel="canonical"[^>]*>/g)];
  assert.equal(canonicals.length, 1);
  assert.equal(attribute(canonicals[0][0], "href"), "https://slava-ads.ru/");
  assert.doesNotMatch(html, /<link\b[^>]*\bhreflang=/i, "No hreflang alternates");
  const anchors = [...html.matchAll(/<a\b[^>]*\bhref="([^"]*)"[^>]*>/g)].map((m) => m[1]);
  assert.ok(!anchors.some((h) => /slavaads\.ru/i.test(h)), "No SEO anchor links to slavaads.ru");
});
