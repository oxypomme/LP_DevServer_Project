import { StatusCodes } from "http-status-codes";
import { fetchAPI } from "./api";
import { IToken } from "./types/responses";

const loginForm = document.getElementById("login-form") as HTMLFormElement;

function onAuthed(token: string) {
  localStorage.setItem("authToken", token);
  document.location.href = "/welcome";
}
(async () => {
  if (loginForm && !loginForm.onsubmit) {
    // Checking if previous token is still valid
    const prevToken = localStorage.getItem("authToken");
    if (prevToken) {
      const { status: authStatus } = await fetchAPI("GET /auth");
      if (authStatus === StatusCodes.OK) {
        onAuthed(prevToken);
        return;
      }
    }

    // Adding action to the login form
    loginForm.onsubmit = async (e) => {
      e.preventDefault();
      const data = new FormData(loginForm);

      const username = data.get("username") as string;
      const password = data.get("password") as string;
      if (username && password) {
        try {
          const { status, payload } = await fetchAPI<IToken>("POST /auth", {
            username,
            password,
          });
          if (status === StatusCodes.OK && typeof payload !== "string") {
            onAuthed(payload.token);
          } else {
            throw payload;
          }
        } catch (error) {
          const result = document.querySelector("#login-result");

          if (result) {
            result.innerHTML = error as string;
          }
        }
      }
      loginForm.reset();
    };
  }
})();
