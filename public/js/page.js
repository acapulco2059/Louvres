class Page{
    constructor(domContainer, url){
        this.url            = url;
        this.domContainer   = domContainer;
        this.content;
        this.initData       = null; //main data (price + date + etc.)
        this.orderData;
        this.visitorData;
        window.louvres.page = this;
        this.render("loading");

        this.initOrder_initialize();
    };

    /**
     * get main data and 1st render
     * @return {[type]} [description]
     */
    async initOrder_initialize(){
        var answer    = await fetch(`http://${this.url}`, {method: 'GET'});
        this.initData = await answer.json();
        this.observer = new MutationObserver(()=>{this.initOrder_datepickerInit()});
        this.observer.observe(this.domContainer, { attributes: false, childList: true });
        this.render("initOrder");
    }

    initOrder_datepickerInit(){
        const picker = datepicker('visitDay', {
            customDays: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            customMonths: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre']
        });
    }

    initOrder_finalize(){
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

        this.visitorsList_initialize(orderData);
        this.observer.disconnect()
    }

    async visitorsList_initialize(orderData){
        await this.postAPI(orderData,'initOrder');
        this.orderData = await this.content;
        this.observer  = new MutationObserver(()=>{this.visitorsList_datepickerInit()});
        this.observer.observe(this.domContainer, { attributes: false, childList: true });
        this.render("visitorsList");
    }1

    visitorsList_datepickerInit(){
        const datepickers = [];
        for (let i = 1; i <= this.orderData.number_of_ticket; i++) {
            datepickers[i] = datepicker(`birthday${id}`, options)
        }
    }

    visitorsList_finalize(){
        let numberOfTicket = this.orderData.number_of_ticket;

        let visitor = [];
        for(let j=1 ; j <= numberOfTicket; j++) {
            let reduice = document.getElementById(`reduice${j}`);

            var visitorData = {
                    lastname: document.getElementById(`lastname${j}`).value,
                    firstname: document.getElementById(`firstname${j}`).value,
                    birthday: document.getElementById(`birthday${j}`).value,
                    country: 75,
                    reduice: reduice.checked
                };
                visitor.push(visitorData);
        }
        console.log(visitor)


        var visitorData = {
                ordered_unique_id: this.orderData.ordered_unique_id,
                visitor: visitor
            }

        this.stripeStep_initialize(visitorData);
    }

    async stripeStep_initialize(visitorData){
        await this.postAPI(visitorData, 'validOrder');
        this.visitorData = await this.content;
        this.render("stripeStep");
    }

    render(step){
        this.domContainer.innerHTML = this[`template_${step}`]();
    }

    async postAPI(data, url){
        this.render("loading");
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



    template_initOrder(){

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
                <button onclick="louvres.page.initOrder_finalize()">Validez</button>
         </section>`;
    }

    template_visitorsList(){
        var str = "";
        for (let i = 1; i <= this.orderData.number_of_ticket; i++) {
            str += this.template_visitorsListPartial(i);
        }

        return `
            <section id='form2'>
                ${str}
                <button onclick="louvres.page.visitorsList_finalize()">Validez</button>
            </section>`;
    }

    template_visitorsListPartial(id){

        return `
            <h4>Visiteur ${id}</h4>
            <div class=''>
                <div class=''>
                    <label for='lastname'>Nom</label>
                    <input type='text' name='lastname${id}' id='lastname${id}' placeholder='Dupont' required />
                </div>
                <div class=''>
                    <label for='name' >Prénom</label>
                    <input type='text' name='firstname${id}' id='firstname${id}' placeholder='Jean' required />
                </div>
                <div class=''>
                    <label for='birthday' >Date de naissance</label>
                    <input type='text' name='birthday${id}' id='birthday${id}' placeholder='JJ/MM/AAAA' required;/>
                </div>
                <div class=''>
                    <input type='checkbox' id='reduice${id}'/>
                    <label for='reduice' >Tarif réduit (Avec justificatif)</label>
                </div>
            </div>`;
    }

    template_loading(){
        return `
		je suis en train de charger
		`;

    }

    template_stripeStep(){
        return `
        todo template_stripeStep
        `;
    }

};