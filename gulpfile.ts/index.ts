import { config as dotenv } from "dotenv";
dotenv({
  path: process.env.NODE_ENV !== "production" ? ".env.dev" : undefined,
});

import { task, watch, series, parallel } from "gulp";
import { server } from "gulp-connect-php";
import browserSync from "browser-sync";

import { pruneJS, transpileTS } from "./javascript";
import { pruneCSS, transpileSCSS } from "./css";
import { addRessources, pruneRes } from "./res";

const prune = parallel(pruneJS, pruneCSS, pruneRes);

function reload() {
  return new Promise<void>((res, rej) => {
    try {
      browserSync.reload();
      res();
    } catch (error) {
      rej(error);
    }
  });
}

task(
  "build",
  series(prune, parallel(transpileTS, transpileSCSS, addRessources))
);

function watchDev() {
  watch("src/**/*.ts", series(pruneJS, transpileTS, reload));
  watch("src/**/*.scss", series(pruneCSS, transpileSCSS, reload));
  watch("res/**/*", series(pruneRes, addRessources, reload));
  watch("php/**/*", reload);
}

task("watch", watchDev);

task("serve", function () {
  server(
    {
      port: process.env.HTTP_PORT ?? 80,
      router: "public/index.php",
    },
    function () {
      browserSync({
        proxy: `127.0.0.1:${process.env.HTTP_PORT ?? 80}`,
      });
    }
  );
  watchDev();
});

task("docker", function () {
  browserSync({
    proxy: `127.0.0.1:${process.env.HTTP_PORT ?? 8080}`,
  });
  watchDev();
});
