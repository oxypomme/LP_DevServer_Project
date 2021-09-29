import { task, watch, series, parallel } from "gulp";
import del from "del";
import { server } from "gulp-connect-php";
import browserSync from "browser-sync";

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

task("serve", function () {
  server(
    {
      port: 8080,
      router: "public/index.php",
    },
    function () {
      browserSync({
        proxy: "127.0.0.1:8080",
      });
    }
  );

  watch("src/ts/**/*.ts", series(pruneJS, transpileTS));
  watch("src/scss/**/*.scss", series(pruneCSS, transpileSCSS));
  watch(["php/**/*", "src/**/*"]).on("change", function () {
    browserSync.reload();
  });
});
