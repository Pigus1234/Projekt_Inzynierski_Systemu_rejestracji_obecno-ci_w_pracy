<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista ewakuacyjna</title>

    <style>
        @page { margin: 12mm; }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
            color: #000;
            margin: 0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            padding: 12px 0 10px 0;
            border-bottom: 2px solid #000;
            margin-bottom: 14px;
        }

        .page-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .page-meta {
            font-size: 12px;
            margin: 0;
            text-align: right;
            white-space: nowrap;
        }

        .no-print {
            margin: 10px 0 14px 0;
            display: flex;
            gap: 10px;
        }

        .print-button {
            border: 1px solid #000;
            background: #fff;
            padding: 8px 12px;
            font-size: 12px;
            cursor: pointer;
        }

        .department-section {
            margin: 0 0 18px 0;
            break-inside: avoid;
        }

        .department-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            margin: 0 0 8px 0;
        }

        .department-name {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }

        .department-count {
            font-size: 12px;
            margin: 0;
            white-space: nowrap;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            font-size: 12px;
            font-weight: 700;
            text-align: left;
            border: 1px solid #000;
            padding: 6px 8px;
        }

        tbody td {
            font-size: 12px;
            border: 1px solid #000;
            padding: 6px 8px;
        }

        .column-ordinal {
            width: 14mm;
            text-align: right;
        }

        .column-checkbox {
            width: 22mm;
            text-align: center;
        }

        .checkbox-box {
            display: inline-block;
            width: 10mm;
            height: 10mm;
            border: 1px solid #000;
            box-sizing: border-box;
        }

        .empty-state {
            font-size: 12px;
            margin: 14px 0 0 0;
        }

        @media print {
            .no-print { display: none !important; }

            .department-section { break-inside: avoid; }

            .department-section + .department-section {
                break-before: page;
                page-break-before: always;
            }

            tr { break-inside: avoid; }
        }
    </style>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</head>
<body>
    <header class="page-header">
        <h1 class="page-title">Lista ewakuacyjna</h1>
        <p class="page-meta">Wydruk: {{ $printedAt->format('Y-m-d H:i') }}</p>
    </header>

    <div class="no-print">
        <button type="button" class="print-button" onclick="window.print()">
            Drukuj
        </button>
    </div>

    @if (empty($departments))
        <p class="empty-state">Brak obecnych pracowników do wydruku.</p>
    @else
        @foreach ($departments as $department)
            <section class="department-section">
                <div class="department-header">
                    <h2 class="department-name">{{ $department['departmentName'] }}</h2>
                    <p class="department-count">Liczba osób: {{ count($department['employees']) }}</p>
                </div>

                <table aria-label="Lista ewakuacyjna działu {{ $department['departmentName'] }}">
                    <thead>
                        <tr>
                            <th class="column-ordinal">Lp.</th>
                            <th>Imię i nazwisko</th>
                            <th class="column-checkbox">Bezpieczny</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($department['employees'] as $employee)
                            <tr>
                                <td class="column-ordinal">{{ $loop->iteration }}</td>
                                <td>{{ $employee['fullName'] }}</td>
                                <td class="column-checkbox">
                                    <span class="checkbox-box" aria-hidden="true"></span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endforeach
    @endif
</body>
</html>
