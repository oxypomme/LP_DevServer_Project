import { dest } from "gulp";
import { pipeline } from "readable-stream";
import sourcemaps from "gulp-sourcemaps";
import tsify from "tsify";
import { sync } from "glob";
import uglify from "gulp-uglify";
import browserify from "browserify";
import source from "vinyl-source-stream";
import buffer from "vinyl-buffer";

export function transpileTS() {
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
