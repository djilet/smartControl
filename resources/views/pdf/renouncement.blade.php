@extends('pdf.pdf')

@section('content')
    <table>
        <tr>
            <td>Исх.№{{ $renouncement->id }} от {{ $renouncement->created_at->format('d.m.Y') }}г.</td>
            <td class="right">Руководителю компании</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td class="right">{{ $renouncement->prescription->contractor->title }}</td>
        </tr>
    </table>

    <p style="margin-bottom: 0.28cm;"><br/><br/></p>

    <p class="justify" style="text-indent: 1.5cm; margin-bottom: 0.28cm;">
        Сообщаем Вам, что представленные в исполнительной документации объемы
        (КС №{{ $renouncement->prescription->number_ks }}) небыли приняты в связи с не устраненными
        нарушениями при строительстве объекта, выявленными в представляемом периоде.
        Перечень нарушений представлен в приложении.
    </p>

    <table style="margin-left: 1.5cm;">
        @foreach($renouncement->items as $index => $item)
            <tr>
                @if($index == 0)
                    <td width="3cm">Приложение:</td>
                @else
                    <td width="3cm">&nbsp;</td>
                @endif
                <td>{{ $index+1 }}. Предписание №П/{{ $item->id }}</td>
            </tr>
        @endforeach
    </table>

    <table style="margin-top: 2cm;">
        <tr>
            <td>Директор</td>
            <td class="right">{{ $renouncement->prescription->building->responsible }}</td>
        </tr>
    </table>

    @foreach($renouncement->items as $index => $item)
        <div class="page-break"></div>
        <div class="right">Приложение №{{ $index+1 }}</div>
        @include('pdf.include.prescription', ['prescription' => $item])
    @endforeach

@endsection
