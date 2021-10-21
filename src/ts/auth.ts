import { StatusCodes } from "http-status-codes";
import { fetchAPI } from "./api";
import { IToken } from "./types/responses";

const loginForm = document.getElementById("login-form") as HTMLFormElement;
const signoutBtn = document.getElementById(
  "signout-button"
) as HTMLButtonElement;

function onAuthed(token: string) {
  localStorage.setItem("authToken", token);
  document.location.href = "/welcome";
}
/**
 * Remove sotred token and redirect to auth page
 */
export function redirectToAuth(): void {
  localStorage.removeItem("authToken");
  document.location.href = "/";
}

const nonProtected = ["/", "/register", "/api"];

(async () => {
  // Checking if previous token is still valid
  const prevToken = localStorage.getItem("authToken");
  const isRouteProtected = !nonProtected.includes(location.pathname);
  // If token is set
  if (prevToken) {
    const { status: authStatus } = await fetchAPI("GET /auth");
    // If token isn't valid
    if (authStatus !== StatusCodes.OK) {
      redirectToAuth();
      return;
    }
    // If on a non protected route and token is valid
    if (!isRouteProtected && authStatus === StatusCodes.OK) {
      onAuthed(prevToken);
      return;
    }
  } else if (isRouteProtected) {
    // If token isn't set & route protected
    redirectToAuth();
  }

  if (loginForm && !loginForm.onsubmit) {
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

if (signoutBtn && !signoutBtn.onclick) {
  signoutBtn.onclick = (e) => {
    e.preventDefault();
    redirectToAuth();
  };
}
