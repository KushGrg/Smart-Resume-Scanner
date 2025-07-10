<div>
    @if($show && $resume)
        <x-modal wire:model.defer="show" maxWidth="2xl">
            <x-slot name="title">
                Resume Details - {{ $resume->name }}
            </x-slot>
            <x-slot name="content">
                <div class="mb-4">
                    <strong>Designation:</strong> {{ $resume->designation }}<br>
                    <strong>Email:</strong> {{ $resume->email }}<br>
                    <strong>Phone:</strong> {{ $resume->phone }}<br>
                    <strong>Location:</strong> {{ $resume->city }}, {{ $resume->country }}<br>
                    <strong>Address:</strong> {{ $resume->address }}<br>
                </div>
                <div class="mb-4">
                    <strong>Summary:</strong> {{ $resume->summary }}
                </div>
                {{-- TODO: Show experiences, educations, skills --}}
                <div class="flex gap-6">
                    <div class="flex-1">
                        <h4 class="font-semibold mb-2">Experience</h4>
                        @foreach($experiences as $exp)
                            <div class="mb-2">
                                <strong>{{ $exp['job_title'] }}</strong> at {{ $exp['employer'] }}<br>
                                <span class="text-xs text-gray-500">{{ $exp['location'] }} | {{ $exp['start_date'] }} -
                                    {{ $exp['end_date'] ?? 'Present' }}</span>
                                <div>{{ $exp['work_summary'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold mb-2">Education</h4>
                        @foreach($educations as $edu)
                            <div class="mb-2">
                                <strong>{{ $edu['degree'] }}</strong> in {{ $edu['field_of_study'] }}<br>
                                {{ $edu['school_name'] }}, {{ $edu['location'] }}<br>
                                <span class="text-xs text-gray-500">{{ $edu['start_date'] }} -
                                    {{ $edu['end_date'] ?? 'Present' }}</span>
                                <div>{{ $edu['description'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="font-semibold mb-2">Skills</h4>
                    <ul class="flex flex-wrap gap-2">
                        @foreach($skills as $skill)
                            <li class="bg-base-200 px-2 py-1 rounded">{{ $skill }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="mt-6 flex gap-2">
                    <x-button outline icon="o-document" label="Preview as HTML" />
                    <x-button outline icon="o-document-arrow-down" label="Preview as PDF" />
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-button wire:click="closeModal" label="Close" />
            </x-slot>
        </x-modal>
    @endif
</div>