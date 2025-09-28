@extends ('admin.layouts.master')
@section('title')
    Add Purchase Product
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">


                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('purchase.add.post') }}" method="POST">

                                    {{ csrf_field() }}

                                    <div class="form-group row">
                                        <label class="col-md-2 col-form-label">Purchase Date</label>
                                        <div class="input-group col-md-3">
                                            <input type="text" autocomplete="off" name="date" class="form-control"
                                                   placeholder="yyyy-mm-dd"
                                                   id="datepicker">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="ti-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>


                                    <table id="invoice-item-table" class="table table-bordered table-responsive-sm table-responsive-lg table-sm">
                                        <tr>
                                            <th width="4%">#</th>
                                            <th width="20%">Item Category</th>
                                            <th width="20%">Item Name</th>
                                            <th width="16%">Item Serial</th>
                                            <th width="8%">QTY</th>
                                            <th width="12%">Price</th>
                                            <th width="15%">Total</th>
                                            <th width="5%"></th>
                                        </tr>

                                        <tr>
                                            <td class="text-center"><span id="sr_no">1</span></td>
                                            <td>
                                                <select name="item_cat_name[]" id="item_cat_name1" data-srno="1" class="form-control pr_item_add">
                                                    <option value=''>Select Item Category</option>
                                                    @foreach ($cats as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="item_name[]" id="item_name1" data-srno="1" class="form-control item_name_add">
                                                    <option value=''>Select Item Name</option>
                                                </select>
                                            </td>
                                            <td hidden>
                                                <input type="hidden" name="item_sl[]" id="item_sl1" data-srno="1" class="form-control item_sl" value=""/>
                                            </td>

                                            <td>
                                                <input type="text" name="item_sku[]" id="item_sku1" data-srno="1" readonly class="form-control" value=""/>
                                            </td>

                                            <td><input type="text" name="order_item_quantity[]" id="order_item_quantity1" data-srno="1" class="form-control order_item_quantity" /></td>
                                            <td><input type="text" name="order_item_price[]" id="order_item_price1" data-srno="1" class="form-control number_only order_item_price" /></td>
                                            <td><input type="text" name="order_item_final_amount[]" id="order_item_final_amount1" data-srno="1" readonly class="form-control order_item_final_amount" /></td>
                                            <td></td>
                                        </tr>
                                    </table>

                                    <div class="form-group">
                                        <button type="button" name="add_row" id="add_row" class="btn btn-success btn-xs">Add More</button>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2 control-label">Grand Total</label>
                                        <label class="col-md-2 control-label"><b><span id="final_total_amt">0</span></b></label>
                                        <input type="hidden" id="final_total_amt_val" class="form-control" name="final_total" value="0">
                                    </div>


                                    <div class="form-group row">
                                        <label class="col-md-2 control-label">Pay From</label>
                                        <div class="col-md-4">
                                            <select name="account" id="account" class="form-control">
                                                <option value=''>Select Account</option>
                                                @foreach ($accounts as $ac)
                                                    <option value='{{ $ac->id }}'>{{ $ac->account_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <input type="hidden" name="total_item" id="total_item" value="1" />

                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" id="purchase-add" class="btn btn-info waves-effect waves-light">Add Purchase</button>
                                            <a href="{{ route('purchases') }}" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                    <!-- end row -->

                </div> <!-- end card-box -->
            </div><!-- end col -->
        </div>
        <!-- end row -->
@endsection

@section('custom_js')
    <script type="application/javascript">
        $(document).ready(function(){

            var final_total_amt = $('#final_total_amt').text();
            var count = 1;

            $(document).on('click', '#add_row', function(){
                count++;
                $('#total_item').val(count);
                var html_code = '';
                html_code += '<tr id="row_id_'+count+'">';
                html_code += '<td class="text-center"><span id="sr_no">'+count+'</span></td>';

                html_code += '<td>\n' +
                    '<select name="item_cat_name[]" id="item_cat_name'+count+'" data-srno="'+count+'" class="form-control pr_item_add">\n' +
                    '<option value="">Select Item Category</option>\n' +
                    '@foreach ($cats as $cat)'+
                    '<option value="{{ $cat->id }}">{{ $cat->name }}</option>\n' +
                    '@endforeach'+
                    '</select>\n' +
                    '</td>';

                html_code +='<td>\n' +
                    '<select name="item_name[]" id="item_name'+count+'" data-srno="'+count+'" class="form-control item_name_add">\n' +
                    '<option value="">Select Item Name</option>\n' +
                    '</select>\n' +
                    '</td>';

                html_code += '<td hidden><input type="hidden" name="item_sl[]" id="item_sl'+count+'" data-srno="'+count+'" class="form-control number_only item_sl" /></td>';
                html_code += '<td><input type="text" name="item_sku[]" id="item_sku'+count+'" data-srno="'+count+'" readonly class="form-control number_only" /></td>';

                html_code += '<td><input type="text" name="order_item_quantity[]" id="order_item_quantity'+count+'" data-srno="'+count+'" class="form-control number_only order_item_quantity" /></td>';
                html_code += '<td><input type="text" name="order_item_price[]" id="order_item_price'+count+'" data-srno="'+count+'" class="form-control number_only order_item_price" /></td>';
                html_code += '<td><input type="text" name="order_item_final_amount[]" id="order_item_final_amount'+count+'" data-srno="'+count+'" readonly class="form-control order_item_final_amount" /></td>';
                html_code += '<td><button type="button" name="remove_row" id="'+count+'" class="btn btn-danger btn-xs remove_row">X</button></td>';
                html_code += '</tr>';

                $('#invoice-item-table').append(html_code);
            });

            $(document).on('click', '.remove_row', function(){
                var row_id = $(this).attr("id");
                var total_item_amount = $('#order_item_final_amount'+row_id).val();
                alert(total_item_amount);
                var final_amount = $('#final_total_amt').text();
                var result_amount = parseFloat(final_amount) - parseFloat(total_item_amount);
                $('#final_total_amt').text(result_amount);
                $('#final_total_amt_val').val(result_amount);
                $('#row_id_'+row_id).remove();
                count--;
                $('#total_item').val(count);
            });



            function product_add(targetElement) {
                var cat = targetElement.val();
                var sr = targetElement.data('srno');

                if(cat !== ''){
                    $.ajax({
                        type: "GET",
                        dataType: "json",
                        url: "{{ route('purchase.select.product') }}",
                        data: {cat: cat, _token: '{{ csrf_token() }}'},
                        success: function (data) {
                            $('#item_name'+sr).empty();
                            $('#item_name'+sr).append('<option value="">Select Item Name</option>');

                            $.each(data, function (index, regenciesObj) {
                                $('#item_name' + sr).append('<option value="' + regenciesObj.id + '">' + regenciesObj.name + '</option>');
                            })
                        }
                    });
                } else {
                    $('#item_name' + sr).empty();
                    $('#item_name' + sr).append('<option value="">Select Item Name</option>');
                }
            }


            $(document).on('change', '.pr_item_add', function(e){
                var targetElement = $(e.target);
                product_add(targetElement);
            });


            function product_sl(targetElement) {
                var name = targetElement.val();
                var sr = targetElement.data('srno');

                if(name != ''){
                    $.ajax({
                        type: "GET",
                        dataType: "json",
                        url: "{{ route('purchase.product.sl') }}",
                        data: {id: name, _token: '{{ csrf_token() }}'},
                        success: function (data) {
                            $('#item_sl' + sr).val(data.serial_type);
                            if(data.serial_type == 2){
                                $('#item_sku' + sr).attr('readonly', false);
                            } else {
                                $('#item_sku' + sr).attr('readonly', true);
                            }
                        }
                    });
                } else {
                    $('#item_sl' + sr).val('');
                    $('#item_sku' + sr).attr('readonly', true);
                }
            }


            $(document).on('change', '.item_name_add', function(e){
                var targetElement = $(e.target);
                product_sl(targetElement);
            });



            function cal_final_total(count)
            {
                var final_item_total = 0;
                for(j=1; j<=count; j++)
                {
                    var quantity = 0;
                    var price = 0;
                    var item_total = 0;
                    var unit = $('#item_sl'+j).val();
                    if(unit == 2){
                        quantity = 1;
                    }else {
                        quantity = $('#order_item_quantity' + j).val();
                    }
                    if(quantity > 0)
                    {
                        price = $('#order_item_price'+j).val();
                        if(price > 0)
                        {
                            item_total = parseFloat(quantity) * parseFloat(price);
                            final_item_total = parseFloat(final_item_total) + parseFloat(item_total);
                            $('#order_item_final_amount'+j).val(item_total);
                        } else {
                            $('#order_item_final_amount'+j).val(0);
                        }
                    } else{
                        $('#order_item_final_amount'+j).val(0);
                    }
                }
                $('#final_total_amt').text(final_item_total);
                $('#final_total_amt_val').val(final_item_total);
            }

            $(document).on('keyup', '.order_item_quantity', function(){
                cal_final_total(count);
            });

            $(document).on('keyup', '.order_item_price', function(){
                cal_final_total(count);
            });



            $('#purchase-add').click(function(){
                if($.trim($('#account').val()).length == 0)
                {
                    alert("Please Enter Pay From");
                    $('#account').focus();
                    return false;
                }
                if($.trim($('#datepicker').val()).length == 0)
                {
                    alert("Please Enter Date");
                    $('#datepicker').focus();
                    return false;
                }

                for(var no=1; no<=count; no++)
                {
                    if($.trim($('#item_name'+no).val()).length == 0)
                    {
                        alert("Please Enter Item Name");
                        $('#item_name'+no).focus();
                        return false;
                    }

                    if($.trim($('#order_item_quantity'+no).val()).length == 0)
                    {
                        alert("Please Enter Quantity");
                        $('#order_item_quantity'+no).focus();
                        return false;
                    }

                    if($.trim($('#order_item_price'+no).val()).length == 0)
                    {
                        alert("Please Enter Price");
                        $('#order_item_price'+no).focus();
                        return false;
                    }

                    if($.trim($('#item_sl'+no).val()) == 2)
                    {
                        if($.trim($('#item_sku'+no).val()).length == 0)
                        {
                            alert("Please Enter Product Serial");
                            $('#item_sku'+no).focus();
                            return false;
                        }
                    }

                }

                $('#invoice_form').submit();

            });

        });

        $("#datepicker").datepicker({
            changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'

        });
    </script>
@endsection
@section('required_css')
    <link href='{{ asset("assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css") }}'
          rel="stylesheet" type="text/css"/>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
@endsection