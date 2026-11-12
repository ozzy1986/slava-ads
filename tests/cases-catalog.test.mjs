import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import test from "node:test";

const html = await readFile(new URL("../dist/cases/index.html", import.meta.url), "utf8");
const text = (markup) => markup.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();

const intro = html.match(/<section\b[^>]*\bclass="[^"]*\bpt-10\b[^"]*"[^>]*>([\s\S]*?)<\/section>/)?.[1];
assert.ok(intro, "The cases catalog must contain the intro section with the highlights strip");

const cards = [...intro.matchAll(/<div\b[^>]*\bclass="[^"]*\bresume-skills-card\b[^"]*"[^>]*>[\s\S]*?<\/div>/g)].map((m) => m[0]);

test("the highlights strip keeps four cells", () => {
  assert.equal(cards.length, 4);
});

test("the geography cell states the regions without a country count", () => {
  const body = text(intro);
  assert.doesNotMatch(body, /\d+\s?стран/i, "The misleading numeric country count must be gone");
  assert.ok(body.includes("Россия, СНГ и США"));
  const geoCard = cards.find((c) => text(c).includes("Россия, СНГ и США"));
  assert.ok(geoCard, "A cell must show the geography");
  assert.doesNotMatch(geoCard, /stat-num/, "The geography cell must not render a big number");
});

test("the remaining highlights stay grounded and unchanged", () => {
  const body = text(intro);
  for (const fragment of ["проектов в портфолио", "8 лет", "опыта в трафике", "около 20%", "мой лучший результат по конверсии"]) {
    assert.ok(body.includes(fragment), `Highlight must keep "${fragment}"`);
  }
  const projectsCard = cards.find((c) => text(c).includes("проектов в портфолио"));
  assert.ok(projectsCard && text(projectsCard).includes("6"), "The projects cell keeps the verifiable case count");
  assert.doesNotMatch(body, /компаний/, "The unverified company count must be gone");
  assert.doesNotMatch(body, /до 20%/, "The universal conversion ceiling must be gone");
});