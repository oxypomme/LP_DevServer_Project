import { StatusCodes } from "http-status-codes";
import { fetchAPI } from "./api";
import { redirectToAuth } from "./auth";
import { IUser } from "./types/responses";

const profileForm = document.getElementById("profile-form") as HTMLFormElement;
(async () => {
  if (profileForm) {
    const { status, payload: user } = await fetchAPI<IUser>(
      "GET /api/users/me"
    );
    if (status === StatusCodes.OK && typeof user !== "string") {
      const usernameEl =
        profileForm.querySelector<HTMLInputElement>("#register-username");
      if (usernameEl) usernameEl.value = user.username;

      const emailEl = profileForm.querySelector<HTMLInputElement>("#email");
      if (emailEl) emailEl.value = user.email;

      const phoneEl = profileForm.querySelector<HTMLInputElement>("#phone");
      if (phoneEl) phoneEl.value = user.phone.toString();

      const birthdateEl =
        profileForm.querySelector<HTMLInputElement>("#birthdate");
      if (birthdateEl) {
        const [date, month, year] = new Date(user.birthdate)
          .toLocaleDateString("fr", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
          })
          .split("/");
        birthdateEl.value = `${year}-${month}-${date}`;
      }

      const addressEl = profileForm.querySelector<HTMLInputElement>("#address");
      if (addressEl) addressEl.value = user.address;

      const cityEl = profileForm.querySelector<HTMLInputElement>("#city");
      if (cityEl) cityEl.value = user.city;

      const countryEl = profileForm.querySelector<HTMLInputElement>("#country");
      if (countryEl) countryEl.value = user.country;

      profileForm.onsubmit = async (e) => {
        e.preventDefault();
        const data = new FormData(profileForm);

        const username = data.get("username") as string;
        const email = data.get("email") as string;
        const phone = data.get("phone") as string;
        const birthdate = data.get("birthdate") as string;
        const address = data.get("address") as string;
        const city = data.get("city") as string;
        const country = data.get("country") as string;
        const password = data.get("password") as string;
        const confirmPassword = data.get("confirm-password") as string;
        const userStatus = parseInt(data.get("status") as string);
        if (
          username.length != 0 &&
          email.length != 0 &&
          phone.length != 0 &&
          birthdate.length != 0 &&
          address.length != 0 &&
          city.length != 0 &&
          country.length != 0 &&
          password.length != 0
        ) {
          if (password === confirmPassword) {
            const { status, payload } = await fetchAPI<IUser>(
              `PUT /api/users/${user.id}`,
              {
                username,
                email,
                phone,
                birthdate,
                address,
                city,
                country,
                password,
                status: userStatus as EStatus,
              } as IUserInput
            );
            if (status === StatusCodes.OK && typeof payload !== "string") {
              //? Notify user of success ?
              document.location.href = "/welcome";
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

      const delBtn =
        profileForm.querySelector<HTMLButtonElement>("#delete-profile");
      if (delBtn)
        delBtn.onclick = async (e) => {
          e.preventDefault();
          const { status } = await fetchAPI(`DELETE /api/users/${user.id}`);
          if (status === StatusCodes.OK) {
            redirectToAuth();
          }
          // TODO: error management
        };
    }
  }
})();
