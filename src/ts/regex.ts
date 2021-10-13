const regexUsername = /^[a-zA-Z]{2,20}$/;
const regexEmail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
const regexPhone = /^(0|\+33)[1-9]([-. ]?[0-9]{2}){4}$/;
const regexAdress = /^[a-zA-Z]([-'\s]?[a-zA-Z]){4,99}$/;
const regexCityAndCountry = /^[a-zA-Z]([-'\s]?[a-zA-Z]){1,49}$/;


if(document.getElementById("register-username") as HTMLFormElement){
    const username =  document.getElementById("register-username") as HTMLFormElement;
    const nameError =  document.getElementById("name-error") as HTMLFormElement;
    username.addEventListener("keyup", ()=> {
        if (username.value.match(regexUsername)) {
            nameError.style.display = 'none';
            return true;
        } else {
            nameError.style.display = 'inline-block';
            return false;
        }
    });
    const email =  document.getElementById("email") as HTMLFormElement;
    const emailError =  document.getElementById("email-error") as HTMLFormElement;
    email.addEventListener("keyup", ()=> {
        
        console.log("checkEmail");

        if (email.value.match(regexEmail)) {
            emailError.style.display = 'none';
            return true;
        } else {
            emailError.style.display = 'inline-block';
            return false;
        }
    });

    const phone =  document.getElementById("phone") as HTMLFormElement;
    const phoneError =  document.getElementById("phone-error") as HTMLFormElement;
    phone.addEventListener("keyup", ()=> {

        if (phone.value.match(regexPhone)) {
            phoneError.style.display = 'none';
            return true;
        } else {
            phoneError.style.display = 'inline-block';
            return false;
        }
    });

    const adress =  document.getElementById("adress") as HTMLFormElement;
    const adressError =  document.getElementById("adress-error") as HTMLFormElement;
    adress.addEventListener("keyup", ()=> {

        if (adress.value.match(regexAdress)) {
            adressError.style.display = 'none';
            return true;
        } else {
            adressError.style.display = 'inline-block';
            return false;
        }
    });

    const city =  document.getElementById("city") as HTMLFormElement;
    const cityError =  document.getElementById("city-error") as HTMLFormElement;
    city.addEventListener("keyup", ()=> {

        if (city.value.match(regexCityAndCountry)) {
            cityError.style.display = 'none';
            return true;
        } else {
            cityError.style.display = 'inline-block';
            return false;
        }
    });

    const country =  document.getElementById("country") as HTMLFormElement;
    const countryError =  document.getElementById("country-error") as HTMLFormElement;
    country.addEventListener("keyup", ()=> {

        if (country.value.match(regexCityAndCountry)) {
            countryError.style.display = 'none';
            return true;
        } else {
            countryError.style.display = 'inline-block';
            return false;
        }
    });


    const password =  document.getElementById("register-password") as HTMLFormElement;
    const passwordError =  document.getElementById("password-error") as HTMLFormElement;
    const numberLetterError =  document.getElementById("number-letter-error") as HTMLFormElement;
    const specCharacError =  document.getElementById("spec-charac-error") as HTMLFormElement;
    const betweenError =  document.getElementById("between-error") as HTMLFormElement;

    password.addEventListener("keyup", ()=> {
        let correct = true;
        let mdp = password.value;
        passwordError.style.display = 'none';

        // on teste la présence de chiffre et de lettres
        if (mdp.search(/\d/) !== -1 && mdp.search(/[a-zA-Z]/) !== -1) {
            passwordError.style.display = "none";
            numberLetterError.style.display = "none";
        } else {
            correct = false;
            numberLetterError.style.display = "inline-block";
        }

        // on teste la présence de caractère spécial
        if (mdp.search(/[!#$%&?+=()@*."]/) !== -1) {
            passwordError.style.display = "none";
            specCharacError.style.display = "none";
        } else {
            correct = false;
            specCharacError.style.display = "inline-block";
        }

        // on teste la longueur du mot de passe
        if (mdp.length >= 5 && mdp.length <= 15) {
            betweenError.style.display = "none";
        } else {
            correct = false;
            betweenError.style.display = "inline-block";
        }

        if (!correct) {
            passwordError.style.display = "inline-block";
        }

        return correct;
    });


    const confirmPassword =  document.getElementById("confirm-password") as HTMLFormElement;
    const confirmPasswordError =  document.getElementById("confirm-password-error") as HTMLFormElement;
    confirmPassword.addEventListener("keyup", ()=> {
        let correct = true;
        confirmPasswordError.style.display = 'none';

        if (confirmPassword.value !== password.value) {
            correct = false;
            confirmPasswordError.style.display = 'inline-block';
        }

        return correct;
    });  
}