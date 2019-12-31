// Set your publishable key: remember to change this to your live publishable key in production
// See your keys here: https://dashboard.stripe.com/account/apikeys
var stripe = Stripe('pk_test_2OFpbfgSgBUGkoZWohSOcNy800yq9SWnpv');
var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
var style = {
    base: {
        // Add your base input styles here. For example:
        fontSize: '16px',
        color: "#32325d",
    }
};

// Create an instance of the card Element.
var card = elements.create('card', {style: style});

// Add an instance of the card Element into the `card-element` <div>.
card.mount('#card-element');

card.addEventListener('change', function(event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

var submitButton = document.getElementById('submit');
var client = window.louvres.page.paymentData.clientSecret;

submitButton.addEventListener('click', function(ev) {

    stripe.confirmCardPayment(client, {
        payment_method: {
            card: card,
            // billing_details: {
            //     name: 'Jenny Rosen'
            // }
        }
    }).then(function(result) {
        if (result.error) {
            // Show error to your customer (e.g., insufficient funds)
            console.log(result.error.message);
        } else {
            // The payment has been processed!
            if (result.paymentIntent.status === 'succeeded') {
               alert('Paiement réussi')
                window.louvres.page.stripeStep_payment();
            }
        }
    });
});