@extends('admin.layouts.master')
@section('title')
    Inventory Maintain
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card-box">


                        <h4 class="header-title mt-0 m-b-30">Maintain From Stock Items</h4>

                        <div class="p-20">
                            <form class="form-horizontal" role="form" action="{{ route('inventory.stock.maintain') }}"
                                  method="POST">

                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Item Serial</label>
                                    <div class="col-9 {{ $errors->has('stock_serial') ? 'has-error' : '' }}">
                                        <select class="form-control select2" name="stock_serial" id="stock_serial">
                                            <option value="">Select Serial Number</option>
                                            @foreach($stocks as $stock)
                                                <option value="{{$stock['serial']}}">{{$stock['serial']}} ({{ $stock->product->name }})</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('stock_serial') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Item Name</label>
                                    <div class="col-9 {{ $errors->has('stock_name') ? 'has-error' : '' }}"
                                         id="stock_item">
                                        <input type="text" readonly class="form-control" name="stock_name" value=""
                                               placeholder="Item Name">
                                        <span class="text-danger">{{ $errors->first('stock_name') }}</span>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Maintain To</label>
                                    <div class="col-9 {{ $errors->has('stock_maintain_to') ? 'has-error' : '' }}">
                                        <select class="form-control" name="stock_maintain_to" id="stock_maintain">
                                            <option value="">Select Maintain To</option>
                                            <option value="Use" {{ (collect(old('stock_maintain_to'))->contains("Use")) ? 'selected':'' }}>
                                                Use
                                            </option>
                                            <option value="Sell" {{ (collect(old('stock_maintain_to'))->contains("Sell")) ? 'selected':'' }}>
                                                Sell
                                            </option>
                                            <option value="Refund" {{ (collect(old('stock_maintain_to'))->contains("Refund")) ? 'selected':'' }}>
                                                Refund
                                            </option>
                                            <option value="Loss" {{ (collect(old('stock_maintain_to'))->contains("Loss")) ? 'selected':'' }}>
                                                Loss
                                            </option>
                                        </select>
                                        <span class="text-danger">{{ $errors->first('stock_maintain_to') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Quantity</label>
                                    <div class="col-9 {{ $errors->has('stock_qty') ? 'has-error' : '' }}"
                                         id="stock_qty">
                                        <input type="text" readonly class="form-control" name="stock_qty" value=""
                                               placeholder="Enter Quantity">
                                        <span class="text-danger">{{ $errors->first('stock_qty') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row" id="stock_sell" style="display: none">
                                    <label class="col-3 col-form-label">Sell Amount</label>
                                    <div class="col-9 {{ $errors->has('stock_amount') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" name="stock_amount" value=""
                                               placeholder="Enter Sell Amount">
                                        <span class="text-danger">{{ $errors->first('stock_amount') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row" id="stock_account" style="display: none">
                                    <label class="col-3 col-form-label">Account</label>
                                    <div class="col-9 {{ $errors->has('stock_account') ? 'has-error' : '' }}">
                                        <select class="form-control" name="stock_account">
                                            <option value="">Select Account Name</option>
                                            @foreach($accounts as $account)
                                                <option value="{{$account->id}}">{{$account->account_name}}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('stock_account') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Comment</label>
                                    <div class="col-9 {{ $errors->has('stock_comment') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" name="stock_comment" value=""
                                               placeholder="Enter Comment">
                                        <span class="text-danger">{{ $errors->first('stock_comment') }}</span>
                                    </div>
                                </div>

                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-9">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Maintain
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!-- end col -->

                <div class="col-xl-6">
                    <div class="card-box">

                        <h4 class="header-title mt-0 m-b-30">Maintain From Used Items</h4>

                        <div class="p-20">
                            <form class="form-horizontal" role="form" action="{{ route('inventory.used.maintain') }}"
                                  method="POST">

                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Item Serial</label>
                                    <div class="col-9 {{ $errors->has('use_serial') ? 'has-error' : '' }}">
                                        <select class="form-control select2" name="use_serial" id="used_serial">
                                            <option value="">Select Serial Number</option>
                                            @foreach($ugases as $used)
                                                <option value="{{$used['serial']}}">{{$used['serial']}} ({{ $used->product->name }})</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('use_serial') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Item Name</label>
                                    <div class="col-9 {{ $errors->has('use_name') ? 'has-error' : '' }}" id="used_item">
                                        <input type="text" readonly class="form-control" name="use_name" value=""
                                               placeholder="Item Name">
                                    </div>
                                    <span class="justify-content-end row text-danger col-9">{{ $errors->first('use_name') }}</span>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Maintain To</label>
                                    <div class="col-9 {{ $errors->has('use_maintain_to') ? 'has-error' : '' }}">
                                        <select class="form-control" name="use_maintain_to" id="use_maintain">
                                            <option value="">Select Maintain To</option>
                                            <option value="Stock" {{ (collect(old('use_maintain_to'))->contains("Stock")) ? 'selected':'' }}>
                                                Stock
                                            </option>
                                            <option value="Sell" {{ (collect(old('use_maintain_to'))->contains("Sell")) ? 'selected':'' }}>
                                                Sell
                                            </option>
                                            <option value="Refund" {{ (collect(old('use_maintain_to'))->contains("Refund")) ? 'selected':'' }}>
                                                Refund
                                            </option>
                                            <option value="Loss" {{ (collect(old('use_maintain_to'))->contains("Loss")) ? 'selected':'' }}>
                                                Loss
                                            </option>
                                        </select>
                                        <span class="text-danger">{{ $errors->first('use_maintain_to') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Quantity</label>
                                    <div class="col-9 {{ $errors->has('use_qty') ? 'has-error' : '' }}" id="use_qty">
                                        <input type="text" readonly class="form-control" name="use_qty" value=""
                                               placeholder="Enter Quantity">
                                        <span class="text-danger">{{ $errors->first('use_qty') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row" id="use_sell" style="display: none">
                                    <label class="col-3 col-form-label">Sell Amount</label>
                                    <div class="col-9 {{ $errors->has('use_amount') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" name="use_amount" value=""
                                               placeholder="Enter Sell Amount">
                                        <span class="text-danger">{{ $errors->first('use_amount') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row" id="use_account" style="display: none">
                                    <label class="col-3 col-form-label">Account</label>
                                    <div class="col-9 {{ $errors->has('use_account') ? 'has-error' : '' }}">
                                        <select class="form-control" name="use_account">
                                            <option value="">Select Account Name</option>
                                            @foreach($accounts as $account)
                                                <option value="{{$account->id}}">{{$account->account_name}}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('use_account') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Comment</label>
                                    <div class="col-9 {{ $errors->has('use_comment') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" name="use_comment" value=""
                                               placeholder="Enter Comment">
                                        <span class="text-danger">{{ $errors->first('use_comment') }}</span>
                                    </div>
                                </div>

                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-9">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Maintain
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!-- end col -->
            </div>
        </div><!-- end col -->
    </div>
    <!-- end row -->



@endsection
@section('custom_js')
    <script>
        $(document).ready(function () {
            // Select2
            $(".select2").select2();

        });
        $("#stock_maintain").change(function () {
            var stock_to = $(this).val();
            if (stock_to == 'Sell') {
                $('#stock_sell').show();
                $('#stock_account').show();
            } else {
                $('#stock_sell').hide();
                if (stock_to == 'Refund') {
                    $('#stock_account').show();
                } else {
                    $('#stock_account').hide();
                }
            }
        });

        $("#use_maintain").change(function () {
            var use__to = $(this).val();
            if (use__to == 'Sell') {
                $('#use_sell').show();
                $('#use_account').show();
            } else {
                $('#use_sell').hide();
                if (use__to == 'Refund') {
                    $('#use_account').show();
                } else {
                    $('#use_account').hide();
                }
            }
        });
        $("#stock_serial").change(function () {
            var stock_serial = $(this).val();
            if (stock_serial != '') {
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: "{{ route('stock.maintain.product.select') }}",
                    data: {serial: stock_serial, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        $('#stock_item').empty();
                        $('#stock_item').append('<input type="text" readonly class="form-control" name="stock_name" value="' + data.product.name + '" placeholder="Item Name">');
                        if (data.qty > 1) {
                            $('#stock_qty').empty();
                            $('#stock_qty').append('<input type="text" class="form-control" name="stock_qty" value="" placeholder="Enter Quantity">');
                        } else {
                            $('#stock_qty').empty();
                            $('#stock_qty').append('<input type="text" readonly class="form-control" name="stock_qty" value="1" placeholder="Enter Quantity">');
                        }
                    }
                });
            } else {
                $('#stock_item').empty();
                $('#stock_item').append('<input type="text" readonly class="form-control" name="stock_name" value="" placeholder="Item Name">');
                $('#stock_qty').empty();
                $('#stock_qty').append('<input type="text" readonly class="form-control" name="stock_qty" value="" placeholder="Enter Quantity">');
            }
        });
        $("#used_serial").change(function () {
            var use_serial = $(this).val();
            if (use_serial != '') {
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: "{{ route('use.maintain.product.select') }}",
                    data: {serial: use_serial, _token: '{{ csrf_token() }}'},
                    success: function (data) {
                        $('#used_item').empty();
                        $('#used_item').append('<input type="text" readonly class="form-control" name="use_name" value="' + data.product.name + '" placeholder="Item Name">');
                        if (data.qty > 1) {
                            $('#use_qty').empty();
                            $('#use_qty').append('<input type="text" class="form-control" name="use_qty" value="" placeholder="Enter Quantity">');
                        } else {
                            $('#use_qty').empty();
                            $('#use_qty').append('<input type="text" readonly class="form-control" name="use_qty" value="1" placeholder="Enter Quantity">');
                        }
                    }
                });
            } else {
                $('#used_item').empty();
                $('#used_item').append('<input type="text" readonly class="form-control" name="use_name" value="" placeholder="Item Name">');
                $('#use_qty').empty();
                $('#use_qty').append('<input type="text" readonly class="form-control" name="use_qty" value="" placeholder="Enter Quantity">');
            }
        });

    </script>
@endsection