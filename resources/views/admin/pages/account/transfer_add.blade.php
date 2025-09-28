@extends ('admin.layouts.master')
@section('title')
    Transfer Balance
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">

                    <div class="row">
                        <div class="col-12">
                            <div class="p-2">
                                <form class="form-horizontal" role="form" action="{{ route('account.transfer.post') }}" method="POST">

                                    {{ csrf_field() }}

                                    <div class="form-group row">
                                        <label class="col-4 col-md-2 col-form-label">Transfer From*</label>
                                        <div class="col-8 col-md-10 {{ $errors->has('transfer_from') ? 'has-error' : '' }}">
                                            <select class="form-control" name="transfer_from" required>
                                                <option value="">Select Account</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{$account['id']}}" {{ (collect(old('transfer_from'))->contains($account['id'])) ? 'selected':'' }}>{{$account['account_name']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('transfer_from') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-4 col-md-2 col-form-label">Transfer To*</label>
                                        <div class="col-8 col-md10 {{ $errors->has('transfer_to') ? 'has-error' : '' }}">
                                            <select class="form-control" name="transfer_to" required>
                                                <option value="">Select Account</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{$account['id']}}" {{ (collect(old('transfer_to'))->contains($account['id'])) ? 'selected':'' }}>{{$account['account_name']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('transfer_to') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-4 col-md-2 col-form-label">Transfer Amount*</label>
                                        <div class="col-8 col-md-10 {{ $errors->has('amount') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="amount" value="{{ old('amount') }}" placeholder="Enter Transfer Amount" required>
                                            <span class="text-danger">{{ $errors->first('amount') }}</span>
                                        </div>
                                    </div>

                                    {{--<div class="form-group row">--}}
                                        {{--<label class="col-2 col-form-label">Transfer Date*</label>--}}

                                        {{--<div class="input-group col-10 {{ $errors->has('transfer_date') ? 'has-error' : '' }}">--}}
                                            {{--<input type="text" autocomplete="off" name="transfer_date" class="form-control"--}}
                                                   {{--placeholder="yyyy-mm-dd"--}}
                                                   {{--id="datepicker" value="{{ old('transfer_date')??date('Y-m-d') }}" required>--}}

                                            {{--<div class="input-group-append">--}}
                                                {{--<span class="input-group-text"><i class="ti-calendar"></i></span>--}}
                                            {{--</div>--}}
                                            {{--<span class="text-danger">{{ $errors->first('transfer_date') }}</span>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}

                                   <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light my-1">Transfer Balance</button>
                                            <a href="{{ route('account.index') }}" class="btn btn-secondary my-1">Cancel</a>
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
    <script type="text/javascript">

        $(document).ready(function () {
            $("#datepicker").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd',
            });
        });
    </script>
@endsection
@section('required_css')
    <link href='{{ asset("assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css") }}'
          rel="stylesheet" type="text/css"/>
@endsection
@section('required_js')
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
@endsection