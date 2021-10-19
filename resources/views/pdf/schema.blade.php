@extends('pdf.pdf')

@section('content')
    <table cellspacing="0" cellpadding="0">
        <tr class="td-border center">
            <td>№ п.п.</td>
            <td style="width: 65%">Перечень выявленных замечания и нарушений.</td>
            <td>Срок устранения</td>
        </tr>
        @foreach($prescription->itemsWithDemands as $index => $item)
            <tr class="td-border">
                <td class="center">{{ $item->id }}</td>
                <td style="width: 65%">
                    @foreach($item->demands as $demand)
                        {{ $demand->description }}<br>{{ $demand->regulatory }}
                    @endforeach
                </td>
                <td class="center">
                    @if($item->date_elimination)
                        {{ $item->date_elimination->format('d.m.Y') }}
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    @foreach($prescription->schemaBase64Images() as $image)
        <div class="page-break"></div>
        <p class="center bold">Фото-приложение к предписанию</p>
        <p>Местоположение предписания № П/{{ $prescription->id }} на плане</p>
        <div style="text-align: center; max-height: 25cm; overflow: hidden;">
        	<img src="{{ $image }}" style="max-width: 100%; max-height: 100%;">
    	</div>
    @endforeach
@endsection
