<?php $user = Auth::user(); ?>
@extends('admin.layouts.master')
@section('title')
    Help
@endsection



@section('content')
    <style>
        .boxShadoInLinkBox {
            box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
            border: 1px solid #cccccc38;
            border-radius: 5px;
        }

        .urlInputForm {}
    </style>

    <div class="row">

        <div class="col-md-12">
            <div class="rsponseBox">

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>There were some errors with your submission:</strong>
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-5">
            <div class="leftPart">

                <div class="urlInputForm boxShadoInLinkBox p-3">
                    <form action="{{ route('deelko.support.store') }}" method="POST">
                        @csrf
                        <h4 class="innerSmlTitle">Create Link:</h4>
                        <div class="singleInput py-2">
                            <label for="url_endpoint">URL End Point</label>
                            <input type="text" class="form-control" name="url_endpoint" id="url_endpoint"
                                placeholder="URL End-Point, EX: 'all-clients'">
                        </div>
                        <div class="singleInput py-2">
                            <label for="youTube_share_link">YouTube Share Link</label>
                            <input type="text" class="form-control" name="youTube_share_link" id="youTube_share_link"
                                placeholder="YouTube Share Link'">
                        </div>
                        <div class="singleInput py-2">
                            <input type="submit" value="Upload" class="btn btn-primary">
                        </div>

                    </form>
                </div>

                <div class="urlInputForm boxShadoInLinkBox p-3 mt-3">
                    <form action="{{ route('deelko.support.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <h4 class="innerSmlTitle">Update Link:</h4>
                        <div class="singleInput py-2">
                            <label for="select_end_point">Select End Point</label>
                            <select class="form-control" id="select_end_point" name="select_end_point">
                                <option disabled selected>Select a End-Point</option>
                                @foreach ($all_youTube_links as $link)
                                    <option value="{{$link->id}}">{{ $link->url_endpoint }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="singleInput py-2">
                            <label for="update_youtube_link">YouTube Share Link</label>
                            <input type="text" class="form-control" name="update_youtube_link" id="update_youtube_link"
                                placeholder="YouTube Share Link'">
                        </div>
                        <div class="singleInput py-2">
                            <input type="submit" value="Updated Link" class="btn btn-primary">
                        </div>

                    </form>
                </div>

            </div>
        </div>
        <div class="col-md-7">
            <div class="all_youTube_links_table boxShadoInLinkBox">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SL.</th>
                            <th>End-Point</th>
                            <th>YouTube Link</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($all_youTube_links->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center">No Data to show!</td>
                        </tr>
                    @else
                        @foreach ($all_youTube_links as $link)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $link->url_endpoint }}</td>
                                <td>{{ $link->youTube_share_link }}</td>
                                <td class="text-center">
                                    <form action="{{ route('deelko.support.distroy') }}" method="POST" id="deleteLinkForm-{{ $link->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="delete_link_id" value="{{ $link->id }}">
                                        <input type="submit" value="Delete" class="btn btn-danger btn-sml">
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom_js')
<script>
    document.getElementById('deleteLinkForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the form from submitting immediately
        let confirmAction = confirm("Are you sure you want to delete this link?");
        if (confirmAction) {
            this.submit(); // Submit the form if the user confirms
        }
    });
</script>
@endsection


@section('required_css')
    <link href='{{ asset('assets/css/datatables.min.css') }}' rel="stylesheet" type="text/css" />
@endsection
@section('custom_css')
    <style>
        .dataTable>thead>tr>th[class*=sort]:after {
            display: none;
        }

        .dataTable>thead>tr>th[class*=sort]:before {
            display: none;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
@endsection

