class Page{
    constructor(domContainer, url){
        this.url = url;
        this.domContainer = domContainer;
        this.step=1;
        this.content;
        this.initData = null;
        this.orderData;
        this.visitorData;
        window.louvres.page = this;
        this.domContainer.innerHTML = this.loading();

        this.render();
    };

    async render(){
        var datainit = await this[`template${this.step}`]();
        this.domContainer.innerHTML = datainit;
    }

    async init(){
        var answer  = await fetch(`http://${this.url}`, {method: 'GET'});
        this.initData = await answer.json();
    }

    async postAPI(data, url){
        let answer = await fetch(`http://${this.url}/${url}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        this.content = await answer.json();
    }



    async template1(){
        await this.init();

        // DatePicker Init
        jQuery(function($){
            $('#visitDay').datepicker({
                dateFormat: "yy/mm/dd",
                minDate: 0,
                maxDate: 365,
            });

        });
        return `
        <section id='form1'>
            <div class='fields'>
                <div class='field'>
                    <label for='email' >Email</label>
                    <input type='text' name='email' id='email' placeholder='exemple@exemple.com'required/>
                </div>
                <div class='field half'>
                    <label for='numberOfTicket' >Quantité</label>
                    <input type='text' name='numberOfTicket' id='numberOfTicket' placeholder='1' required  />
                </div>
                <div class='field half'>
                    <label for='visitDay' >Date de la visite</label>
                    <input type='text' name='visitDay' id='visitDay' placeholder='AAAA/MM/JJ' required />
                </div>
                <div class='field half'>
                    <input type='checkbox' id='halfday' />
                    <label for='halfday' >Demi-Journée (de 14h à 20h)</label>
                </div>
            </div>
                <button onclick="louvres.page.stepChange()">Validez</button>
         </section>`;
    }

    async template2(){

        // DatePicker Init
        jQuery(function($){
            $('#birthday').datepicker({
                dateFormat: "yy/mm/dd",
                maxDate: -30
            });

        });

        return `
            <section id='form2'>
                <h4>Visiteur</h4>
                <div class=''>
                    <div class=''>
                        <label for='lastname'>Nom</label>
                        <input type='text' name='lastname' id='lastname' placeholder='Dupont' required />
                    </div>
                    <div class=''>
                        <label for='name' >Prénom</label>
                        <input type='text' name='firstname' id='firstname' placeholder='Jean' required />
                    </div>
                    <div class=''>
                        <label for='birthday' >Date de naissance</label>
                        <input type='text' name='birthday' id='birthday' placeholder='JJ/MM/AAAA' required;/>
                    </div>
                    <div class=''>
                        <input type='checkbox' id='reduice' />
                        <label for='reduice' >Tarif réduit (Avec justificatif)</label>
                    </div>
                </div>
                <button onclick="louvres.page.stepChange()">Validez</button>
            </section>`;
    }

    async template3(){

    }

    async stepChange(){
        switch(this.step){
            case 1:
                //this.domContainer.innerHTML = this.loading();
                let halfday = document.getElementById('halfday');

                // Array data to Post
                let orderData = {
                    email: document.getElementById('email').value,
                    number_of_ticket: parseInt(document.getElementById('numberOfTicket').value, 10),
                    visit_day: document.getElementById('visitDay').value,
                    half_day: halfday.checked,
                    total_price: 0,
                }

                await this.postAPI(orderData,'initOrder');
                this.orderData = await this.content;

                //on change d'étape
                this.step++;
                this.render();
                break;

            case 2:
                let reduice = document.getElementById('reduice');
                console.log(this.orderData.ordered_unique_id);

                var visitorData = {
                    ordered_unique_id: this.orderData.ordered_unique_id,
                    visitor:[{
                        lastname: document.getElementById('lastname').value,
                        firstname: document.getElementById('firstname').value,
                        birthday: document.getElementById('birthday').value,
                        country: 75,
                        reduice: reduice.checked
                    }
                    ]
                };

                await this.postAPI(visitorData, 'validOrder');
                this.visitorData = await this.content;

                this.step++;
                this.render();
                break;
        }
    }

    loading(){
        return `
		je suis en train de charger
		`;

    }

};