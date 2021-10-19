@extends('email.email')

@section('content')
    <p>
        Пользователь <b>{{ $user->first_name }} {{ $user->last_name}}</b> 
        изменил список файлов у объекта <b>{{ $building->title }}</b>, 
        находящегося по адресу <b>{{ $building->address }}</b>
    </p>

    @if($deletedFiles)
        <div style="margin-top:15px;">
            <p>Удаленные файлы:</p>
            <ul>
                @foreach($deletedFiles as $file)
                    <li>{{ $file->user_filename }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if($addedFiles)
        <div style="margin-top:15px;">
            <p>Добавленные файлы:</p>
            <ul>
                @foreach($addedFiles as $file)
                    <li>{{ $file->user_filename }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection