@extends('email.email')

@section('content')
    <p>Ваш новый пароль:</p>
    <p><b>{{ $password }}</b></p>
@endsection
