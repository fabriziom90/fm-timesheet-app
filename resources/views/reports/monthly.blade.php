<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1,
        h2,
        h3 {
            margin: 0 0 10px 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
            border: 1px solid #444;
            padding: 6px;
            text-align: left;
        }

        .total {
            margin-top: 20px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h2>Report mensile ore lavorate</h2>
    <p><strong>Utente:</strong> {{ $user->name }}</p>
    <p><strong>Mese:</strong> {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</p>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Inizio</th>
                <th>Fine</th>
                <th>Ore</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $e)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>
                    <td>{{ $e->start_time }}</td>
                    <td>{{ $e->end_time }}</td>
                    <td>{{ number_format($e->duration_hours, 2) }}</td>
                    <td>{{ $e->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">Totale ore nel mese: {{ number_format($totalHours, 2) }}</p>

</body>

</html>
