@extends('email.email')

@section('content')
    <p>Ваш логин: <b>{{ $user->email }}</b></p>
    <p>Ваш пароль: <b>{{ $password }}</b></p>
@endsection
