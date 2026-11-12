// @ts-check
import { defineConfig } from "astro/config";
import sitemap from "@astrojs/sitemap";
import mdx from "@astrojs/mdx";
import tailwindcss from "@tailwindcss/vite";

// https://astro.build
export default defineConfig({
  site: "https://slava-ads.ru",
  output: "static",
  trailingSlash: "ignore",
  build: {
    inlineStylesheets: "auto",
    format: "directory",
  },
  integrations: [
    sitemap({
      filter: (page) => !page.includes("/admin") && !page.includes("/api"),
    }),
    mdx(),
  ],
  vite: {
    plugins: [tailwindcss()],
  },
});
