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
            <label for="email" class="col-md-3 control-label">Email</label>
            <div class="col-md-11">
                <div class="col-md-10 field-wrapper">
                    <input class="col-md-12 required" placeholder="Email *" name="email" type="email"
                           value="mark@pemburn.com">
                </div>
            </div>
            <label for="email" class="col-md-3 control-label">Zip</label>
            <div class="col-md-11">
                <div class="col-md-2 field-wrapper">
                    <input class="col-md-12 required" placeholder="Zip *" name="zip" type="text"
                           value="<?php echo $user->zip; ?>">
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-12 field-wrapper text-right">
                    <button class="button" id="existing_member" name="existing_member">Submit</button>
                </div>
            </div>
        </div>
    </form>
</div>  <!-- rapi_renew -->
<div id="rapi_form">
    <form method="POST" accept-charset="UTF-8" id="member_update">
        <input name="id" type="hidden" value="<?php echo $user->id; ?>">
        <input name="active" type="hidden" value="<?php echo $user->id; ?>">
        <div class="panel-heading">
            <h4><?php echo $user->first_name . ' ' . $user->last_name; ?></h4>
            <div>Member since: <?php echo $user->member_since_date; ?></div>
        </div>
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
                                    <option
                                        value="<?php echo $prefix; ?>" <?php echo $selected; ?>><?php echo $prefix; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 field-wrapper">
                            <input class="col-md-12 required" placeholder="First Name *" name="first_name" type="text"
                                   value="<?php echo $user->first_name; ?>">
                        </div>
                        <div class="col-md-2 field-wrapper">
                            <input class="col-md-12" placeholder="Middle" name="middle_name" type="text"
                                   value="<?php echo $user->middle_name; ?>">
                        </div>
                        <div class="col-md-3 field-wrapper">
                            <input class="col-md-12 required" placeholder="Last Name *" name="last_name" type="text"
                                   value="<?php echo $user->last_name; ?>">
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
                            <input class="col-md-12 required" placeholder="Address 1 *" name="address_1" type="text"
                                   value="<?php echo $user->address_1; ?>">
                        </div>
                    </div>
                    <div class="col-md-12 col-md-offset-1">
                        <div class="col-md-10 field-wrapper">
                            <input class="col-md-12" placeholder="Address 2" name="address_2" type="text"
                                   value="<?php echo $user->address_2; ?>">
                        </div>
                    </div>
                    <div class="col-md-12 col-md-offset-1 row">
                        <div class="col-md-4 field-wrapper">
                            <input class="col-md-12 required" placeholder="City *" name="city" type="text"
                                   value="<?php echo $user->city; ?>">
                        </div>
                        <div class="col-md-2 field-wrapper">
                            <select class="col-md-12 required" name="state">
                                <option value="">Select</option>
                                <?php foreach ($states as $state) :
                                    $selected = ($state == $user->state) ? 'selected' : '';
                                    ?>
                                    <option
                                        value="<?php echo $state; ?>" <?php echo $selected; ?>><?php echo $state; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 field-wrapper">
                            <input class="col-md-12 required" placeholder="Zip *" name="zip" type="text"
                                   value="<?php echo $user->zip; ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-md-3 control-label">Email</label>
                    <div class="col-md-12">
                        <div class="col-md-10 field-wrapper">
                            <input class="col-md-12 required" placeholder="Email *" name="email" type="email"
                                   value="mark@pemburn.com">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-3">
                        Cell phone
                    </div>
                    <div class="col-md-3 field-wrapper">
                        <input class="col-md-12" placeholder="Cell Phone" name="cell_phone" type="text"
                               value="<?php echo $user->cell_phone; ?>">
                    </div>
                    <div class="col-md-3">
                        Home phone
                    </div>
                    <div class="col-md-3 field-wrapper">
                        <input class="col-md-12" placeholder="Home Phone" name="home_phone" type="text"
                               value="<?php echo $user->home_phone; ?>">
                    </div>
                </div>
                <div class="form-group">
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