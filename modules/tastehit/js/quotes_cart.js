$(document).ready(function(){
    // GUEST CHECKOUT / NEW ACCOUNT MANAGEMENT
    if ((typeof isLogged == 'undefined' || !isLogged) || (typeof isGuest !== 'undefined' && isGuest)) {
        if (addressEnabled == 1) {
            $('#address_enabled').val('1');
        }
        if (isGuest) {
            //$('.is_customer_param').hide();
            $('#opc_account_form').show('slow');
            $('#is_new_customer').val('0');
            $('#opc_account_choice, #opc_invoice_address').hide();
            //$('#new_account_title').html(txtInstantCheckout);
        }
        else if (guestCheckoutEnabled && !isLogged) {
            $('#opc_account_choice').show();
            $('#opc_account_form, #opc_invoice_address').hide();

            $(document).on('click', '#opc_createAccount', function (e) {
                e.preventDefault();
                $('.is_customer_param').show();
                $('#opc_account_form').slideDown('slow');
                $('#is_new_customer').val('1');
                $('#opc_account_choice, #opc_invoice_address').hide();

            });
            $(document).on('click', '#opc_guestCheckout', function (e) {
                e.preventDefault();
                $('.is_customer_param').hide();
                $('#opc_account_form').slideDown('slow');
                $('#is_new_customer').val('0');
                $('#opc_account_choice, #opc_invoice_address').hide();
                //$('#new_account_title').html(txtInstantCheckout);
                $('#submitAccount').attr({id: 'submitGuestAccount', name: 'submitGuestAccount'});

            });
        }
        else {
            $('#opc_account_choice').hide();
            $('#is_new_customer').val('1');
            $('.is_customer_param, #opc_account_form').show();
            $('#opc_invoice_address').hide();
        }

        // SHOW ADDRESS
        $(document).on('click', '#show_address_form', function (e) {
            e.preventDefault();
            $('#address_enabled').val('1');
            $('#address_block').slideDown('slow');
            $(this).hide();
        });

        // LOGIN FORM
        $(document).on('click', '#openLoginFormBlock', function (e) {
            e.preventDefault();
            $('#openNewAccountBlock').show();
            $(this).hide();
            $('#login_form_content').slideDown('slow');
            $('#new_account_form_content').slideUp('slow');
        });
        // LOGIN FORM SENDING
        $(document).on('click', '#SubmitLogin', function (e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                headers: {"cache-control": "no-cache"},
                url: authenticationUrl + '?rand=' + new Date().getTime(),
                async: false,
                cache: false,
                dataType: "json",
                data: 'SubmitLogin=true&ajax=true&email=' + encodeURIComponent($('#login_email').val()) + '&passwd=' + encodeURIComponent($('#login_passwd').val()) + '&token=' + static_token,
                success: function (jsonData) {
                    if (jsonData.hasError) {
                        var errors = '<b>' + txtThereis + ' ' + jsonData.errors.length + ' ' + txtErrors + ':</b><ol>';
                        for (var error in jsonData.errors)
                            //IE6 bug fix
                            if (error !== 'indexOf')
                                errors += '<li>' + jsonData.errors[error] + '</li>';
                        errors += '</ol>';
                        $('#opc_login_errors').html(errors).slideDown('slow');
                    }
                    else {
                        // update token

                        //static_token = jsonData.token;
                        //isLogged = 1;

                        //updateNewAccountToAddressBlock();
                        window.location.reload();
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (textStatus !== 'abort') {
                        error = "TECHNICAL ERROR: unable to send login informations \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
                        if (!!$.prototype.fancybox)
                            $.fancybox.open([
                                {
                                    type: 'inline',
                                    autoScale: true,
                                    minHeight: 30,
                                    content: '<p class="fancybox-error">' + error + '</p>'
                                }
                            ], {
                                padding: 0
                            });
                        else
                            alert(error);
                    }
                }
            });
        });
    }
});

