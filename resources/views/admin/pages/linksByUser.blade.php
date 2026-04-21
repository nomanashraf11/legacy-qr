@extends('admin.layout.master')
@section('title', 'Living Leagacy | QR CODES')

@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">QR CODES</h4>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <table id="selection-datatable"
                                class="table table-striped dt-responsive nowrap w-100 align-middle">
                                <thead>
                                    <tr>
                                        <th>Link</th>
                                        <th>Name</th>
                                        <th>Url</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($qrCodes as $qrCode)
                                        <tr>
                                            <td>{{ $qrCode->uuid }}</td>
                                            <td>{{ $qrCode->profile->name ?? '------' }}</td>
                                            <td><a href="{{ config('app.client_url') . '/' . $qrCode->uuid }}" target="_blank">
                                                    {{ config('app.client_url') . '/' . $qrCode->uuid }}</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $qrCodes->links() }}
                        </div>
                    </div>
                </div>
            </div>
            {{-- @foreach ($qrCodes as $code)
                <a href="{{ route('admin.qr.view', $code->uuid) }}">{{ $code->uuid }}</a>
            @endforeach --}}
        </div>
    </div>
@endsection
