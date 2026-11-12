import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import test from "node:test";

const read = (p) => readFile(new URL("../dist/" + p, import.meta.url), "utf8");
const text = (m) => m.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
const leadButtons = (html) =>
  [...html.matchAll(/<button\b[^>]*\bdata-lead="([^"]+)"[^>]*>([\s\S]*?)<\/button>/g)].map((m) => ({
    source: m[1],
    label: text(m[2]),
  }));

const UNIFIED = "Получить разбор проекта";
const CTA_SUBTITLE =
  "Расскажите, что продаёте, в каком регионе работаете и что уже пробовали. Начнём с разбора текущей ситуации и возможных следующих шагов.";

const pages = {
  home: await read("index.html"),
  catalog: await read("cases/index.html"),
  kontekst: await read("kontekstnaya-reklama/index.html"),
  target: await read("targetirovannaya-reklama/index.html"),
  sites: await read("sozdanie-saytov/index.html"),
  design: await read("dizayn/index.html"),
  tmm: await read("cases/tmm/index.html"),
  hhn: await read("cases/hhn/index.html"),
  blog: await read("blog/osnovy-internet-marketinga/index.html"),
};

test("every lead-open button uses the single canonical label", () => {
  for (const [name, html] of Object.entries(pages)) {
    for (const b of leadButtons(html)) {
      assert.equal(b.label, UNIFIED, `${name}: button source=${b.source} must read "${UNIFIED}"`);
    }
  }
});

test("lead sources stay distinct per placement", () => {
  const homeSources = leadButtons(pages.home).map((b) => b.source);
  for (const s of ["header", "mobile-menu", "footer", "sticky-mobile", "hero-primary", "faq", "about", "cta-section"]) {
    assert.ok(homeSources.includes(s), `home must keep source ${s}`);
  }
  assert.equal(new Set(homeSources).size, homeSources.length, "home sources must be unique");
  assert.ok(leadButtons(pages.kontekst).some((b) => b.source === "service-kontekstnaya-reklama"));
  assert.ok(leadButtons(pages.kontekst).some((b) => b.source === "service-cta-kontekstnaya-reklama"));
  assert.ok(leadButtons(pages.tmm).some((b) => b.source === "case-tmm"));
  assert.ok(leadButtons(pages.catalog).some((b) => b.source === "cases-cta"));
  assert.ok(leadButtons(pages.blog).some((b) => b.source === "blog-cta"));
});

test("service pages carry no payment-model, payback or universal-conversion claims", () => {
  for (const name of ["kontekst", "target", "sites", "design"]) {
    const t = text(pages[name]);
    assert.doesNotMatch(t, /оплата за результат/i, `${name}: ambiguous payment model`);
    assert.doesNotMatch(t, /окупаем|окупается|окупится|окупил|окупают/i, `${name}: payback promise`);
    assert.doesNotMatch(t, /до 20%/, `${name}: universal conversion ceiling`);
    assert.doesNotMatch(t, /в пике/, `${name}: peak qualifier conflicts with the average`);
    assert.doesNotMatch(t, /нескольких дней/, `${name}: unconfirmed timing`);
  }
  for (const name of ["kontekst", "sites"]) {
    const t = text(pages[name]);
    assert.ok(t.includes("около 20%"), `${name}: personal-best conversion kept`);
    assert.ok(t.includes("мой лучший результат по конверсии"), `${name}: personal-best framing`);
  }
});

test("peak/average/up-to qualifiers agree between cards and case narratives", () => {
  const tmm = text(pages.tmm);
  assert.ok(tmm.includes("В среднем 200"), "tmm narrative keeps the average");
  assert.doesNotMatch(tmm, /до 200/, "tmm must not switch to an up-to ceiling");
  assert.match(pages.tmm, /name="description" content="[^"]*в среднем 200/, "tmm meta description uses the average");
  const hhn = text(pages.hhn);
  assert.ok(hhn.includes("в среднем 50"), "hhn narrative keeps the average");
  assert.doesNotMatch(hhn, /стабильные 50/, "hhn must not claim a stable constant");
  const k = text(pages.kontekst);
  assert.ok(k.includes("заявок в неделю в среднем"), "service outcome uses the average");
  assert.ok(k.includes("средняя цена заявки в нише ремонта"), "service price outcome is an average");
});

test("the catalog plaque drops the unverified client count and universal ceiling", () => {
  const intro = pages.catalog.match(/<section\b[^>]*\bclass="[^"]*\bpt-10\b[^"]*"[^>]*>([\s\S]*?)<\/section>/)?.[1];
  assert.ok(intro, "catalog intro section present");
  const body = text(intro);
  assert.doesNotMatch(body, /компаний/, "unverified company count removed");
  assert.doesNotMatch(body, /до 20%/, "universal conversion ceiling removed");
  assert.doesNotMatch(body, /пять заказчиков|им нравятся результаты/, "subjective client-count prose removed");
  for (const fragment of ["проектов в портфолио", "6", "Россия, СНГ и США", "8 лет", "около 20%", "мой лучший результат по конверсии", "ограниченное число проектов"]) {
    assert.ok(body.includes(fragment), `plaque/intro must keep "${fragment}"`);
  }
});

test("the lead modal speaks the same language as the canonical CTA", () => {
  const title = pages.home.match(/<h2 id="lead-modal-title"[^>]*>([^<]*)<\/h2>/)?.[1];
  assert.equal(title, UNIFIED);
  const subtitle = pages.home.match(/<h2 id="lead-modal-title"[^>]*>[^<]*<\/h2>\s*<p[^>]*>([^<]*)<\/p>/)?.[1];
  assert.equal(subtitle, CTA_SUBTITLE);
  assert.ok(pages.home.includes("Отвечаю лично в рабочее время."), "modal success keeps a personal, untimed reply note");
  assert.doesNotMatch(pages.home, /пары часов/, "no unconfirmed reply-time promise");
});

test("the submit button restores its own label instead of a hardcoded one", async () => {
  const src = await readFile(new URL("../src/components/LeadModal.astro", import.meta.url), "utf8");
  assert.doesNotMatch(src, /textContent = "Отправить заявку"/, "finally must not hardcode the submit label");
  assert.match(src, /submitLabel|initialLabel/, "submit label is captured and restored");
});