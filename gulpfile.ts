import { task, watch, src, dest, series, parallel } from "gulp";
const pipeline = require("readable-stream").pipeline;
import sourcemaps from "gulp-sourcemaps";
import uglify from "gulp-uglify";
import clean from "gulp-clean";
import browserify from "browserify";
import { sync } from "glob";
import source from "vinyl-source-stream";
import tsify from "tsify";
import buffer from "vinyl-buffer";

import gulpsass from "gulp-sass";
import sassCompiler from "sass";
const sass = gulpsass(sassCompiler);

function pruneJS() {
  return src(["tmp/js/**/*, dist/js/*"], { read: false }).pipe(clean());
}
function pruneCSS() {
  return src(["dist/css/**/*"], { read: false }).pipe(clean());
}
const prune = parallel(pruneJS, pruneCSS);

function transpileTS() {
  const files = sync("src/ts/**/*.ts");
  return pipeline(
    browserify({
      basedir: ".",
      debug: true,
      entries: files,
      cache: {},
      packageCache: {},
    })
      .plugin(tsify)
      .bundle(),
    source("bundle.js"),
    buffer(),
    sourcemaps.init({ loadMaps: true }),
    uglify(),
    sourcemaps.write("."),
    dest("dist/js")
  );
}
function transpileSCSS() {
  return pipeline(
    src("src/scss/**/*.scss"),
    sourcemaps.init(),
    sass({ outputStyle: "compressed" }).on("error", sass.logError),
    sourcemaps.write("."),
    dest("dist/css/")
  );
}

// TODO? : Resize images ?

task("build", series(prune, parallel(transpileTS, transpileSCSS)));

task("watch", function () {
  watch("src/ts/**/*.ts", series(pruneJS, transpileTS));
  watch("src/scss/**/*.scss", series(pruneCSS, transpileSCSS));
});
