<table cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td colspan="11" class="center">
            @if($prescription->contractor)
                <b>{{ $prescription->contractor->title }}</b>
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="11"><br></td>
    </tr>
    <tr>
        <td colspan="11" class="center">
            <b>Предписание № </b></span><span><b>П/{{ $prescription->id }}</b>
        </td>
    </tr>
    <tr>
        <td colspan="11" class="center">
            ОБ УСТРАНЕНИИ НАРУШЕНИЙ ПРИ СТРОИТЕЛЬСТВЕ ОБЪЕКТА<span></span>
            КАПИТАЛЬНОГО СТРОИТЕЛЬСТВА.
        </td>
    </tr>
    <tr>
        <td colspan="11"><br></td>
    </tr>
    <tr>
        <td colspan="6">
            @if($prescription->building)
                {{ $prescription->building->address }}
            @endif
        </td>
        <td colspan="5" class="right">
            {{ \Carbon\Carbon::now()->isoFormat('DD "MMMM" YYYY', 'Do MMMM') }}
        </td>
    </tr>
    <tr>
        <td colspan="4" class="field-desc center">
            (место составления)
        </td>
        <td colspan="7"><br></td>
    </tr>
    <tr>
        <td colspan="11"><br></td>
    </tr>
    <tr>
        <td colspan="3">
            Выдано
        </td>
        <td colspan="8" class="center">
            @if($prescription->contractor)
                <b>{{ $prescription->contractor->title }}</b>
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="3"><br></td>
        <td colspan="8" class="field-desc center">
            (наименование лица, осуществляющего строительство)
            <br>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            в отношении объекта
        </td>
        <td colspan="8">
            {{ $prescription->building->title }}
        </td>
    </tr>
    <tr>
        <td colspan="3"><br></td>
        <td colspan="8" class="field-desc center">
            (наименование объекта капитального строительства)
            <br>
        </td>
    </tr>
    <tr>
        <td colspan="11">
            {{ $prescription->building->address }}
        </td>
    </tr>
    <tr>
        <td colspan="11" class="field-desc center">
            (указать почтовый или строительный адрес объекта капитального строительства)
        </td>
    </tr>
    <tr>
        <td colspan="11"><br></td>
    </tr>
    <tr>
        <td colspan="11">
            По результатам проверки был составлен акт, на основании которого предписываю: устранить нарушения требований СНиП, ГОСТ, РД, проектной документации допущенные при выполнении строительно-монтажных работ:
        </td>
    </tr>
    <tr>
        <td colspan="11"><br></td>
    </tr>
    <tr class="td-border center">
        <td>
            <p>№ п.п.</p>
        </td>
        <td colspan="8">
            Перечень выявленных замечания и нарушений.
        </td>
        <td colspan="2">
            Срок устранения
        </td>
    </tr>

    @foreach($prescription->itemsWithDemands as $index => $item)
        <tr class="td-border">
            <td class="center">{{ $item->id }}</td>
            <td colspan="8">
                @foreach($item->demands as $demand)
                    {{ $demand->description }}<br>{{ $demand->regulatory }}
                @endforeach
            </td>
            <td colspan="2" class="center">
                @if($item->date_elimination)
                    {{ $item->date_elimination->format('d.m.Y') }}
                @endif
            </td>
        </tr>
    @endforeach

    <tr>
        <td colspan="11"><br></td>
    </tr>

    <tr>
        <td colspan="11">
            @if ($dateMin = $prescription->itemsWithDemands()->pluck('date_elimination')->min())
                О выполнении настоящего предписания в срок до
                {{ $dateMin->format('d.m.Y г.') }} уведомить
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="11">
            <p><span></span><br></p>
        </td>
    </tr>
    <tr>
        <td colspan="11">
            <p>
                Заказчика по адресу электронной почты:
            </p>
        </td>
    </tr>
    <tr>
        <td colspan="11" class="field-desc"><br></td>
    </tr>
    <tr>
        <td colspan="3"><br></td>
        <td><br></td>
        <td colspan="4" class="center">
            @if($prescription->building)
                <b>{{ $prescription->building->responsible }}</b>
            @endif
        </td>
        <td><br></td>
        <td colspan="2"><br></td>
    </tr>
    <tr>
        <td colspan="3" class="field-desc center">(подпись)</td>
        <td><br></td>
        <td colspan="4" class="field-desc center">
            (расшифровка подписи)
        </td>
        <td><br></td>
        <td colspan="2" class="field-desc center">
            (должность)
        </td>
    </tr>
    <tr>
        <td colspan="11"><br></td>
    </tr>
    <tr>
        <td colspan="11">
            Экземпляр предписания получил:
        </td>
    </tr>
    <tr>
        <td colspan="11"><br></td>
    </tr>
    <tr>
        <td colspan="11">
            "___" _________________ 20___ г.
        </td>
    </tr>
    <tr>
        <td colspan="11"><br></td>
    </tr>
    <tr>
        <td colspan="3"><br></td>
        <td><br></td>
        <td colspan="4" class="center">
            <b>{{ $prescription->contractor_representative }}</b>
        </td>
        <td><br></td>
        <td colspan="2"><br></td>
    </tr>
    <tr>
        <td colspan="3" class="field-desc center">
            (подпись)
        </td>
        <td><br></td>
        <td colspan="4" class="field-desc center">
            (расшифровка подписи)
        </td>
        <td><br></td>
        <td colspan="2" class="field-desc center">
            (должность)
        </td>
    </tr>
    </tbody>
</table>

@if (!empty($prescription->images()))
	<div class="page-break"></div>
	<p class="center bold">Фото-приложение к предписанию</p>
	<br />
	@foreach($prescription->images() as $itemImage)
		<div style="text-align: center; margin-bottom: 0.5cm; max-height: 25cm; overflow: hidden;">
			<img src="{{ $itemImage }}" style="max-width: 100%; max-height: 100%;">
		</div>
	@endforeach
@endif

@foreach($prescription->schemaBase64Images() as $image)
    <div class="page-break"></div>
    <p>Местоположение предписания № П/{{ $prescription->id }} на плане</p>
    <div style="text-align: center; max-height: 25cm; overflow: hidden;">
        <img src="{{ $image }}" style="max-width: 100%; max-height: 100%;">
    </div>
@endforeach
