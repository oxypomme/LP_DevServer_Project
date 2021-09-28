// const { watch, src, dest, parallel } = require("gulp");
// const pipeline = require("readable-stream").pipeline;
// const sourcemaps = require("gulp-sourcemaps");
// const concat = require("gulp-concat");
// const ts = require("gulp-typescript");
// const uglify = require("gulp-uglify");
// const sass = require("gulp-sass")(require("sass"));
import { watch as gwatch, src, dest, parallel } from "gulp";
const pipeline = require("readable-stream").pipeline;
import sourcemaps from "gulp-sourcemaps";
import concat from "gulp-concat";
import ts from "gulp-typescript";
import uglify from "gulp-uglify";
import { exclude } from "gulp-ignore";
const sass = require("gulp-sass")(require("sass"));

const tsProject = ts.createProject("tsconfig.json");

function typescriptClient() {
  return pipeline(
    tsProject.src(),
    sourcemaps.init(),
    exclude("gulpfile.ts"),
    tsProject(),
    concat("bundle.js"),
    uglify(),
    sourcemaps.write(".", { sourceRoot: "./", includeContent: false }),
    dest("dist/js/")
  );
}

function typescriptServer() {
  //TODO
  return;
}

function scss() {
  return pipeline(
    src("src/scss/**/*.scss"),
    sourcemaps.init(),
    sass({ outputStyle: "compressed" }).on("error", sass.logError),
    sourcemaps.write("."),
    dest("dist/css/")
  );
}

// TODO? : Resize images ?

const buildClient = parallel(typescriptClient, scss);
buildClient.displayName = "build:client";

const buildServer = parallel(typescriptServer);
buildServer.displayName = "build:server";

const build = parallel(buildClient, buildServer);

const watch = () => {
  gwatch("src/ts/**/*.ts", typescriptClient);
  gwatch("src/scss/**/*.scss", scss);
  //TODO: watch server
};

export { build, buildClient, buildServer, watch };
