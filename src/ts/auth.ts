export async function loginUser(
  username: string,
  password: string
): Promise<IResponse<IToken>> {
  return await (
    await fetch("/auth", {
      method: "POST",
      body: JSON.stringify({
        username,
        password,
      }),
    })
  ).json();
}

const loginForm = document.getElementById("login-form") as HTMLFormElement;

if (loginForm && !loginForm.onsubmit) {
  loginForm.onsubmit = async (e) => {
    e.preventDefault();
    const data = new FormData(loginForm);

    const username = data.get("username") as string;
    const password = data.get("password") as string;
    if (username && password) {
      const { status, payload } = await loginUser(username, password);
      if (status === 200 && typeof payload !== "string") {
        localStorage.setItem("authToken", payload.token);
        document.location.href = "http://localhost:3000/welcome";
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
