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
