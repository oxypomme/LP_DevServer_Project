import { StatusCodes } from "http-status-codes";
import { fetchAPI } from "./api";
import { redirectToAuth } from "./auth";
import { IUser } from "./types/responses";

const registerForm = document.getElementById("signup-form") as HTMLFormElement;
if (registerForm && !registerForm.onsubmit) {
  registerForm.onsubmit = async (e) => {
    e.preventDefault();
    const data = new FormData(registerForm);

    const username = data.get("username") as string;
    const email = data.get("email") as string;
    const phone = data.get("phone") as string;
    const birthdate = data.get("birthdate") as string;
    const address = data.get("address") as string;
    const city = data.get("city") as string;
    const country = data.get("country") as string;
    const password = data.get("password") as string;
    const confirmPassword = data.get("confirm-password") as string;
    const csrf_name = data.get("csrf_name") as string;
    const csrf_value = data.get("csrf_value") as string;
    if (
      username.length != 0 &&
      email.length != 0 &&
      phone.length != 0 &&
      birthdate.length != 0 &&
      address.length != 0 &&
      city.length != 0 &&
      country.length != 0 &&
      password.length != 0 &&
      csrf_name &&
      csrf_value
    ) {
      if (password === confirmPassword) {
        const { status, payload } = await fetchAPI<IUser, CSRF<IUserInput>>(
          "POST /api/users",
          {
            username,
            email,
            phone,
            birthdate,
            address,
            city,
            country,
            password,
            csrf_name,
            csrf_value,
          }
        );
        if (status === StatusCodes.OK && typeof payload !== "string") {
          //? Notify user of success ?
          redirectToAuth();
        } else {
          throw payload;
        }
      }
    } else {
      const submitError = document.getElementById(
        "submit-error"
      ) as HTMLFormElement;
      submitError.style.display = "inline-block";
    }
  };
}
