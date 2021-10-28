import { IMessage, IRelationList } from "../types/responses";
import WindowEnv from "../windowEnv";
import {
  onFriendConnection,
  onFriendDisconnection,
  onFriendList,
  onMessageDeleted,
  onMessageEdited,
  onNewMessage,
} from "./chat";
import { IConnection, IPing, IWSPacket, IWSPayload } from "./types";

enum WSPacketTypes {
  ERROR = "error",
  PING = "ping",
  MESSAGE = "message",
  MESSAGE_EDITED = "message_edit",
  MESSAGE_DELETION = "message_deletion",
  FRIEND_LIST = "friends",
  FRIEND_CONNECTION = "connection_in",
  FRIEND_DISCONNECTION = "connection_out",
}

let isJWTSent = false;

function send(type: WSPacketTypes, data: IWSPayload, forceAuth = false): void {
  const event: IWSPacket = {
    type,
    payload: data,
  };
  // Send auth if first time or forced
  if (!isJWTSent || forceAuth) {
    isJWTSent = true;
    event.jwt = localStorage.getItem("authToken") ?? undefined;
  }
  console.log("[DEBUG] [WS] Outgoing :", event);
  ws?.send(JSON.stringify(event));
}

function error(error: any) {
  console.error("[WS]", error);
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
    error(ev);
  };

  ws.onmessage = (ev) => {
    const event = JSON.parse(ev.data) as IWSPacket;
    console.log("[DEBUG] [WS] Incoming :", event);

    switch (event.type) {
      case WSPacketTypes.ERROR:
        error(event.payload);
        break;

      case WSPacketTypes.PING:
        console.log(
          `Connected via WS with ${
            new Date().getTime() - (event.payload as IPing).startTime
          }ms`
        );
        break;

      case WSPacketTypes.MESSAGE:
        onNewMessage(event.payload as IMessage);
        break;

      case WSPacketTypes.MESSAGE_EDITED:
        onMessageEdited(event.payload as IMessage);
        break;

      case WSPacketTypes.MESSAGE_DELETION:
        onMessageDeleted(event.payload as IMessage);
        break;

      case WSPacketTypes.FRIEND_LIST:
        onFriendList((event.payload as IRelationList).relations);
        break;

      case WSPacketTypes.FRIEND_CONNECTION:
        onFriendConnection((event.payload as IConnection).id);
        break;

      case WSPacketTypes.FRIEND_DISCONNECTION:
        onFriendDisconnection((event.payload as IConnection).id);
        break;

      default:
        console.log(event);
        break;
    }
  };
}
