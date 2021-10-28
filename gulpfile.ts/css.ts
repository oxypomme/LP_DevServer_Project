import { src, dest } from "gulp";
import { pipeline } from "readable-stream";
import sourcemaps from "gulp-sourcemaps";
import del from "del";
import gulpsass from "gulp-sass";
import sassCompiler from "sass";
const sass = gulpsass(sassCompiler);
import postcss from "gulp-postcss";
import autoprefixer from "autoprefixer";
import cssnano from "cssnano";

export function transpileSCSS() {
  return pipeline(
    src("src/scss/**/style.scss"),
    sourcemaps.init(),
    sass().on("error", sass.logError),
    postcss([autoprefixer, cssnano]),
    sourcemaps.write(".", { includeContent: false }),
    dest("dist/css/")
  );
}

export function pruneCSS() {
  return del(["dist/css/*"]);
}
