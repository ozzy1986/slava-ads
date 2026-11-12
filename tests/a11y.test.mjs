import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import test from "node:test";

const read = (p) => readFile(new URL("../dist/" + p, import.meta.url), "utf8");
const home = await read("index.html");

const openTag = (html, attr) => {
  const m = html.match(new RegExp("<[a-z]+\\b[^>]*\\b" + attr + "\\b[^>]*>", "i"));
  return m ? m[0] : "";
};

test("the mobile-menu toggle exposes its expanded state and controls", () => {
  const toggle = openTag(home, "data-menu-toggle");
  assert.ok(toggle, "menu toggle button must exist");
  assert.match(toggle, /aria-expanded="false"/, "toggle starts collapsed");
  assert.match(toggle, /aria-controls="mobile-menu"/, "toggle points at the menu it opens");
});

test("the mobile menu has the id referenced by the toggle", () => {
  const menu = openTag(home, "data-mobile-menu");
  assert.ok(menu, "mobile menu must exist");
  assert.match(menu, /id="mobile-menu"/, "menu id matches the toggle aria-controls");
});

test("the lead dialog carries correct modal semantics", () => {
  const modal = (home.match(/<div\b[^>]*id="lead-modal"[^>]*>/) || [""])[0];
  assert.ok(modal, "lead modal must exist");
  assert.match(modal, /role="dialog"/, "dialog role");
  assert.match(modal, /aria-modal="true"/, "modal flag");
  assert.match(modal, /aria-labelledby="lead-modal-title"/, "labelled by its title");
  assert.match(modal, /aria-hidden="true"/, "dialog starts hidden from assistive tech");
});