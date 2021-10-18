import WindowEnv from "../windowEnv";
import { IPing, IWSPacket, IWSPayload } from "./types";

enum WSPacketTypes {
  ERROR = "error",
  PING = "ping",
  MESSAGE = "message",
  MESSAGE_EDITED = "message_edit",
  MESSAGE_DELETION = "message_deletion",
}

function send(type: WSPacketTypes, data: IWSPayload): void {
  const event: IWSPacket = {
    type,
    jwt: localStorage.getItem("authToken") ?? "",
    payload: data,
  };
  console.log("[DEBUG] [WS] Outgoing :", event);
  ws?.send(JSON.stringify(event));
}

export function ping(): void {
  send(WSPacketTypes.PING, {
    startTime: new Date().getTime(),
  } as IPing);
}

export function sendMessage(
  content: string,
  attachement: string,
  target?: number,
  group?: number
): void {
  send(WSPacketTypes.MESSAGE, {
    content,
    attachement,
    target,
    group,
  } as IMessageInput);
}

export function editMessage(
  id: number,
  content: string,
  attachement: string
): void {
  send(WSPacketTypes.MESSAGE_EDITED, {
    id,
    content,
    attachement,
  } as IMessageInput);
}

export function delMessage(id: number): void {
  send(WSPacketTypes.MESSAGE_DELETION, {
    id,
  } as IMessageInput);
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

      case WSPacketTypes.MESSAGE:
        // TODO
        break;

      case WSPacketTypes.MESSAGE_EDITED:
        // TODO
        break;

      case WSPacketTypes.MESSAGE_DELETION:
        // TODO
        break;

      default:
        console.log(event);
        break;
    }
  };
}
