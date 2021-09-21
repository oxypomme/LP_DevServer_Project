const { watch, src, dest, parallel } = require("gulp");
const ts = require("gulp-typescript");
const uglify = require("gulp-uglify");
const sourcemaps = require("gulp-sourcemaps");
const pipeline = require("readable-stream").pipeline;
const sass = require("gulp-sass")(require("sass"));

const tsProject = ts.createProject("tsconfig.json");

function typescript() {
  // TODO : Pack in one file
  return pipeline(
    tsProject.src(),
    sourcemaps.init(),
    tsProject(),
    uglify(),
    sourcemaps.write(".", { sourceRoot: "./", includeContent: false }),
    dest("public/js/")
  );
}

function scss() {
  return pipeline(
    src("client/scss/**/*.scss"),
    sourcemaps.init(),
    sass({ outputStyle: "compressed" }).on("error", sass.logError),
    sourcemaps.write("."),
    dest("public/css/")
  );
}

exports.build = () => {
  parallel(typescript, scss);
};

exports.watch = () => {
  watch("client/ts/**/*.ts", typescript);
  watch("client/scss/**/*.scss", scss);
};
