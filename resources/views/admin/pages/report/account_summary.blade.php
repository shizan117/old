@extends ('admin.layouts.master')
@section('title')
    Accounts Summary
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="m-t-0 header-title py-3 py-md-0">{{ $page_title }}</h4>
                    </div>
                    <div class="col-md-6">
                        <form class="form-horizontal" action="" role="form" method="get">
                            <div class="form-group row">
                                <div class="col-5 col-md-6">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="from_date" value="{{ \request('from_date')??'' }}" class="form-control datepicker" placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-7 col-md-6">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="to_date" value="{{ \request('to_date')??'' }}" class="form-control datepicker" placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                            <button type="submit"
                                                    class="btn btn-info waves-effect waves-light">Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <table class="table  table-bordered"
                       cellspacing="0" width="100%" style="font-size: 18px;">
                    {{--<thead>--}}
                    {{--<tr>--}}
                        {{--<th>Head</th>--}}
                        {{--<th>Amount</th>--}}
                    {{--</tr>--}}
                    {{--</thead>--}}

                    <tbody>
                        <tr class="text-danger">
                            <td>Total Client Due</td>
                            <td>{{ number_format($data['ac_receivable'],2) }}</td>
                        </tr>
                        <tr class="text-success">
                            <td colspan="2">Incomes:</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 50px; font-size: 16px;">Client Payment Collection</td>
                            <td style="font-size: 16px;">{{ number_format($data['total_client_payment'],2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 50px; font-size: 16px;">Service Charge</td>
                            <td style="font-size: 16px;">{{ number_format($data['total_service_charge'],2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 50px; font-size: 16px;">OTC Fee</td>
                            <td style="font-size: 16px;">{{ number_format($data['total_client_otc_charge'],2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 50px; font-size: 16px;">Reseller Recharge</td>
                            <td style="font-size: 16px;">{{ number_format($data['total_reseller_recharge'],2) }}</td>
                        </tr>
                        <tr class="text-success">
                            <td>Total Income</td>
                            <td>{{ number_format($data['total_income'],2) }}</td>
                        </tr>
                        <tr class="text-danger">
                            <td>Total Expense</td>
                            <td>{{ number_format($data['total_expense'],2) }}</td>
                        </tr>
                        <tr class="text-warning">
                            <td>Net Profit</td>
                            <td>{{ number_format($data['total_income']-$data['total_expense'],2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection

@section('custom_js')
    {{--@include('admin.layouts.print-js')--}}

    <script>
        $(document).ready(function () {
            $(".datepicker").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'
            });
        });
    </script>
@endsection

@section('required_css')
    <link href='{{ asset("assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css") }}'
          rel="stylesheet" type="text/css"/>
@endsection
@section('custom_css')
@endsection
@section('required_js')
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
@endsection
