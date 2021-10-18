import { StatusCodes } from "http-status-codes";
import { fetchAPI } from "./api/fetch";
import { IToken } from "./api/responses";

const loginForm = document.getElementById("login-form") as HTMLFormElement;

if (loginForm && !loginForm.onsubmit) {
  loginForm.onsubmit = async (e) => {
    e.preventDefault();
    const data = new FormData(loginForm);

    const username = data.get("username") as string;
    const password = data.get("password") as string;
    if (username && password) {
      const { status, payload } = await fetchAPI<IToken>("POST /auth", {
        username,
        password,
      });
      if (status === StatusCodes.OK && typeof payload !== "string") {
        localStorage.setItem("authToken", payload.token);
        document.location.href = "/welcome";
      } else {
        const result = document.querySelector("#login-result");

        if (result) {
          result.innerHTML = payload as string;
        }
      }
    }
    loginForm.reset();
  };
}
