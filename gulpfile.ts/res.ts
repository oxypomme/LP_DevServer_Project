import { src, dest } from "gulp";
import { pipeline } from "readable-stream";
import del from "del";

export function addRessources() {
  return pipeline(src("res/**/*"), dest("dist/res"));
}

export function pruneRes() {
  return del(["dist/res/*"]);
}
