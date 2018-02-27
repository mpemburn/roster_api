<div id="rapi_choice">
    <div>Please indicate whether you are a becoming a new member or renewing an existing membership</div>
    <div>
        <label for="type_choice_new"><input id="type_choice_new" name="type_choice" type="radio" value="0">New Member</label>
    </div>
    <div>
        <label for="type_choice_renew"><input id="type_choice_renew" name="type_choice" type="radio" value="1">Renewing Member</label>
    </div>
</div> <!-- rapi_choice -->
<div id="rapi_renew">
    <form method="POST" accept-charset="UTF-8" id="member_fetch">
        <div class="form-group">
            <div class="col-md-12 row">
                <div class="col-md-8">
                    <label for="email" class="col-md-12 control-label">Email</label>
                    <input class="col-md-12 required" placeholder="Email *" name="member_email" type="email" value="">
                </div>
                <div class="col-md-3">
                    <label for="zip" class="col-md-12 control-label">Zip</label>
                    <input class="col-md-12 required" placeholder="Zip *" name="member_zip" type="text" value="">
                </div>
            </div>
            <div id="member_fetch_button" class="col-md-11">
                <div class="col-md-12 field-wrapper text-right">
                    <button class="button" id="existing_member" name="existing_member">Submit</button>
                </div>
            </div>
            <div class="fetch-message" id="member_fetch_message" class="col-md-11"></div>
        </div>
    </form>
</div>  <!-- rapi_renew -->
<div id="rapi_form">
    <form method="POST" accept-charset="UTF-8" id="member_update">
        <input name="id" type="hidden" value="">
        <input name="active" type="hidden" value="">
        <div class="panel-body row">
            <main class="main-column col-md-12" style="border: 1px solid gray;">
                <div class="form-group">
                    <label for="name" class="col-md-3 control-label">Name</label>
                    <div class="col-md-12 row">
                        <div class="col-md-2 field-wrapper">
                            <select class="col-md-12" name="prefix">
                                <option value="">Select</option>
                                <?php foreach ($prefixes as $prefix) :
                                    $selected = ($prefix == $user->prefix) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $prefix; ?>" <?php echo $selected; ?>><?php echo $prefix; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 field-wrapper">
                            <input class="col-md-12 required" placeholder="First Name *" name="first_name" type="text" value="">
                        </div>
                        <div class="col-md-2 field-wrapper">
                            <input class="col-md-12" placeholder="Middle" name="middle_name" type="text" value="">
                        </div>
                        <div class="col-md-3 field-wrapper">
                            <input class="col-md-12 required" placeholder="Last Name *" name="last_name" type="text" value="">
                        </div>
                        <div class="col-md-2 field-wrapper">
                            <select class="col-md-12 field-wrapper" name="suffix">
                                <option value="">Select</option>
                                <?php foreach ($suffixes as $suffix) :
                                    $selected = ($suffix == $user->suffix) ? 'selected' : '';
                                    ?>
                                    <option
                                        value="<?php echo $suffix; ?>" <?php echo $selected; ?>><?php echo $suffix; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address" class="col-md-3 control-label">Address</label>
                    <div class="col-md-12">
                        <div class="col-md-10 field-wrapper">
                            <input class="col-md-12 required" placeholder="Address 1 *" name="address_1" type="text" value="">
                        </div>
                    </div>
                    <div class="col-md-12 col-md-offset-1">
                        <div class="col-md-10 field-wrapper">
                            <input class="col-md-12" placeholder="Address 2" name="address_2" type="text" value="">
                        </div>
                    </div>
                    <div class="col-md-12 col-md-offset-1 row">
                        <div class="col-md-4 field-wrapper">
                            <input class="col-md-12 required" placeholder="City *" name="city" type="text" value="">
                        </div>
                        <div class="col-md-2 field-wrapper">
                            <select class="col-md-12 required" name="state">
                                <option value="">Select</option>
                                <?php foreach ($states as $state) :
                                    $selected = ($state == $user->state) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $state; ?>" <?php echo $selected; ?>><?php echo $state; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 field-wrapper">
                            <input class="col-md-12 required" placeholder="Zip *" name="zip" type="text" value="">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-md-3 control-label">Email</label>
                    <div class="col-md-12">
                        <div class="col-md-10 field-wrapper">
                            <input class="col-md-12 required" placeholder="Email *" name="email" type="email" value="">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3">
                        Cell phone
                    </div>
                    <div class="col-md-3 field-wrapper">
                        <input class="col-md-12" placeholder="Cell Phone" name="cell_phone" type="text" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label for="address" class="col-md-3 control-label">Emergency Contact</label>
                    <div class="col-md-9">
                        <div class="col-md-10 field-wrapper">
                            <input class="col-md-12 required" placeholder="Contact Name *" name="contact_name" type="text" value="">
                        </div>
                    </div>
                    <div class="col-md-3 field-wrapper">
                        <input class="col-md-12 required" placeholder="Contact Phone *" name="contact_phone" type="text" value="">
                    </div>
                </div>
                <div class="form-group">
                    <div id="paypal-button-container"></div>

                    <script>
                        paypal.Button.render({

                            env: 'sandbox', // sandbox | production

                            // PayPal Client IDs - replace with your own
                            // Create a PayPal app: https://developer.paypal.com/developer/applications/create
                            client: {
                                sandbox:    'AZDxjDScFpQtjWTOUtWKbyN_bDt4OgqaF4eYXlewfBP4-8aqX3PiV8e1GWU6liB2CUXlkA59kJXE7M6R',
                                production: '<insert production client id>'
                            },

                            // Show the buyer a 'Pay Now' button in the checkout flow
                            commit: true,

                            // payment() is called when the button is clicked
                            payment: function(data, actions) {

                                // Make a call to the REST api to create the payment
                                return actions.payment.create({
                                    payment: {
                                        transactions: [
                                            {
                                                amount: { total: '0.01', currency: 'USD' }
                                            }
                                        ]
                                    }
                                });
                            },

                            // onAuthorize() is called when the buyer approves the payment
                            onAuthorize: function(data, actions) {

                                // Make a call to the REST api to execute the payment
                                return actions.payment.execute().then(function() {
                                    window.alert('Payment Complete!');
                                });
                            }

                        }, '#paypal-button-container');

                </script>

                    <div class="col-md-12">
                        <div class="col-md-12 field-wrapper text-right">
                            <button class="button" id="save_member" name="save_member">Submit</button>
                        </div>
                    </div>
                </div>
            </main>

        </div>
    </form>
</div> <!-- rapi_form -->