import { block } from "@roots/wordpress-hmr";

block.load(
  () => require.context(`./blocks`, true, /index\.*$/),
  (context, load) => module.hot?.accept(context.id, load)
);
