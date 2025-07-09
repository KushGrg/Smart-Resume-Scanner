@php
    $resume = $resume ?? null;
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Resume - {{ $resume->name ?? '' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        h1,
        h2,
        h3 {
            margin-bottom: 0;
        }

        .section {
            margin-bottom: 18px;
        }

        .label {
            font-weight: bold;
        }

        ul {
            margin: 0;
            padding-left: 18px;
        }
    </style>
</head>

<body>
    <h1>{{ $resume->name ?? '' }}</h1>
    <p><span class="label">Designation:</span> {{ $resume->designation ?? '' }}</p>
    <p><span class="label">Contact:</span> {{ $resume->email ?? '' }} | {{ $resume->phone ?? '' }}</p>
    <p><span class="label">Location:</span> {{ $resume->city ?? '' }}, {{ $resume->country ?? '' }}</p>
    <p><span class="label">Address:</span> {{ $resume->address ?? '' }}</p>

    <div class="section">
        <h2>Professional Summary</h2>
        <p>{{ $summary ?? ($resume->summary ?? '') }}</p>
    </div>

    <div class="section">
        <h2>Skills</h2>
        <ul>
            @foreach ($skills ?? [] as $skill)
                <li>{{ $skill }}</li>
            @endforeach
        </ul>
    </div>

    <div class="section">
        <h2>Experience</h2>
        @foreach ($experiences ?? [] as $exp)
            <div>
                <strong>{{ $exp['job_title'] ?? '' }}</strong> at {{ $exp['employer'] ?? '' }}<br>
                <span>{{ $exp['location'] ?? '' }} | {{ $exp['start_date'] ?? '' }} -
                    {{ $exp['end_date'] ?? 'Present' }}</span>
                <div>{{ $exp['work_summary'] ?? '' }}</div>
            </div>
        @endforeach
    </div>

    <div class="section">
        <h2>Education</h2>
        @foreach ($educations ?? [] as $edu)
            <div>
                <strong>{{ $edu['degree'] ?? '' }}</strong> in {{ $edu['field_of_study'] ?? '' }}<br>
                {{ $edu['school_name'] ?? '' }}, {{ $edu['location'] ?? '' }}<br>
                <span>{{ $edu['start_date'] ?? '' }} - {{ $edu['end_date'] ?? 'Present' }}</span>
                <div>{{ $edu['description'] ?? '' }}</div>
            </div>
        @endforeach
    </div>
</body>

</html>