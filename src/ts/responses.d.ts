interface IBaseResponse {
  status: number;
}

interface IErrorResponse extends IBaseResponse {
  message?: string;
}

interface ILoginResponse extends IErrorResponse {
  token?: string;
}
