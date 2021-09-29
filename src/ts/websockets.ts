import WindowEnv from "./windowEnv";

const ws = new WebSocket(
  WindowEnv.PHP_MODE === "production"
    ? `ws://${location.host}/ws`
    : `ws://${location.hostname}:8090`
);
ws.onopen = (ev) => {
  ws.send("test");
  console.log(ws);
};
