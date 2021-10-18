import { IMessage } from "../types/responses";

export enum WSPacketTypes {
  ERROR = "error",
  MESSAGE = "message",
  PING = "ping",
}

export interface IPing {
  startTime: number;
}

export type IWSPayload = IMessage | IMessageInput | IPing;

export interface IWSPacket {
  type: WSPacketTypes;
  jwt?: string;
  payload: IWSPayload | string;
}
