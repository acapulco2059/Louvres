class Page{
    constructor(domContainer, url){
        this.url = url;
        this.domContainer = domContainer;
        this.step=1;
        window.louvres.page = this;
        this.domContainer.innerHTML = this.loading();

        this.render();
    };

    render(){
        this.domContainer.innerHTML= this[`template${this.step}`]();
    }

    async init(){
        let answer  = await fetch(`http://${this.url}`, {method: 'GET'});
        let content = await answer.json();
        return content;
    }



    template1(){
        this.init().then(content => console.log(content.open));

        // DatePicker Init
        jQuery(function($){
            $('#visitDay').datepicker({
                dateFormat: "yy/mm/dd",
                minDate: 0,
                maxDate: 365,
            });

        });

        return `
		<input id="email" name="email" placeholder="exemple@louvre.fr">
		<input id="numberOfTicket" name="numberOfTicket" placeholder="1">
		<label for="datepicker">Date de visite</label>
		<input type="text" id="visitDay">
        <label for="halfday">Demi-journée</label>
		<input type="checkbox" id="halfday" name="halfday">
		<button onclick="louvres.page.stepChange()">Validez</button>
		`;
    }

    template2(){
         console.log(content.number_of_ticket);
    }

    async stepChange(){
        switch(this.step){
            case 1:
                //this.domContainer.innerHTML = this.loading();
                let halfday = document.getElementById('halfday');
                let visitDay = document.getElementById('visitDay').value;

                // Array data to Post
                let collectedData = {
                    email: document.getElementById('email').value,
                    number_of_ticket: parseInt(document.getElementById('numberOfTicket').value, 10),
                    visit_day: document.getElementById('visitDay').value,
                    half_day: halfday.checked,
                    total_price: 0,
                }

                console.log(collectedData);
                //je fait une requete à mon serveur en lui envoyant collectedData
                const answer = await fetch(`http://${this.url}/initOrder`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(collectedData)
                });
                var content = await answer.json();

                console.log(content);

                //on change d'étape
                this.step++;
                this.render();
                break;
            case 2:
                return 'je suis à l\'étape 2';
                break;
        }
    }

    loading(){
        return `
		je suis en train de charger
		`;

    }

};