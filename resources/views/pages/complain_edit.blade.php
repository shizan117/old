@extends ('layouts.master')
@section('title','Edit Complain')

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">


                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('client.complain.update',$complain->id) }}" method="POST">
                                    {{ csrf_field() }}
                                    {{--<input type="hidden" name="{{$complain->id}}">--}}
                                    <div class="form-group row">

                                        @php($c_titles=['Line Slow','Router Disconnect','Router Signal Red','Router Signal Yellow','Connection Disconnect',
                                        'Computer Red Signal','Computer Yellow Signal','Re-Connection Request','House/Flat Shifting','Disconnect Request',
                                        'Package Change','Others'])

                                        <div class="col-6 {{ $errors->has('title') ? 'has-error' : '' }}">
                                        <label class="col-form-label">Complain Title</label>
                                            <select class="form-control" name="title" id="title">
                                                <option value="">Select Complain Title</option>
                                                @foreach($c_titles as $title)
                                                    <option value="{{$title}}"  {{ $complain->title==$title?'selected':'' }}>{{$title}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('title') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 {{ $errors->has('title') ? 'has-error' : '' }}">
                                            <label class="col-form-label">Description</label>
                                            <textarea class="form-control" name="description">{{ $complain->description }}</textarea>
                                            <span class="text-danger">{{ $errors->first('description') }}</span>
                                        </div>
                                    </div>

                                   <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Update Complain</button>
                                            <a href="{{ route('client.complain.index') }}" class="btn btn-secondary">Cancel</a>
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
    <script>

        $(document).ready(function () {
            // Select2
            $(".select2").select2();

        });

    </script>
@endsection