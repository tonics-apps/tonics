import * as myModule from "./script-combined.js";

const tableOfContent = new myModule.TableOfContent('.entry-content');
tableOfContent.tocDepth(6).run();