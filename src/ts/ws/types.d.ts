import { IMessage } from "../types/responses";

interface IPing {
  startTime: number;
}

type IWSPayload = IMessage | IMessageInput | IPing;

interface IWSPacket {
  type: WSPacketTypes;
  jwt?: string;
  payload: IWSPayload | string;
}
