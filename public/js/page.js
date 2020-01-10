class Page{
    constructor(domContainer, url){
        this.url            = url;
        this.domContainer   = domContainer;
        this.content;
        this.initData       = null; //main data (price + date + etc.)
        this.orderData;
        this.visitorData;
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
        this.convertData();
        this.observer = new MutationObserver(()=>{this.initOrder_datepickerInit()});
        this.observer.observe(this.domContainer, { attributes: false, childList: true });
        this.render("initOrder");
    }

    /**
     *
     */
    initOrder_datepickerInit(){
        this.observer.disconnect();

        const initDate = this.initData.holiday;
        const open = this.initData.open;
        let holiday = [];
        initDate.forEach(element => {
            let date = new Date();
            let valueTemp = element.split('/');
            if(valueTemp.length == 2){
                element = date.getFullYear() + '/' + element;
            }
            holiday.push(element);
        });

        $('#visitDay').datepicker({
            format: 'yyyy/mm/dd',
            keyboardNavigation: false,
            datesDisabled: holiday,
            daysOfWeekDisabled: this.closed,
            startDate: 'd',
            endDate: '+365d',
            language: "fr",
        });
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
            $('#birthday'+i).datepicker({
                format: 'yyyy/mm/dd',
                endDate: 'd',
                language: "fr",
            });

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
        this.render("stripeStep");
    }

    async stripeStep_payment(){
        var stripeData = {
            ordered_unique_id: this.orderData.ordered_unique_id,
            payment_intent_id: this.visitorData.stripe_id
        };
        await this.postAPI(stripeData, 'payment');
        this.paymentData = await this.content;
        this.confirm_payment();
    }

    confirm_payment(){
        if(this.paymentData.status = "succeeded")
        {
            this.render("confirmPayment");
        }

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

    convertData() {
        this.closed = [];
        if(!this.initData.open.Sunday) this.closed.push(0);
        if(!this.initData.open.Monday) this.closed.push(1);
        if(!this.initData.open.Tuesday) this.closed.push(2);
        if(!this.initData.open.Wednesday) this.closed.push(3);
        if(!this.initData.open.Thursday) this.closed.push(4);
        if(!this.initData.open.Friday) this.closed.push(5);
        if(!this.initData.open.Saturday) this.closed.push(6);
        this.closed = this.closed.join(",");
    }




    template_initOrder(){
        let headDom = document.querySelector('head');
        let stripeScript = document.createElement('script');
        stripeScript.src = 'js/validator/initOrderValidator.js';
        headDom.appendChild(stripeScript);

        return `
        <h3 class="text-center">Billetterie en Ligne du Musée du Louvre</h3>
        <section id='form1'>
            <div class='col-md-6'>
                <div class="col-md-12">
                    <label for='email' >Email</label>
                    <input class="form-control" type='text' name='email' id='email' placeholder='exemple@exemple.com' " required/>
                    <div>
                        <span id="emailError" class="formErrors"></span>
                    </div>
                </div>
                <div class='col-md-12'>
                    <label for='numberOfTicket' >Quantité</label>
                    <input class="form-control" type='text' name='numberOfTicket' id='numberOfTicket' value='1' " required/>
                    <div>
                        <span id="numberOfTicketError" class="formErrors"></span>
                    </div>

                </div>
                <div class='col-md-12'>
                    <label for='visitDay' >Date de la visite</label>
                    <div class="input-group date">
                      <input type="text" id='visitDay' class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class='col-md-12'>
                    <label class="form-check-label" for='halfday' >Demi-Journée (de 14h à 20h)</label>
                    <input class="form-check-input" type='checkbox' id='halfday' o/>
                </div>
                 <button class='col-md-4' type="button" onclick="verifInitOrder()">Validez</button>
            </div>
         </section>`;
    }

    template_visitorsList(){
        let headDom = document.querySelector('head');
        let stripeScript = document.createElement('script');
        stripeScript.src = 'js/validator/visitorsListValidator.js';
        headDom.appendChild(stripeScript);

        var str = "";
        for (let i = 1; i <= this.orderData.number_of_ticket; i++) {
            str += this.template_visitorsListPartial(i);
        }

        return `
            <section id='form2'>
                ${str}
                <button onclick="verifVisitorsList()">Validez</button>
            </section>`;
    }

    template_visitorsListPartial(id){

        return `
            <h4>Visiteur ${id}</h4>
            <div class="">            
                <div class='col-md-6'>
                    <label for='lastname'>Nom</label>
                    <input class="form-control" type='text' name='lastname${id}' id='lastname${id}' placeholder='Dupont' required />
                    <div>
                        <span id="lastnameError${id}" class="formErrors"></span>
                    </div>
                </div>
                <div class='col-md-6'>
                    <label for='name' >Prénom</label>
                    <input class="form-control" type='text' name='firstname${id}' id='firstname${id}' placeholder='Jean' required />
                    <div>
                        <span id="firstnameError${id}" class="formErrors"></span>
                    </div>
                </div>
                <div class='col-md-12'>
                    <label for='birthday' >Date de naissance</label>
                    <div class="input-group date">
                      <input type="text" id='birthday${id}' class="form-control" required><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
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

    template_confirmPayment(){
        return `
        <div class="">
            <h3>Paiement accepté</h3>
            <div class="">
                <span class="">Un email de confirmation vient de vous être envoyé sur votre boite : ${this.paymentData.email}</span>
            </div>
            <div class="">
                <span class="">Nous vous souhaitons une agréable visite au musée du Louvre</span>
            </div>
            <div class="">
                <button onclick="this.initOrder_initialize()">Retour à l'accueil</button>
            </div>    
        </div>
        `;
    }


    template_loading(){
        return `
        <div class="d-flex justify-content-center">
          <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
		`;

    }



};