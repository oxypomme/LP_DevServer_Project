const { watch, src, dest, parallel } = require("gulp");
const pipeline = require("readable-stream").pipeline;
const sourcemaps = require("gulp-sourcemaps");
const concat = require("gulp-concat");
const ts = require("gulp-typescript");
const uglify = require("gulp-uglify");
const sass = require("gulp-sass")(require("sass"));

const tsProject = ts.createProject("tsconfig.json");

function typescript() {
  return pipeline(
    tsProject.src(),
    sourcemaps.init(),
    tsProject(),
    concat("bundle.js"),
    uglify(),
    sourcemaps.write(".", { sourceRoot: "./", includeContent: false }),
    dest("dist/js/")
  );
}

function scss() {
  return pipeline(
    src("client/scss/**/*.scss"),
    sourcemaps.init(),
    sass({ outputStyle: "compressed" }).on("error", sass.logError),
    sourcemaps.write("."),
    dest("dist/css/")
  );
}

// TODO? : Resize images ?

exports.build = () => {
  parallel(typescript, scss);
};

exports.watch = () => {
  watch("client/ts/**/*.ts", typescript);
  watch("client/scss/**/*.scss", scss);
};
