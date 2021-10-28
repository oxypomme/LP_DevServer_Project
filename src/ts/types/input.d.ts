type APIInput =
  | ILogin
  | IUserInput
  | IGroupInput
  | IRelationInput
  | ILocationInput
  | IMessageInput;

interface ICSRF {
  csrf_name: string;
  csrf_value: string;
}

type CSRF<T extends APIInput> = ICSRF & T;

interface ILogin {
  username: string;
  password: string;
}

interface IUserInput {
  username: string;
  password: string;
  email: string;
  phone: string;
  birthdate: string;
  address: string;
  city: string;
  country: string;
  status?: EStatus;
}

interface IGroupInput {
  name: string;
}

interface IRelationInput {
  target: number;
}

interface ILocationInput {
  long: number;
  lat: number;
}

interface IMessageInput {
  id?: number;
  content: string;
  attachement: string;
  target?: IUser;
  group?: IGroup;
}
