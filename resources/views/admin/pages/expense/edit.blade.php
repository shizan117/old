@extends ('admin.layouts.master')
@section('title')
    Edit Expense
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">


                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('expanse.edit.post', $expanse->id) }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Expanse Title <span class="text-warning">*</span></label>
                                        <div class="col-10 {{ $errors->has('expanse_name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="expanse_name" value="{{ $expanse->name }}"
                                                   placeholder="Enter Expanse Title" required>
                                            <span class="text-danger">{{ $errors->first('expanse_name') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Expanse Category <span class="text-warning">*</span></label>
                                        <div class="col-4 {{ $errors->has('expanse_category') ? 'has-error' : '' }}">
                                            <select class="form-control" name="expanse_category" required>
                                                <option value="">Select Category</option>
                                                @foreach($cats as $cat)
                                                    <option value="{{ $cat->id}}" {{ ($expanse->cat_id == $cat->id) ? 'selected':'' }}>{{$cat->name}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('expanse_category') }}</span>
                                        </div>

                                        <label class="col-2 col-form-label">Expense Date <span class="text-warning">*</span></label>
                                        <div class="input-group col-4 {{ $errors->has('expense_date') ? 'has-error' : '' }}">
                                            <input type="text" autocomplete="off" name="expense_date" class="form-control"
                                                   placeholder="yyyy-mm-dd" data-date-end-date="0d"
                                                   id="datepicker" value="{{ $expanse->date }}" required>

                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="ti-calendar"></i></span>
                                            </div>
                                            <span class="text-danger">{{ $errors->first('expense_date') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Expanse Amount <span class="text-warning">*</span></label>
                                        <div class="col-4 {{ $errors->has('amount') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="amount" value="{{ $expanse->amount }}" placeholder="Enter Expanse Amount" required>
                                            <span class="text-danger">{{ $errors->first('amount') }}</span>
                                        </div>

                                        <label class="col-2 col-form-label">Expanse's Account <span class="text-warning">*</span></label>
                                        <div class="col-4 {{ $errors->has('account') ? 'has-error' : '' }}">
                                            <select class="form-control" name="account" required>
                                                <option value="">Select Account</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id}}" {{ ($expanse->transaction->account->id == $account->id) ? 'selected':'' }}>{{$account->account_name}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('account') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Note</label>
                                        <div class="col-10 {{ $errors->has('note') ? 'has-error' : '' }}">
                                            <textarea name="note" cols="30" rows="4" class="form-control"
                                                      placeholder="Note additional information">{{ $expanse->note }}</textarea>
                                            <span class="text-danger">{{ $errors->first('note') }}</span>
                                        </div>
                                    </div>

                                   <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Update Expanse</button>
                                            <a href="{{ route('expanse.list') }}" class="btn btn-secondary">Cancel</a>
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
