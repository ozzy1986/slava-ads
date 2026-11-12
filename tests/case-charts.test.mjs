import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import test from "node:test";

const read = (id) => readFile(new URL(`../dist/cases/${id}/index.html`, import.meta.url), "utf8");
const text = (m) => m.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
const figure = (html) =>
  html.match(/<figure\b[^>]*\bclass="[^"]*\bcase-chart\b[^"]*"[^>]*>[\s\S]*?<\/figure>/)?.[0];

// Every expected number is sourced from content.ts (metric/secondary/resultNote)
// and caseDetails.ts (result). The test pins them so rendering cannot alter facts.
const DELTA = {
  morskaya: {
    caption: "Динамика: одинаковые периоды 2023 и 2024",
    bars: [
      ["Показы", "+25%"],
      ["Клики", "+19%"],
      ["Стоимость клика", "−9%"],
      ["Отказной трафик", "−5%"],
      ["Глубина просмотра", "+59%"],
      ["Стоимость конверсии", "−16%"],
      ["Конверсии", "+29%"],
    ],
  },
  sarmat: {
    caption: "Динамика при росте расходов на 10 000 ₽",
    bars: [
      ["Показы", "+148%"],
      ["Клики", "+63%"],
      ["Конверсии", "+121%"],
      ["Стоимость конверсии", "−28%"],
    ],
  },
};

const COMPARE = {
  proxamam: { caption: "Конверсии за сопоставимые периоды", series: [["193", "286"]] },
  kzmdgrp: { caption: "Заявки в неделю и цена лида", series: [["5", "20"], ["$23", "$5,59"]] },
};

const RATE = {
  tmm: { caption: "Доля оформленных заявок", display: "70%", label: "оформляется" },
  hhn: { caption: "Доля оформленных заявок", display: "70%", label: "оформляется" },
};

function assertOneChart(html, id, kind) {
  const kinds = html.match(/data-chart-kind="/g) || [];
  assert.equal(kinds.length, 1, `${id}: exactly one chart on the page`);
  assert.ok(html.includes(`data-chart-kind="${kind}"`), `${id}: chart kind is ${kind}`);
  const fig = figure(html);
  assert.ok(fig, `${id}: a <figure class="case-chart"> is rendered`);
  assert.ok(fig.includes('role="img"'), `${id}: graphic exposes role="img"`);
  assert.match(fig, /aria-label="[^"]+"/, `${id}: graphic has a non-empty aria-label`);
  return fig;
}

for (const [id, spec] of Object.entries(DELTA)) {
  test(`delta chart ${id} shows the sourced period-over-period deltas`, async () => {
    const html = await read(id);
    const fig = assertOneChart(html, id, "delta");
    const body = text(fig);
    assert.ok(body.includes(spec.caption), `${id}: caption present`);
    for (const [label, display] of spec.bars) {
      assert.ok(body.includes(label), `${id}: metric label "${label}"`);
      assert.ok(body.includes(display), `${id}: sourced value "${display}"`);
    }
    const bars = fig.match(/data-bar="/g) || [];
    assert.equal(bars.length, spec.bars.length, `${id}: exactly ${spec.bars.length} bars, no invented series`);
  });
}

for (const [id, spec] of Object.entries(COMPARE)) {
  test(`compare chart ${id} shows the sourced before/after values`, async () => {
    const html = await read(id);
    const fig = assertOneChart(html, id, "compare");
    const body = text(fig);
    assert.ok(body.includes(spec.caption), `${id}: caption present`);
    for (const [before, after] of spec.series) {
      assert.ok(body.includes(before), `${id}: before value "${before}"`);
      assert.ok(body.includes(after), `${id}: after value "${after}"`);
    }
    const series = fig.match(/data-series="/g) || [];
    assert.equal(series.length, spec.series.length, `${id}: exactly ${spec.series.length} compared series`);
  });
}

for (const [id, spec] of Object.entries(RATE)) {
  test(`rate chart ${id} shows the sourced fulfilment share`, async () => {
    const html = await read(id);
    const fig = assertOneChart(html, id, "rate");
    const body = text(fig);
    assert.ok(body.includes(spec.caption), `${id}: caption present`);
    assert.ok(body.includes(spec.display), `${id}: rate "${spec.display}"`);
    assert.ok(body.includes(spec.label), `${id}: rate label "${spec.label}"`);
    assert.ok(fig.includes("<svg"), `${id}: donut is drawn as inline SVG`);
  });
}

test("no case chart invents a time series or a second chart", async () => {
  for (const id of [...Object.keys(DELTA), ...Object.keys(COMPARE), ...Object.keys(RATE)]) {
    const html = await read(id);
    const fig = figure(html);
    assert.ok(fig, `${id}: chart exists`);
    // A delta chart is only allowed where the source gives period deltas.
    const isDelta = Object.keys(DELTA).includes(id);
    assert.equal(fig.includes('data-chart-kind="delta"'), isDelta, `${id}: delta only where sourced`);
    // No monthly/weekly axis labels that would imply an invented time series.
    assert.doesNotMatch(text(fig), /\b(янв|фев|мар|апр|май|июн|июл|авг|сен|окт|ноя|дек)\b/i, `${id}: no fabricated month axis`);
  }
});