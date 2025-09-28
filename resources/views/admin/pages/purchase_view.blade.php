@extends ('admin.layouts.master')
@section('title')
    View Purchase
@endsection
@section('custom_css')
    <style  media="print">


        @media print
        {
            @page
            {
                margin: 30px 12px;
            }
        }

    </style>
@endsection

@section('content')
        <div class="row">
            <div class="col-md-12">
                <div class="card-box">

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-12">
                            <div class="pull-left row">
                                @if(file_exists("assets/images/".$setting['logo']))
                                    <img src="{{ asset("assets/images/".$setting['logo']) }}" width="70px" height="70px"><span><span
                                                class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>
                                @else
                                    <img src="{{ asset('assets/images/default-logo.png') }}" width="70px" height="70px"><span><span
                                                class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>
                                @endif

                            </div>
                            <div class="pull-right text-right">
                                <h4>Purchase #<br>
                                    <strong>PRNO-{{sprintf('%06d',$pr->id)}}</strong>
                                </h4>
                            </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 m-b-0">

                                <div class="pull-left m-t-20 table-borderless table-sm">

                                    <strong>{!! $setting['address'] !!}</strong><br>
                                    Phone: <strong>{{ $setting['phone'] }}</strong><br>
                                </div>
                                <div class="pull-right m-t-20 text-right">
                                    <p>Payment Date: <strong>{{ date('d-M-Y', strtotime($pr->purchase_date)) }}</strong></p>
                                    Entry By: <strong>{{ $pr->user->name }}</strong><br>
                                </div>
                            </div><!-- end col -->
                        </div>

                        <h4 class="text-center">Purchase Details</h4>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table m-t-0 table-sm table-bordered">
                                        <thead>
                                        <tr class="text-center"><th>#</th>
                                            <th>Item Name</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Total Price</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($i = 0)
                                        @foreach ($purchases as $purchase)
                                            @php($i = $i+1)
                                        <tr>
                                            <td class="text-center">{{ $i }}</td>
                                            <td class="text-center">{{ $purchase->product->name }}</td>
                                            <td class="text-center">{{ $purchase->qty }}</td>
                                            <td class="text-right">{{ $purchase->price }} {{ $setting['currencyCode'] }}</td>
                                            <td class="text-right">{{ $purchase->total_price }} {{ $setting['currencyCode'] }}</td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-5 col-6">
                                <div class="clearfix">

                                </div>
                            </div>
                            <div class="col-xl-3 col-6 offset-xl-4">
                                <div class="table-borderless table-sm">
                                    <table class="table">
                                        <tr>
                                            <td class="text-right"><b>Total:</b></td>
                                            <td class="text-right">{{ $pr->price }} {{ $setting['currencyCode'] }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row m-t-30">
                            <div class="col-xl-6 col-6 m-t-5">
                                _________________________________ <br>
                                <strong>Signature Of Entry Person</strong>

                            </div>

                            <div class="col-xl-3 col-6 offset-xl-3 text-right m-t-5">
                                __________________________________ <br>
                                <strong>Authorized Signature & Co. Stamp</strong> <br>
                            </div>
                        </div>
                        <hr>
                        <div class="d-print-none">
                            <div class="pull-right">
                                <a href="javascript:window.print()" class="btn btn-light waves-effect waves-light"><i class="fa fa-print"></i></a>
                                <a href="{{ route('purchases') }}" class="btn btn-primary waves-effect waves-light">Back</a>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div> <!-- end row -->

                </div> <!-- end card-box -->
            </div><!-- end col -->
        </div>
        <!-- end row -->
@endsection
