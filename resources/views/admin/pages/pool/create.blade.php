@extends ('admin.layouts.master')
@section('title')
    Add New Pool
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">
                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('pool.add.post') }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Pool Name</label>
                                        <div class="col-10 {{ $errors->has('pool_name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="pool_name" value="" placeholder="Enter Pool Name">
                                            <span class="text-danger">{{ $errors->first('pool_name') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Pool IP Range</label>
                                        <div class="col-10 {{ $errors->has('range_ip') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="range_ip" value="" placeholder="Enter Pool IP (192.168.1.2-192.168.1.254)">
                                            <span class="text-danger">{{ $errors->first('range_ip') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Server Name</label>
                                        <div class="col-10 {{ $errors->has('server_id') ? 'has-error' : '' }}">
                                            <select class="form-control" name="server_id">
                                                <option value="">Select Server</option>
                                                @foreach($servers as $server)
                                                    <option value="{{$server['id']}}">{{$server['server_name']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('server_id') }}</span>
                                        </div>
                                    </div>



                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add Pool</button>
                                            <a href="{{ route('pool.index') }}" class="btn btn-secondary">Cancel</a>
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