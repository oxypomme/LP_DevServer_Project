import { task, watch, series, parallel } from "gulp";
import del from "del";

import { transpileTS } from "./javascript";
import { transpileSCSS } from "./css";

function pruneJS() {
  return del(["dist/js/*"]);
}
function pruneCSS() {
  return del(["dist/css/*"]);
}
const prune = parallel(pruneJS, pruneCSS);

// TODO? : Resize images ?

task("build", series(prune, parallel(transpileTS, transpileSCSS)));

task("watch", function () {
  watch("src/ts/**/*.ts", series(pruneJS, transpileTS));
  watch("src/scss/**/*.scss", series(pruneCSS, transpileSCSS));
});
