<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    {{-- @php
        dd($qrCodePath);
    @endphp --}}
    {{-- @svg($qrCodePath) --}}
    {{-- <img src="assets{{ $qrCodePath }}" alt=""> --}}
    <img src="{{ asset('images/qr_codes/4IvFZXvJ7V.svg') }}" alt="">




    {{-- <img src="{{ $qrCodePath }}" alt="fg" srcset="">
    {!! $qrCodePath !!} --}}
</body>

</html>
