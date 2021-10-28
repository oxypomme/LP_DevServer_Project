import { APIResult, IResponse } from "../types/responses";

type HttpMethods = "GET" | "POST" | "PUT" | "DELETE";

type AuthEndpoints = "auth";

type BaseGroupEndpoints = "groups" | `groups/${number}`;

type RelationEndpoints = "relations" | `relations/${number}`;

type MessagesEndpoints = `messages/${number}` | `messages/${number}/${number}`;

type UserEndpoints =
  | "users"
  | "users/me"
  | `users/${number}`
  | `users/${number}/${
      | BaseGroupEndpoints
      | RelationEndpoints
      | "location"
      | MessagesEndpoints}`;

type GroupMembersEndpoints = "members" | `members/${number}`;

type GroupEndpoints =
  | BaseGroupEndpoints
  | `groups/${number}/${GroupMembersEndpoints | MessagesEndpoints}`;

type ApiEndoints = AuthEndpoints | `api/${UserEndpoints | GroupEndpoints}`;

type APIUrl = `${HttpMethods} /${ApiEndoints}`;

export const fetchAPI = async <
  T extends APIResult,
  I extends APIInput = APIInput
>(
  endpoint: APIUrl,
  body?: I | CSRF<I>,
  init?: RequestInit,
  headers?: HeadersInit
): Promise<IResponse<T>> => {
  const [, method, path] = endpoint.match(/(?<method>\S*)\s(?<path>\S*)/) ?? [];

  return (
    await fetch(path, {
      method,
      headers: {
        Authorization: "Bearer " + localStorage.getItem("authToken"),
        Accept: "application/json",
        "Content-Type": "application/json",
        ...headers,
      },
      body: JSON.stringify(body),
      ...init,
    })
  ).json();
};
