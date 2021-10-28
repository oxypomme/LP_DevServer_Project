import { IMessage, IRelationList } from "../types/responses";

interface IPing {
  startTime: number;
}

interface IConnection {
  id: number;
}

interface IDisconnection {
  id: number;
}

type IWSPayload =
  | IMessage
  | IMessageInput
  | IPing
  | IRelationList
  | IConnection
  | IDisconnection;

interface IWSPacket {
  type: WSPacketTypes;
  jwt?: string;
  payload: IWSPayload | string;
}
