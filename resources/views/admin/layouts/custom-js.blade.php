<script type="text/javascript">
    $(document).ready(function () {

        $("#type").change(function () {
            var type = $(this).val();
            if (type == 'PPPOE') {
                $('#pool_name').show();
                $('#sharedUser').hide();
                $('#server_password').show();
                $('#client_ip').hide();
            } else if (type == 'Hotspot') {
                $('#sharedUser').show();
                $('#pool_name').show();
                $('#server_password').show();
                $('#client_ip').hide();
            } else if (type == 'IP') {
                $('#sharedUser').hide();
                $('#pool_name').hide();
                $('#server_password').hide();
                $('#client_ip').show();
            } else {
                $('#sharedUser').hide();
                $('#pool_name').hide();
                $('#server_password').hide();
                $('#client_ip').hide();
            }
            $('#pool').empty();
            $('#pool').append('<option value="">Select Pool Name</option>');

            $('#plan').empty();
            $('#plan').append('<option value="">Select Plan Name</option>');

        });

        $('#type').change(function () {

            var type = $(this).val();
            if (type) {
                $.ajax({
                    url: "{{ route('ajaxSelect.server') }}",
                    data: {_token: '{{ csrf_token() }}'},
                    type: "GET",
                    dataType: "json",
                    success: function (data) {
                        $('#server').empty();
                        $('#server').append('<option value="">Select Server Name</option>');

                        $.each(data, function (index, regenciesObj) {
                            $('#server').append('<option value="' + regenciesObj.id + '">' + regenciesObj.server_name + '</option>');
                        })

                    }
                });
            } else {
                $('#server').empty();
                $('#server').append('<option value="">Select Server Name</option>');

            }
        });

        $('#type').change(function () {

            var type = $(this).val();
            if (type) {
                $.ajax({
                    url: "{{ route('ajaxSelect.plan') }}",
                    data: {type: type, _token: '{{ csrf_token() }}'},
                    type: "GET",
                    dataType: "json",
                    success: function (data) {
                        $('#plan_resell').empty();
                        $('#plan_resell').append('<option value="">Select Plan Name</option>');

                        $.each(data, function (index, regenciesObj) {
                            $('#plan_resell').append('<option value="' + regenciesObj.id + '">' + regenciesObj.plan_name + '</option>');
                        })

                    }
                });
            } else {
                $('#plan_resell').empty();
                $('#plan_resell').append('<option value="">Select Server Name</option>');

            }
        });

        $('#server').change(function () {

            var server_id = $(this).val();
            if (server_id) {
                $.ajax({
                    url: "{{ route('ajaxSelect.pool') }}",
                    data: {server_id: server_id, _token: '{{ csrf_token() }}'},
                    type: "GET",
                    dataType: "json",
                    success: function (data) {
                        $('#pool').empty();
                        $('#pool').append('<option value="">Select Pool Name</option>');

                        $.each(data, function (index, regenciesObj) {
                            $('#pool').append('<option value="' + regenciesObj.id + '">' + regenciesObj.pool_name + '</option>');
                        })
                    }
                });
            } else {
                $('#pool').empty();
                $('#pool').append('<option value="">Select Pool Name</option>');
            }
        });

        $("#server").change(function () {
            var server_id = $(this).val();
            var type = $('#type').val();
            if (server_id) {
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: "{{ route('ajaxSelect.plan') }}",
                    data: {server_id: server_id, type: type, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        $('#plan').empty();
                        $('#plan').append('<option value="">Select Plan Name</option>');

                        $.each(data, function (index, regenciesObj) {
                            $('#plan').append('<option value="' + regenciesObj.id + '">' + regenciesObj.plan_name + '</option>');
                        })

                    }
                });
            } else {
                $('#plan').empty();
                $('#plan').append('<option value="">Select Plan Name</option>');
            }

        });

        $("#client").change(function () {
            var client_id = $(this).val();
            if (client_id) {
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: "{{ route('ajaxSelect.invoice.plan') }}",
                    data: {client_id: client_id, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        // Clear fields first
                        $('#plan_price').empty();
                        $('#bandwidth').empty();
                        $('#discount_id').empty();
                        $('#service_charge').empty();
                        $('#total').empty();
                        $('#s_total').empty();
                        $('#buy_plan_price').empty();
                        $('#otc_charge').empty();

                        // Bandwidth
                        $('#bandwidth').append('<input class="form-control" readonly name="bandwidth" id="bandwidth" type="text" value="' + data.plan.bandwidth.bandwidth_name + '" />');

                        // Discount
                        $('#discount_id').append('<input class="form-control" name="discount" id="discount" onkeyup="total_amount()" onkeydown="total_amount()" type="text" value="' + data.discount + '" />');

                        // Service Charge
                        $('#service_charge').append('<input class="form-control" name="service_charge" id="s_charge" onkeyup="total_amount()" onkeydown="total_amount()" type="text" value="' + data.charge + '" />');

                        // OTC Charge
                        $('#otc_charge').append('<input class="form-control" name="otc_charge" id="o_charge" onkeyup="total_amount()" onkeydown="total_amount()" type="text" value="' + data.otc_charge + '" />');


                        // Pricing: normal or reseller
                        if (!data.resellerId) {
                            $('#plan_price').append('<input class="form-control" onkeyup="total_amount()" id="p_price" name="plan_price" readonly type="text" value="' + data.plan.plan_price + '" />');
                            $('#total').append('<input class="form-control" onkeyup="total_amount()" id="total_price" name="total" readonly type="text" value="' + (parseFloat(data.plan.plan_price) + parseFloat(data.charge)+ parseFloat(data.otc_charge)) + '" />');
                            $('#s_total').append('<input class="form-control" onkeyup="" id="sub_total" name="sub_total" readonly type="text" value="' + (parseFloat(data.plan.plan_price) - parseFloat(data.discount) + parseFloat(data.charge)+ parseFloat(data.otc_charge)) + '" />');
                        } else {
                            $('#buy_plan_price').append('<input class="form-control" name="buy_plan_price" readonly type="text" value="' + data.reseller_plan.sell_price + '" />');
                            $('#plan_price').append('<input class="form-control" id="p_price" onkeyup="total_amount()" name="plan_price" readonly type="text" value="' + data.reseller_plan.reseller_sell_price + '" />');
                            $('#total').append('<input class="form-control" id="total_price" onkeyup="total_amount()" name="total" readonly type="text" value="' + (parseFloat(data.reseller_plan.reseller_sell_price) + parseFloat(data.charge)+ parseFloat(data.otc_charge)) + '" />');
                            $('#s_total').append('<input class="form-control" id="sub_total" onkeyup="total_amount()" name="sub_total" readonly type="text" value="' + (parseFloat(data.reseller_plan.reseller_sell_price) - parseFloat(data.discount) + parseFloat(data.charge)+ parseFloat(data.otc_charge)) + '" />');
                        }
                    }
                });
            } else {
                // Reset all fields if no client is selected
                $('#plan_price').html('<input class="form-control" id="p_price" name="plan_price" readonly type="text" value="0.00" />');
                $('#buy_plan_price').html('<input class="form-control" name="buy_plan_price" readonly type="text" value="0.00" />');
                $('#bandwidth').html('<input class="form-control" name="bandwidth" id="bandwidth" readonly type="text" value="" />');
                $('#discount_id').html('<input class="form-control" name="discount" id="discount" onkeyup="total_amount()" onkeydown="total_amount()" type="text" value="0.00" />');
                $('#service_charge').html('<input class="form-control" name="service_charge" id="s_charge"  type="text" value="0.00" />');
                $('#total').html('<input class="form-control" name="total" id="total_price" readonly type="text" value="0.00" />');
                $('#s_total').html('<input class="form-control" name="sub_total" id="sub_total" readonly type="text" value="0.00" />');
                $('#otc_charge').html('<input class="form-control" name="otc_charge" id="o_charge" type="text" value="0.00" />');
            }
        });

        $("#reseller").change(function () {
            var reseller_id = $(this).val();
            if (reseller_id) {
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: "{{ route('ajaxSelect.credit.limit') }}",
                    data: {reseller_id: reseller_id, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        $('#credit').empty();
                        $('#credit').append('<input class="form-control" readonly id="credit_limit" name="credit_limit" type="text" value="' + data.credit_limit + '" />')
//                        $('#amount_id').empty();
//                        $('#amount_id').append('<input class="form-control" name="amount" id="amount" onload="reseller_total_amount()" onchange="reseller_total_amount()" type="text" value="0.00" />');
                    }
                });
            } else {
                $('#credit').empty();
                $('#credit').append('<input class="form-control" readonly id="credit_limit" name="credit_limit" type="text" value="' + data.credit_limit + '" />')
                $('#discount_id').empty();
                $('#discount_id').append('<input class="form-control" name="discount" id="discount" onload="reseller_total_amount()" onchange="reseller_total_amount()" type="text" value="" />');
//                $('#amount_id').empty();
//                $('#amount_id').append('<input class="form-control" name="amount" id="amount" onload="reseller_total_amount()" onchange="reseller_total_amount()" type="text" value="" />');
            }

        });

    });


    function total_amount() {
        console.trace('total_amount called from');
        var p_price = $("#p_price").val();
        var discount = $("#discount").val();
        var service_crg = $("#s_charge").val();
        var otc_charge = $("#o_charge").val();
        if (!p_price) {
            p_price = parseFloat(0).toFixed(2);
        }
        if (!discount) {
            discount = parseFloat(0).toFixed(2);
        }
        if (!service_crg) {
            service_crg = parseFloat(0).toFixed(2);
        }
        if (!otc_charge) {
            otc_charge = parseFloat(0).toFixed(2);
        }
        var total_price = (parseFloat(p_price) + parseFloat(service_crg)+ parseFloat(otc_charge));
        document.getElementById("total_price").value = total_price.toFixed(2);
        if (isNaN(document.getElementById("total_price").value)) {
            document.getElementById("total_price").value = parseFloat(0).toFixed(2);
        }
        var sub_total = (parseFloat(total_price) - parseFloat(discount));

        document.getElementById("sub_total").value = sub_total.toFixed(2);
        if (isNaN(document.getElementById("sub_total").value)) {
            document.getElementById("sub_total").value = parseFloat(0).toFixed(2);
        }

    }

    function reseller_total_amount() {
        var amount = $("#amount").val();
        var discount = $("#discount").val();
        if (!amount) {
            amount = parseFloat(0).toFixed(2);
        }
        if (!discount) {
            discount = parseFloat(0).toFixed(2);
        }
        console.log('ok')

        amount = parseFloat(amount);
        document.getElementById("amount").value = amount.toFixed(2);

        if (isNaN(document.getElementById("amount").value)) {
            document.getElementById("amount").value = parseFloat(0).toFixed(2);
        }
        var sub_total = (parseInt(amount) - parseInt(discount));
        document.getElementById("sub_total").value = sub_total.toFixed(2);
        if (isNaN(document.getElementById("sub_total").value)) {
            document.getElementById("sub_total").value = parseFloat(0).toFixed(2);
        }

    }
</script>