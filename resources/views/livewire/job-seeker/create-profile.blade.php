<div class="max-w-4xl mx-auto px-6 py-8 bg-white shadow rounded-lg">
    {{-- Step Tracker --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-2">Step {{ $step }} of 5</h2>
        <progress class="w-full h-2 rounded bg-purple-200" max="5" value="{{ $step }}"></progress>
    </div>

    {{-- STEP 1: Profile Info --}}
    @if($step === 1)
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Profile Information</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input label="Name" wire:model.defer="name" />
            <x-input label="Designation" wire:model.defer="designation" />
            <x-input label="Phone" wire:model.defer="phone" />
            <x-input label="Email" wire:model.defer="email" />
            <x-input label="Country" wire:model.defer="country" />
            <x-input label="City" wire:model.defer="city" />
        </div>

        <x-textarea label="Address" wire:model.defer="address" class="mt-4" />
    @endif

    {{-- STEP 2: Experience --}}
    @if($step === 2)
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Experience</h3>

        @foreach ($experiences as $index => $exp)
            <div class="border rounded-lg p-4 mb-4 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Job Title" wire:model.defer="experiences.{{ $index }}.job_title" />
                    <x-input label="Employer" wire:model.defer="experiences.{{ $index }}.employer" />
                    <x-input label="Location" wire:model.defer="experiences.{{ $index }}.location" />
                    <x-input label="Start Date" type="date" wire:model.defer="experiences.{{ $index }}.start_date" />
                    <x-input label="End Date" type="date" wire:model.defer="experiences.{{ $index }}.end_date" />
                </div>
                <x-textarea label="Work Summary" wire:model.defer="experiences.{{ $index }}.work_summary" class="mt-3" />
                <x-button flat label="Remove" class="mt-2 text-red-600" @click="$wire.experiences.splice({{ $index }}, 1)" />
            </div>
        @endforeach

        <x-button outline wire:click="addExperience" label="Add Experience" icon="o-plus" />
    @endif

    {{-- STEP 3: Education --}}
    @if($step === 3)
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Education</h3>

        @foreach ($educations as $index => $edu)
            <div class="border rounded-lg p-4 mb-4 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="School Name" wire:model.defer="educations.{{ $index }}.school_name" />
                    <x-input label="Location" wire:model.defer="educations.{{ $index }}.location" />
                    <x-input label="Degree" wire:model.defer="educations.{{ $index }}.degree" />
                    <x-input label="Field of Study" wire:model.defer="educations.{{ $index }}.field_of_study" />
                    <x-input label="Start Date" type="date" wire:model.defer="educations.{{ $index }}.start_date" />
                    <x-input label="End Date" type="date" wire:model.defer="educations.{{ $index }}.end_date" />
                </div>
                <x-textarea label="Description" wire:model.defer="educations.{{ $index }}.description" class="mt-3" />
                <x-button flat label="Remove" class="mt-2 text-red-600" @click="$wire.educations.splice({{ $index }}, 1)" />
            </div>
        @endforeach

        <x-button outline wire:click="addEducation" label="Add Education" icon="o-plus" />
    @endif

    {{-- STEP 4: Skills + Summary --}}
    @if($step === 4)
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Skills & Summary</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input label="Add Skill" wire:model.defer="newSkill" />
            <div class="flex items-end">
                <x-button wire:click="addSkill" label="Add Skill" />
            </div>
        </div>

        @if(count($skills))
            <ul class="list-disc ml-6 mt-4 space-y-1">
                @foreach ($skills as $i => $skill)
                    <li class="flex items-center justify-between">
                        {{ $skill }}
                        <x-button flat label="Remove" icon="o-x-mark" wire:click="removeSkill({{ $i }})" class="text-red-600" />
                    </li>
                @endforeach
            </ul>
        @endif

        <x-textarea label="Professional Summary" wire:model.defer="summary" class="mt-6" />
    @endif

    {{-- STEP 5: Review --}}
    @if($step === 5)
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Review Resume</h3>

        <div class="space-y-2">
            <p><strong>Name:</strong> {{ $name }}</p>
            <p><strong>Designation:</strong> {{ $designation }}</p>
            <p><strong>Contact:</strong> {{ $email }} | {{ $phone }}</p>
            <p><strong>Location:</strong> {{ $city }}, {{ $country }}</p>
            <p><strong>Address:</strong> {{ $address }}</p>
            <p><strong>Summary:</strong> {{ $summary }}</p>
        </div>

        <div class="mt-4">
            <h4 class="font-semibold text-gray-700">Skills</h4>
            <ul class="list-disc ml-6">
                @foreach ($skills as $skill)
                    <li>{{ $skill }}</li>
                @endforeach
            </ul>
        </div>

        <div class="mt-4">
            <h4 class="font-semibold text-gray-700">Experience</h4>
            @foreach ($experiences as $exp)
                <p>• <strong>{{ $exp['job_title'] }}</strong> at {{ $exp['employer'] }}</p>
            @endforeach
        </div>

        <div class="mt-4">
            <h4 class="font-semibold text-gray-700">Education</h4>
            @foreach ($educations as $edu)
                <p>• <strong>{{ $edu['degree'] }}</strong> from {{ $edu['school_name'] }}</p>
            @endforeach
        </div>
    @endif

    {{-- Navigation Buttons --}}
    <div class="mt-8 flex justify-between">
        @if($step > 1)
            <x-button wire:click="back" label="Back" />
        @endif

        @if($step < 5)
            <x-button wire:click="next" label="Next" class="btn-primary" />
        @else
            <x-button wire:click="submit" label="Submit & Generate Resume" class="btn-success" />
        @endif
    </div>
</div>