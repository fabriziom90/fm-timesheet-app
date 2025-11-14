<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report mensile</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 25px;
        }

        img {
            max-width: 250px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="title">
        Report mensile - {{ $month }}/{{ $year }}
    </div>

    {{-- ORE LAVORATE --}}
    <div class="section-title">Ore lavorate</div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Inizio</th>
                <th>Fine</th>
                <th>Ore</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $e)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>
                    <td>{{ $e->start_time }}</td>
                    <td>{{ $e->end_time }}</td>
                    <td>{{ number_format($e->duration_formatted, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Totale ore:</strong> {{ number_format($totalHours, 2) }}</p>
    <p><strong>Importo ore ({{ $hourlyRate }}€/h):</strong> {{ $totalHoursAmount }} €</p>

    {{-- RICEVUTE --}}
    <div class="section-title">Ricevute</div>
    @if (count($receipts) > 0)
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Importo</th>
                    <th>Immagine</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($receipts as $r)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
                        <td>{{ number_format($r->amount, 2) }} €</td>
                        <td>
                            <img src="{{ public_path('storage/' . $r->image_path) }}">
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>

        <p><strong>Totale ricevute:</strong> {{ $totalReceiptsAmount }} €</p>

        <hr>
    @else
        <h2>Nessuna ricevuta caricata questo mese</h2>
    @endif
    <h3>TOTALE COMPLESSIVO: {{ $grandTotal }} €</h3>

</body>

</html>
