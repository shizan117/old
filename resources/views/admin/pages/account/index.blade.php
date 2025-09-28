<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
Account List
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box table-responsive">

                    <div style="margin-bottom: 10px;">
                        @if( $user->hasAnyRole('Super-Admin','Reseller') || $user->can('account_add'))
                        <a href="{{ route('account.add') }}" class="btn btn-primary">Add Account</a>
                        @endif
                        @if( $user->hasAnyRole('Super-Admin','Reseller') || $user->can('account_balance transfer'))
                        <a href="{{ route('account.transfer') }}" class="btn btn-success">Balance Transfer</a>
                        @endif
                    </div>

                    <table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Account Name</th>
                            <th>Account Type</th>
                            <th>Account Number</th>
                            <th>Account Balance</th>
                            <th class="hidden-print">Manage</th>
                        </tr>
                        </thead>

                        <tbody>
                        @php($total=0)
                        @foreach ($accounts as $account)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $account['account_name'] }}</td>
                            <td>{{ $account['account_type'] }}</td>
                            <td>{{ $account['account_number'] }}</td>
                            <td>{{ number_format($account['account_balance'],2) }}</td>
                            <td class="hidden-print">
                                @if( $user->hasAnyRole('Super-Admin','Reseller') || $user->can('account_edit'))
                                <a href="{{ route('account.edit', $account->id) }}" class="btn-edit" title="Edit"><i class="fa fa-edit"></i></a>
                                @endif
{{--                                <a href="{{ route('transaction.index').'?accountId='.$account->id }}" class="btn btn-sm btn-outline-info" title="Transaction List"><i class="fa fa-list-alt"></i></a>--}}
                                {{--<a href="#" class="btn-del"><i class="fa fa-trash-o"></i></a>--}}

                            </td>
                        </tr>
                            @php($total += $account->account_balance)
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-right"><strong>Total=</strong></td>
                                <td><strong>{{ number_format($total,2) }}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div> <!-- end row -->
        <!-- end row -->
@endsection

@section('required_css')
    <link href='{{ asset("assets/css/datatables.min.css") }}' rel="stylesheet" type="text/css"/>
@endsection
@section('custom_css')
    <style>
        .dataTable > thead > tr > th[class*=sort]:after {
            display: none;
        }

        .dataTable > thead > tr > th[class*=sort]:before {
            display: none;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
@endsection

@section('custom_js')
    @include('admin.layouts.print-js')
@endsection
