import { __ } from "@wordpress/i18n";

import edit from "./edit";
import save from "./save";

export const name = `clover/howdy`

export const settings = {
  title: `Howdy`,
  edit,
  save,
  example: {
    attributes: {
      content: __("Howdy"),
    },
  },
};
