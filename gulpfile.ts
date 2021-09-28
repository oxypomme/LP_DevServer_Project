import { task, watch, src, dest, series, parallel } from "gulp";
const pipeline = require("readable-stream").pipeline;
import sourcemaps from "gulp-sourcemaps";
import clean from "gulp-clean";

import tsify from "tsify";
import { sync } from "glob";
import uglify from "gulp-uglify";
import browserify from "browserify";
import source from "vinyl-source-stream";
import buffer from "vinyl-buffer";

import gulpsass from "gulp-sass";
import sassCompiler from "sass";
const sass = gulpsass(sassCompiler);
import postcss from "gulp-postcss";
import autoprefixer from "autoprefixer";
import cssnano from "cssnano";

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
    sourcemaps.write(".", { includeContent: false }),
    dest("dist/js")
  );
}

function transpileSCSS() {
  return pipeline(
    src("src/scss/**/*.scss"),
    sourcemaps.init(),
    sass().on("error", sass.logError),
    postcss([autoprefixer, cssnano]),
    sourcemaps.write(".", { includeContent: false }),
    dest("dist/css/")
  );
}

// TODO? : Resize images ?

task("build", series(prune, parallel(transpileTS, transpileSCSS)));

task("watch", function () {
  watch("src/ts/**/*.ts", series(pruneJS, transpileTS));
  watch("src/scss/**/*.scss", series(pruneCSS, transpileSCSS));
});
