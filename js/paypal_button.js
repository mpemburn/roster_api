paypal.Button.render({

    env: 'sandbox', // sandbox | production

    // PayPal Client IDs
    client: {
        sandbox:    jsNamespace.paypalSandboxKey,
        production: jsNamespace.paypalProductionKey
    },

    // Show the buyer a 'Pay Now' button in the checkout flow
    commit: true,

    // payment() is called when the button is clicked
    payment: function(data, actions) {
        var payment = jsNamespace.rosterApi.getPaymentInfo();
        // Make a call to the REST api to create the payment
        return actions.payment.create(payment);
    },

    // onAuthorize() is called when the buyer approves the payment
    onAuthorize: function(data, actions) {

        // Make a call to the REST api to execute the payment
        return actions.payment.execute().then(function() {
            jsNamespace.rosterApi.paypalSuccess();
        });
    },
    onCancel: function(data, actions) {
        var foo;
    }

}, '#paypal-button-container');
