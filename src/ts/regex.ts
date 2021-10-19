const regexUsername = /^[a-zA-Z]{2,20}$/;
const regexEmail =
  /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
const regexPhone = /^(0|\+33)[1-9]([-. ]?[0-9]{2}){4}$/;
const regexAddress = /^[a-zA-Z\-0-9]([-'\s]?[a-zA-Z\-0-9]){4,99}$/;
const regexCityAndCountry = /^[a-zA-Z]([-'\s]?[a-zA-Z]){1,49}$/;

if (document.getElementById("register-username") as HTMLFormElement) {
  const username = document.getElementById(
    "register-username"
  ) as HTMLFormElement;
  const nameError = document.getElementById("name-error") as HTMLFormElement;
  username.addEventListener("keyup", () => {
    if (username.value.match(regexUsername)) {
      nameError.classList.remove("show");
      return true;
    } else {
      nameError.classList.add("show");
      return false;
    }
  });
  const email = document.getElementById("email") as HTMLFormElement;
  const emailError = document.getElementById("email-error") as HTMLFormElement;
  email.addEventListener("keyup", () => {
    if (email.value.match(regexEmail)) {
      emailError.classList.remove("show");
      return true;
    } else {
      emailError.classList.add("show");
      return false;
    }
  });

  const phone = document.getElementById("phone") as HTMLFormElement;
  const phoneError = document.getElementById("phone-error") as HTMLFormElement;
  phone.addEventListener("keyup", () => {
    if (phone.value.match(regexPhone)) {
      phoneError.classList.remove("show");
      return true;
    } else {
      phoneError.classList.add("show");
      return false;
    }
  });

  const address = document.getElementById("address") as HTMLFormElement;
  const addressError = document.getElementById(
    "address-error"
  ) as HTMLFormElement;
  address.addEventListener("keyup", () => {
    if (address.value.match(regexAddress)) {
      addressError.classList.remove("show");
      return true;
    } else {
      addressError.classList.add("show");
      return false;
    }
  });

  const city = document.getElementById("city") as HTMLFormElement;
  const cityError = document.getElementById("city-error") as HTMLFormElement;
  city.addEventListener("keyup", () => {
    if (city.value.match(regexCityAndCountry)) {
      cityError.classList.remove("show");
      return true;
    } else {
      cityError.classList.add("show");
      return false;
    }
  });

  const country = document.getElementById("country") as HTMLFormElement;
  const countryError = document.getElementById(
    "country-error"
  ) as HTMLFormElement;
  country.addEventListener("keyup", () => {
    if (country.value.match(regexCityAndCountry)) {
      countryError.classList.remove("show");
      return true;
    } else {
      countryError.classList.add("show");
      return false;
    }
  });

  const password = document.getElementById(
    "register-password"
  ) as HTMLFormElement;
  const passwordError = document.getElementById(
    "password-error"
  ) as HTMLFormElement;
  const numberLetterError = document.getElementById(
    "number-letter-error"
  ) as HTMLFormElement;
  const specCharacError = document.getElementById(
    "spec-charac-error"
  ) as HTMLFormElement;
  const betweenError = document.getElementById(
    "between-error"
  ) as HTMLFormElement;

  password.addEventListener("keyup", () => {
    let correct = true;
    const mdp = password.value;
    passwordError.classList.remove("show");

    if (mdp.search(/\d/) !== -1 && mdp.search(/[a-zA-Z]/) !== -1) {
      passwordError.classList.remove("show");
      numberLetterError.classList.remove("show");
    } else {
      correct = false;
      numberLetterError.classList.add("show");
    }

    if (mdp.search(/[!#$%&?+=()@*."]/) !== -1) {
      passwordError.classList.remove("show");
      specCharacError.classList.remove("show");
    } else {
      correct = false;
      specCharacError.classList.add("show");
    }

    if (mdp.length >= 5 && mdp.length <= 15) {
      betweenError.classList.remove("show");
    } else {
      correct = false;
      betweenError.classList.add("show");
    }

    if (!correct) {
      passwordError.classList.add("show");
    }

    return correct;
  });

  const confirmPassword = document.getElementById(
    "confirm-password"
  ) as HTMLFormElement;
  const confirmPasswordError = document.getElementById(
    "confirm-password-error"
  ) as HTMLFormElement;
  confirmPassword.addEventListener("keyup", () => {
    let correct = true;
    confirmPasswordError.classList.remove("show");

    if (confirmPassword.value !== password.value) {
      correct = false;
      confirmPasswordError.classList.add("show");
    }

    return correct;
  });
}
