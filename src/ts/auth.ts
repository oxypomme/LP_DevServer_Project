export async function loginUser(
  username: string,
  password: string
): Promise<ILoginResponse> {
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
      const resp = await loginUser(username, password);
      if (resp.status === 200 && resp.token) {
        localStorage.setItem("authToken", resp.token);
        document.location.href="http://localhost:3000/welcome";
      } else {
        const result = document.querySelector("#login-result");

        if (result && resp.message) {
          result.innerHTML = resp.message;
        }
      }
    }
    loginForm.reset();
  };
}
