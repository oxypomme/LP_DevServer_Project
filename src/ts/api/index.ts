import { APIResult, IResponse } from "../types/responses";

type HttpMethods = "GET" | "POST" | "PUT" | "DELETE";

type AuthEndpoints = "auth";

type BaseGroupEndpoints = "groups" | `groups/${number}`;

type RelationEndpoints = "relations" | `relations/${number}`;

type MessagesEndpoints = "messages" | `messages/${number}`;

type UserEndpoints =
  | "users"
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

type ApiInput = `${HttpMethods} /${ApiEndoints}`;

export const fetchAPI = async <T extends APIResult>(
  endpoint: ApiInput,
  body?: unknown,
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
