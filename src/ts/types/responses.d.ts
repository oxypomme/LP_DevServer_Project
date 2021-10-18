import { StatusCodes } from "http-status-codes";

type APIResult =
  | IToken
  | IUser
  | IUser[]
  | IGroup
  | IGroup[]
  | IRelation
  | IRelation[]
  | ILocation
  | IMessage
  | IMessage[];

interface IResponse<T extends APIResult> {
  status: StatusCodes;
  payload: string | T;
}

interface IToken {
  token: string;
  created_at: string;
  expires: string;
}

interface IUser {
  id: number;
  username: string;
  email: string;
  phone: string | number;
  birthdate: string;
  address: string;
  city: string;
  country: string;
  status: EStatus;
  created_at: string;
}

interface IGroup {
  id: number;
  name: string;
  owner: IUser;
  members: IUser[];
  messages: IMessage[];
  created_at: string;
}

interface IRelation {
  id: number;
  sender: IUser;
  target: IUser;
  created_at: string;
}

interface ILocation {
  id: number;
  long: number;
  lat: number;
  user: IUser;
  updated_at: string;
}

interface IMessage {
  id: number;
  content: string;
  attachement?: string;
  sender: IUser;
  target?: IUser;
  created_at: string;
  updated_at: string;
}
