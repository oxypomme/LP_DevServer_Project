import { src, dest, parallel } from "gulp";
import ts from "gulp-typescript";
import uglify from "gulp-uglify";
const pipeline = require("readable-stream").pipeline;

function typescript() {
  return pipeline(src("src/ts/**/*.ts"), ts(), uglify(), dest("public/js/"));
}

exports.default = parallel(typescript);
