<!DOCTYPE html>
<html>
<head>
    <title>Student Grades Report - {{ $selectedTerm }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .subject { font-weight: bold; margin-top: 30px; font-size: 18px; }
        .info { margin-top: 20px; font-size: 16px; }
        .gwa { text-align: right; font-size: 16px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>

    <h1>Grade Report</h1>
    <h2>Term: {{ $selectedTerm }}</h2>

    @if($student)
        <p class="info"><strong>Student:</strong> {{ $student->name }}</p>
    @endif

    {{-- ✅ GWA Display --}}
    @isset($gwa)
        <p class="gwa">General Weighted Average (GWA): {{ number_format($gwa, 2) }}</p>
    @endisset

    @foreach($gradesByClass as $subject => $grades)
        <div class="subject">{{ $subject }}</div>
        <table>
            <thead>
                <tr>
                    <th>Professor</th>
                    <th>Prelim</th>
                    <th>Midterm</th>
                    <th>Final Term</th>
                    <th>Final Grade</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grades as $grade)
                    <tr>
                        <td>{{ $grade->studentClass->class->professor->name ?? 'N/A' }}</td>
                        <td>{{ $grade->prelim }}</td>
                        <td>{{ $grade->midterm }}</td>
                        <td>{{ $grade->final_term }}</td>
                        <td>{{ $grade->final_grade }}</td>
                        <td>{{ $grade->final_remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <p style="text-align:right; font-size: 12px; margin-top: 40px;">
        Generated on {{ \Carbon\Carbon::now()->format('F j, Y, g:i A') }}
    </p>

</body>
</html>
