<table id="datatable"
       class="table table-sm table-bordered table-responsive-sm table-responsive-lg"
       cellspacing="0" width="100%">
    <thead>
    <tr>
        <th style="display: none" class="hidden-print"></th>
        <th>#</th>
        <th>Client Name</th>
        <th>Username</th>
        <th>Mac Address</th>
        <th>Connected IP</th>
        <th>Connected Time</th>
        <th>Service</th>
    </tr>
    </thead>

    <tbody>
    @php($i = 0)
    @foreach ($clientData as $dataClient)
        @php($i = $i+1)
        @php($client = \App\Client::where('username', $dataClient['name'])->first())
        @if($dataClient['name'] == $client['username'])
            <tr>
                <td style="display: none" class="hidden-print">{{ $dataClient['.id'] }}</td>
                <td>{{ $i }}</td>
                <td>{{ $client['client_name'] }}</td>
                <td>{{ $dataClient['name'] }}</td>
                <td>{{ $dataClient['caller-id'] }}</td>
                <td>{{ $dataClient['address'] }}</td>
                <td>{{ $dataClient['uptime'] }}</td>
                <td>{{ $dataClient['service'] }}</td>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>