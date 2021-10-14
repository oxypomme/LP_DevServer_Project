type HttpMethods = "GET" | "POST" | "PUT" | "DELETE";

type ApiEndoints = string;

type ApiInput = `/api/${ApiEndoints}`;

const fetchAPI = (
  endpoint: ApiInput,
  method: HttpMethods = "GET",
  init?: RequestInit
) => {
  return fetch(`/api/${endpoint}`, {
    method,
    headers: {
      Authorization: "Bearer " + localStorage.getItem("authToken"),
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    ...init,
  });
};
