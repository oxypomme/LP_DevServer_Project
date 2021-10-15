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

type ApiEndoints = AuthEndpoints | UserEndpoints | GroupEndpoints;

type ApiInput = `${HttpMethods} /api/${ApiEndoints}`;

const fetchAPI = (endpoint: ApiInput, init?: RequestInit) => {
  const [, method, path] = endpoint.match(/(?<method>\S*)\s(?<path>\S*)/) ?? [];

  return fetch(path, {
    method,
    headers: {
      Authorization: "Bearer " + localStorage.getItem("authToken"),
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    ...init,
  });
};
