import { src, dest } from "gulp";
const { pipeline } = require("readable-stream");
import sourcemaps from "gulp-sourcemaps";

import gulpsass from "gulp-sass";
import sassCompiler from "sass";
const sass = gulpsass(sassCompiler);
import postcss from "gulp-postcss";
import autoprefixer from "autoprefixer";
import cssnano from "cssnano";

export function transpileSCSS() {
  return pipeline(
    src("src/scss/**/*.scss"),
    sourcemaps.init(),
    sass().on("error", sass.logError),
    postcss([autoprefixer, cssnano]),
    sourcemaps.write(".", { includeContent: false }),
    dest("dist/css/")
  );
}
