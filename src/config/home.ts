/**
 * Reorderable homepage layout.
 *
 * To change the order of semantic blocks on the homepage, simply reorder the
 * keys in `homeBlocks`. Each key maps to a block component in
 * `src/components/blocks/*` via the registry in `src/pages/index.astro`.
 *
 * To hide a block, remove (or comment out) its key.
 */
export type BlockKey =
  | "hero"
  | "evidence"
  | "audience"
  | "economics"
  | "services"
  | "cases"
  | "workflow"
  | "reviews"
  | "qualification"
  | "about"
  | "faq"
  | "cta";

export const homeBlocks: BlockKey[] = [
  "hero",
  "evidence",
  "audience",
  "services",
  "workflow",
  "cases",
  "economics",
  "reviews",
  "qualification",
  "about",
  "faq",
  "cta",
];
