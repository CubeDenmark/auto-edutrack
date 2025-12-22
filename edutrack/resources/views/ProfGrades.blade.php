<!DOCTYPE html>
<html>
<head>
    <title>{{ $className }} Grades</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        h3 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .info {
            text-align: center;
            margin-bottom: 20px;
        }

        .info p {
            margin: 2px 0;
            font-size: 14px;
        }

        .generated-date {
            text-align: right;
            font-size: 12px;
            color: #555;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }
    </style>
</head>
<body>
    <div class="generated-date">
        Date: {{ $dateGenerated }}
    </div>

    <h3>Grades for {{ $className }}</h3>

    <div class="info">
        <p>Term: {{ $term }}</p>
        <p>Professor: {{ $professorName }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Preliminary</th>
                <th>Midterm</th>
                <th>Final Term</th>
                <th>Final Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grades as $grade)
                <tr>
                    <td>{{ $grade->student->name }}</td>
                    <td>{{ $grade->prelim }}</td>
                    <td>{{ $grade->midterm }}</td>
                    <td>{{ $grade->final_term }}</td>
                    <td>{{ $grade->final_grade }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
