class Page{
    constructor(domContainer, url){
        this.url            = url;
        this.domContainer   = domContainer;
        this.content;
        this.initData       = null; //main data (price + date + etc.)
        this.orderData;
        this.visitorData;
        this.paymentIntentData;
        this.paymentData;
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

    /**
     *
     */
    initOrder_datepickerInit(){
        this.observer.disconnect();
        var date = new Date();
        var year = date.getFullYear();
        var month = date.getMonth();
        var day = date.getDate();
        const picker = datepicker('#visitDay', {
            startDay: 1,
            customDays: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            customMonths: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            minDate: new Date(),
            maxDate: new Date(year + 1, month, day),
            // formatter: (input, date, instance) => {
            //     const value = date.toLocaleDateString();
            //     input.value = value;
            // },

            },
        );
    }

    /**
     *
     */
    initOrder_finalize(){
        //this.domContainer.innerHTML = this.loading();
        let halfday = document.getElementById('halfday');
        let date = new Date(document.getElementById('visitDay').value);

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

    /**
     *
     * @param orderData
     * @returns {Promise<void>}
     */
    async visitorsList_initialize(orderData){
        await this.postAPI(orderData,'initOrder');
        this.orderData = await this.content;
        this.observer  = new MutationObserver(()=>{this.visitorsList_datepickerInit()});
        this.observer.observe(this.domContainer, { attributes: false, childList: true });
        this.render("visitorsList");
    }

    /**
     *
     */
    visitorsList_datepickerInit(){
        this.observer.disconnect();
        const datepickers = [];
        for (let i = 1; i <= this.orderData.number_of_ticket; i++) {
            datepickers[i] = datepicker(`#birthday${i}`, {
                startDay: 1,
                customDays: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                customMonths: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                maxDate: new Date(),
            })
        }
    }

    /**
     *
     */
    visitorsList_finalize(){
        let numberOfTicket = this.orderData.number_of_ticket;

        let visitor = [];
        for(let j=1 ; j <= numberOfTicket; j++) {
            let reduice = document.getElementById(`reduice${j}`);

            var visitorDatas = {
                lastname: document.getElementById(`lastname${j}`).value,
                firstname: document.getElementById(`firstname${j}`).value,
                birthday: document.getElementById(`birthday${j}`).value,
                country: 75,
                reduice: reduice.checked
            };
            visitor.push(visitorDatas);
        }

        var visitorsDatas = {
            ordered_unique_id: this.orderData.ordered_unique_id,
            visitor: visitor
        }

        this.stripeStep_initialize(visitorsDatas);
    }

    /**
     *
     * @param visitorsDatas
     * @returns {Promise<void>}
     */
    async stripeStep_initialize(visitorsDatas){
        await this.postAPI(visitorsDatas, 'validOrder');
        this.visitorData = await this.content;
        await this.postAPI(visitorsDatas, 'initPayment');
        this.paymentIntentData = await this.content;
        this.render("stripeStep");
    }

    async stripeStep_payment(){
        var stripeData = {
            ordered_unique_id: this.orderData.ordered_unique_id,
            payment_intent_id: this.paymentIntentData.id
        };
        await this.postAPI(stripeData, 'payment');
        this.paymentData = await this.content;
    }

    /**
     *
     * @param step
     */
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
        <h3 class="text-center">Billetterie en Ligne du Musée du Louvre</h3>
        <section id='form1'>
            <div class='col-md-12'>
                <div class="col-md-6">
                    <label for='email' >Email</label>
                    <input class="form-control" type='text' name='email' id='email' placeholder='exemple@exemple.com'required/>
                </div>
                <div class='col-md-6'>
                    <label for='numberOfTicket' >Quantité</label>
                    <input class="form-control" type='text' name='numberOfTicket' id='numberOfTicket' placeholder='1' required/>
                </div>
                <div class='col-md-6'>
                    <label for='visitDay' >Date de la visite</label>
                    <input class="form-control" type='text' name='visitDay' id='visitDay' placeholder='AAAA/MM/JJ' required/>
                </div>
                <div class='col-md-6'>
                    <label class="form-check-label" for='halfday' >Demi-Journée (de 14h à 20h)</label>
                    <input class="form-check-input" type='checkbox' id='halfday' />
                </div>
            </div>
                <button type="button" onclick="louvres.page.initOrder_finalize()">Validez</button>
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
            <div class="">            
                <div class='col-md-6'>
                    <label for='lastname'>Nom</label>
                    <input class="form-control" type='text' name='lastname${id}' id='lastname${id}' placeholder='Dupont' required />
                </div>
                <div class='col-md-6'>
                    <label for='name' >Prénom</label>
                    <input class="form-control" type='text' name='firstname${id}' id='firstname${id}' placeholder='Jean' required />
                </div>
                <div class='col-md-12'>
                    <label for='birthday' >Date de naissance</label>
                    <input class="form-control" type='text' name='birthday${id}' id='birthday${id}' placeholder='JJ/MM/AAAA' required;/>
                </div>
                <div class='row col-md-12'>
                    <label class="form-check-label" for='reduice' >Tarif réduit (Avec justificatif)</label>
                    <input clas="form-check-input" type='checkbox' id='reduice${id}'/>
                </div>
            </div>`;
    }

    template_stripeStep(){
        let headDom = document.querySelector('head');
        let stripeScript = document.createElement('script');
        stripeScript.src = 'js/client.js';
        headDom.appendChild(stripeScript);

        var summary = "";
        for (let i = 0; i < this.orderData.number_of_ticket; i++){
            summary += this.template_stripeStepPartial(i);
        }

        return `
        <h3 class="orderTitle">Résumé de votre comamnde</h3>
        <section>
        ${summary}
        <div class="">
            <span class="">Total de la commande :</span> ${this.visitorData.total_price}
        </div>
        </section>
        
        <div id="card_form">
            <div id="card-element">
              <!-- Elements will create input elements here -->
            </div>
            
            <!-- We'll put the error messages in this element -->
            <div id="card-errors" role="alert"></div>
                        
            <button id="submit">Payez ${this.visitorData.total_price} €</button>        
        </div>

        `;
    }

    template_stripeStepPartial(i){

        return `
        <div class="">
            <div class="">
            Visiteur n°${i + 1}
            </div>
            <div class="">
                <span class="">Nom :</span> ${this.visitorData.users[i].lastname}
            </div>
            <div class="">
                <span class="">Prénom :</span> ${this.visitorData.users[i].firstname}
            </div>
            <div class="">
                <span class="">Prix du billet :</span> ${this.visitorData.users[i].price} €
            </div>
            <div class="">
                <span class="">Numéro de billet :</span> ${this.visitorData.users[i].unique_id}
            </div>
        </div>
        `;
    }


    template_loading(){
        return `
		je suis en train de charger
		`;

    }



};