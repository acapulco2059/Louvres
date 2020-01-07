function surligne(champ, erreur)
{
    if(erreur)
        champ.style.backgroundColor = "#fba";
    else
        champ.style.backgroundColor = "";
}

function verifVisitorsList()
{
    var result = [];
    for(let k=1; k <= window.louvres.page.orderData.number_of_ticket ;k++){
        let firstname = document.getElementById('firstname'+k);
        let lastname = document.getElementById('lastname'+k);
        let birthday = document.getElementById('birthday'+k);

        let checkFirstname = verifFirstname(firstname, k);
        let checkLastname = verifLastname(lastname, k);

        result.push(checkFirstname);
        result.push(checkLastname);
    }
    if(!result.includes(false)){
        louvres.page.visitorsList_finalize();
    }
}

function verifFirstname(champ, id) {
    let regex = /^[a-zA-Z]{2,25}$/;
    let firstnameError = document.getElementById('firstnameError' + id);

    if (!regex.test(champ.value)) {
        surligne(champ, true);
        firstnameError.textContent = "Veuilliez saisir un Prénom valide"
        return false;
    } else {
        surligne(champ, false);
        firstnameError.textContent = "";
        return true;
    }
}


function verifLastname(champ, id)
{
    let regex = /^[a-zA-Z]{2,25}$/;
    let lastnameError = document.getElementById('lastnameError'+ id);

    if(!regex.test(champ.value))
    {
        surligne(champ, true);
        lastnameError.textContent = "Veuilliez saisir un Nom valide"
        return false;
    }
    else
    {
        surligne(champ, false);
        lastnameError.textContent = "";
        return true;
    }
}

// function verifBirthday(champ)
// {
//
// }