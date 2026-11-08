import { defineProject } from "@fabrik.computer/sdk";

/**
 * How fabrik works in this repository: the domos WordPress plugin (`domos-core`).
 * Every key is optional; a key left out is detected or defaulted. Run `fabrik config explain`
 * to see where each value comes from.
 *
 * The plugin has no site of its own, so there is no preview: load it into a WordPress install
 * to try it (see AGENTS.md). The checks are the ones CI runs (.github/workflows/tests.yml).
 */
export default defineProject({
  base: "main",
  setup: {
    steps: ["composer install --no-interaction --no-progress --prefer-dist", "bun install"],
  },
  checks: {
    test: "composer test",
    lint: "composer lint",
  },
  services: {
    // Rebuilds the plugin's JS and CSS on change.
    dev: {
      command: "bun run dev",
      autostart: false,
      description: "Watch and rebuild the plugin's assets",
    },
    build: {
      command: "bun run build",
      kind: "task",
      description: "Build the plugin's assets for production",
    },
  },
});
