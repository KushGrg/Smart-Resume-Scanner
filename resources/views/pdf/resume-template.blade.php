@php
    $jobSeekerInfo = $jobSeekerInfo ?? null;
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Resume - {{ $jobSeekerInfo->name ?? '' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #222;
            margin: 0;
            padding: 30px;
            font-size: 14px;
        }

        h1,
        h2 {
            margin: 0;
            text-transform: uppercase;
        }

        h1 {
            font-size: 24px;
            letter-spacing: 1px;
        }

        h2 {
            font-size: 16px;
            border-bottom: 1px solid #bbb;
            margin-bottom: 6px;
            padding-bottom: 2px;
        }

        .header,
        .contact-info {
            text-align: center;
        }

        .contact-info {
            font-size: 12px;
            margin-bottom: 20px;
        }

        .main {
            display: flex;
            gap: 30px;
        }

        .left,
        .right {
            flex: 1;
        }

        .section {
            margin-bottom: 24px;
        }

        ul {
            margin: 6px 0;
            padding-left: 18px;
        }

        .skills-columns {
            column-count: 2;
            column-gap: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $jobSeekerInfo->name ?? '' }}</h1>
        <div style="font-size: 14px; font-weight: bold; margin-bottom: 6px;">
            {{ $jobSeekerInfo->designation ?? '' }}
        </div>
    </div>
    <div class="contact-info">
        {{ $jobSeekerInfo->address ?? '' }}<br>
        {{ $jobSeekerInfo->email ?? '' }} | {{ $jobSeekerInfo->phone ?? '' }}<br>
        @if(!empty($jobSeekerInfo->city) || !empty($jobSeekerInfo->country))
            {{ $jobSeekerInfo->city ?? '' }}{{ !empty($jobSeekerInfo->city) && !empty($jobSeekerInfo->country) ? ',' : '' }}
            {{ $jobSeekerInfo->country ?? '' }}
        @endif
    </div>

    <div class="main">
        <!-- LEFT COLUMN -->
        <div class="left">
            <div class="section">
                <h2>Profile Info</h2>
                <p>{{ $summary ?? ($jobSeekerInfo->summary ?? '') }}</p>
            </div>

            <div class="section">
                <h2>Education</h2>
                @foreach ($educations ?? [] as $edu)
                    <div style="margin-bottom: 8px;">
                        <strong>{{ $edu['degree'] ?? '' }}</strong> in {{ $edu['field_of_study'] ?? '' }}<br>
                        {{ $edu['school_name'] ?? '' }}, {{ $edu['location'] ?? '' }}<br>
                        <span style="font-size: 12px; color: #555;">
                            {{ $edu['start_date'] ?? '' }} - {{ $edu['end_date'] ?? 'Present' }}
                        </span>
                        @if(!empty($edu['description']))
                            <div style="font-size: 12px;">{{ $edu['description'] ?? '' }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if(!empty($certifications))
                <div class="section">
                    <h2>Certifications</h2>
                    <ul>
                        @foreach ($certifications as $cert)
                            <li>{{ $cert }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="section">
                <h2>Skills</h2>
                <ul class="skills-columns">
                    @foreach ($skills ?? [] as $skill)
                        <li>{{ $skill }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="right">
            <div class="section">
                <h2>Work Experience</h2>
                @foreach ($experiences ?? [] as $exp)
                    <div style="margin-bottom: 12px;">
                        <strong>{{ $exp['job_title'] ?? '' }}</strong> at {{ $exp['employer'] ?? '' }}<br>
                        <span style="font-size: 12px; color: #555;">
                            {{ $exp['location'] ?? '' }} | {{ $exp['start_date'] ?? '' }} -
                            {{ $exp['end_date'] ?? 'Present' }}
                        </span>
                        @if(!empty($exp['work_summary']))
                            <ul>
                                @foreach (preg_split('/\r?\n/', $exp['work_summary']) as $line)
                                    @if(trim($line) !== '')
                                        <li>{{ $line }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>

</html>