@extends('email.email')

@section('content')
    @if($prescription->status == 'canceled')
        <p>На Ваш адрес направлен отказ по проверке. Документ во вложении.</p>
    @else
        <p>На Ваш адрес направлено предписание. Документ во вложении.</p>
    @endif
@endsection
