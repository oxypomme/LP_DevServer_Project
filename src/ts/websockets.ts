import WindowEnv from "./windowEnv";

const ws = new WebSocket(
  WindowEnv.PHP_MODE ? `ws://localhost:8090` : `ws://${location.hostname}/ws`
);
ws.onopen = (ev) => {
  ws.send("test");
  console.log(ws);
};
