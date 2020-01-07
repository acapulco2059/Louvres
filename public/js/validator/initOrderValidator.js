function surligne(champ, erreur)
{
    if(erreur)
        champ.style.backgroundColor = "#fba";
    else
        champ.style.backgroundColor = "";
}

function getActualDate() {
    var actualDay = new Date();
    var d = actualDay.getDate();

    if (d < 10) { d = '0' + d }
    var m = actualDay.getMonth() + 1
    if (m < 10) { m = '0' + m }
    var y = actualDay.getFullYear();

    var result = y + '/' + m + '/' + d;
    return result;
}

function verifInitOrder(){
    let email = document.getElementById('email');
    let numberOfTicket = document.getElementById('numberOfTicket');
    let visiDate = document.getElementById('visitDay')
    let halfday = document.getElementById('halfday');

    let checkEmail = this.verifEmail(email);
    let checkNumberOfTicket = this.verifNumberOfTicket(numberOfTicket);


    if(checkEmail && checkNumberOfTicket)
    {
        louvres.page.initOrder_finalize();
    };
}

function verifEmail(champ)
{
    var regex = /^[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/;
    if(!regex.test(champ.value))
    {
        surligne(champ, true);
        let emailError = document.getElementById('emailError');
        emailError.textContent = "Veuilliez saisir une adresse email valide"
        return false;
    }
    else
    {
        surligne(champ, false);
        emailError.textContent = "";
        return true;
    }
}

function verifNumberOfTicket(champ)
{
    var numberOfTicket = parseInt(champ.value);

    if(isNaN(numberOfTicket) || champ.value.length >2){
        surligne(champ,true);
        let numberOfTicketError = document.getElementById('numberOfTicketError');
        numberOfTicketError.textContent = "Veuillez saisir un nombre de place correct (entre 1 et 15 places)"
        return false;
    }
    else
    {
        surligne(champ, false);
        numberOfTicketError.textContent = "";
        return true;
    }

}

function halfdayChecked(champ) {
    let date = new Date();
    let actualHour = date.getHours();
    let actualDate = this.getActualDate();
    let inputDate = champ;
    console.log(inputDate);
    if (actualDate == inputDate) {
        if (actualHour >= 20) {
            alert('Le musée est fermé');
            document.getElementById('visitDay').value = '';
        }
        else if (actualHour >= 19) {
            document.getElementById("halfday").checked = true;
            document.getElementById("halfday").disabled = true;
            alert('Le musée va fermer changer de jour');
            document.getElementById('visitDay').value = '';
        }
        else if (actualHour >= 14) {
            document.getElementById("halfday").checked = true;
            document.getElementById("halfday").disabled = true;
        }
    }
    else {
        document.getElementById("halfday").checked = false;
        document.getElementById("halfday").disabled = false;
    }


}