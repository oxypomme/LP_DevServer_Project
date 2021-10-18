import WindowEnv from "../windowEnv";
import { IPing, IWSPacket, IWSPayload, WSPacketTypes } from "./types";

function send(type: WSPacketTypes, data: IWSPayload): void {
  const event: IWSPacket = {
    type,
    jwt: localStorage.getItem("authToken") ?? "",
    payload: data,
  };
  console.log("[DEBUG] [WS] Outgoing :", event);
  ws?.send(JSON.stringify(event));
}

function ping() {
  send(WSPacketTypes.PING, {
    startTime: new Date().getTime(),
  } as IPing);
}

let ws: WebSocket | null = null;
try {
  ws = new WebSocket(
    WindowEnv.PHP_MODE === "production"
      ? `ws://${location.host}/ws`
      : `ws://${location.hostname}:8090`
  );
} catch (error) {
  console.error(error);
}

if (ws) {
  let pingTimer: NodeJS.Timer | null;

  ws.onopen = (ev) => {
    ping();
    // pingTimer = setInterval(ping, 5000);
  };

  ws.onclose = (ev) => {
    if (pingTimer) {
      clearInterval(pingTimer);
      pingTimer = null;
    }
  };

  ws.onerror = (ev) => {
    console.error(ev);
  };

  ws.onmessage = (ev) => {
    const event = JSON.parse(ev.data) as IWSPacket;
    console.log("[DEBUG] [WS] Incoming :", event);

    switch (event.type) {
      case WSPacketTypes.ERROR:
        console.error(event.payload);
        break;

      case WSPacketTypes.PING:
        console.log(
          `Connected via WS with ${
            new Date().getTime() - (event.payload as IPing).startTime
          }ms`
        );
        break;

      default:
        console.log(event);
        break;
    }
  };
}
