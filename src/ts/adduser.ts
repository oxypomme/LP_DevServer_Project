import { fetchAPI } from "./api/fetch";
import { IUser } from "./api/responses";

const registerForm = document.getElementById("signup-form") as HTMLFormElement;
if (registerForm && !registerForm.onsubmit) {
  registerForm.onsubmit = async (e) => {
    e.preventDefault();
    const data = new FormData(registerForm);

    const username = data.get("username") as string;
    const email = data.get("email") as string;
    const phone = data.get("phone") as string;
    const birthdate = data.get("birthdate") as string;
    const adress = data.get("adress") as string;
    const city = data.get("city") as string;
    const country = data.get("country") as string;
    const password = data.get("password") as string;
    const confirmPassword = data.get("confirm-password") as string;
    if (
      username.length != 0 &&
      email.length != 0 &&
      phone.length != 0 &&
      birthdate.length != 0 &&
      adress.length != 0 &&
      city.length != 0 &&
      country.length != 0 &&
      password.length != 0
    ) {
      if (password === confirmPassword) {
        const { status, payload } = await fetchAPI<IUser>("POST /api/users", {
          username,
          email,
          phone,
          birthdate,
          adress,
          city,
          country,
          password,
        });
      }
    } else {
      const submitError = document.getElementById(
        "submit-error"
      ) as HTMLFormElement;
      submitError.style.display = "inline-block";
    }
  };
}
