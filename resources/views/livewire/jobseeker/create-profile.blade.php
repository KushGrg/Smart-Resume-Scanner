<div>
    <div class="max-w-4xl mx-auto p-6 bg-white shadow rounded">
        <h2 class="text-xl font-bold mb-4">Step {{ $step }} of 5</h2>
        <progress class="w-full mb-6" max="5" value="{{ $step }}"></progress>

        {{-- STEP 1: Profile Info --}}
        @if($step === 1)
            <h3 class="text-lg font-semibold mb-3">Profile Information</h3>

            <div class="grid grid-cols-2 gap-4">
                <x-input label="Name" wire:model.defer="name" />
                {{-- <x-input label="Last Name" wire:model.defer="last_name" /> --}}
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
            <h3 class="text-lg font-semibold mb-3">Experience</h3>

            @foreach ($experiences as $index => $exp)
                <div class="border rounded p-3 mb-3">
                    <div class="grid grid-cols-2 gap-4">
                        <x-input label="Job Title" wire:model.defer="experiences.{{ $index }}.job_title" />
                        <x-input label="Employer" wire:model.defer="experiences.{{ $index }}.employer" />
                        <x-input label="Location" wire:model.defer="experiences.{{ $index }}.location" />
                        <x-input label="Start Date" wire:model.defer="experiences.{{ $index }}.start_date" type="date" />
                        <x-input label="End Date" wire:model.defer="experiences.{{ $index }}.end_date" type="date" />
                        <div class="flex items-end">
                            <x-button flat icon="" @click="$wire.experiences.splice({{ $index }}, 1)" label="Remove" />
                        </div>
                    </div>
                    <x-textarea label="Work Summary" wire:model.defer="experiences.{{ $index }}.work_summary" class="mt-2" />
                </div>
            @endforeach

            <x-button outline wire:click="addExperience" label="Add Two Experience Fields" icon="" />
        @endif

        {{-- STEP 3: Education --}}
        @if($step === 3)
            <h3 class="text-lg font-semibold mb-3">Education</h3>

            @foreach ($educations as $index => $edu)
                <div class="border rounded p-3 mb-3">
                    <div class="grid grid-cols-2 gap-4">
                        <x-input label="School Name" wire:model.defer="educations.{{ $index }}.school_name" />
                        <x-input label="Location" wire:model.defer="educations.{{ $index }}.location" />
                        <x-input label="Degree" wire:model.defer="educations.{{ $index }}.degree" />
                        <x-input label="Field of Study" wire:model.defer="educations.{{ $index }}.field_of_study" />
                        <x-input label="Start Date" wire:model.defer="educations.{{ $index }}.start_date" type="date" />
                        <x-input label="End Date" wire:model.defer="educations.{{ $index }}.end_date" type="date" />
                    </div>
                    <x-textarea label="Description" wire:model.defer="educations.{{ $index }}.description" class="mt-2" />
                    <x-button flat icon="" @click="$wire.educations.splice({{ $index }}, 1)" label="Remove" />
                </div>
            @endforeach

            <x-button outline wire:click="$push('educations', [])" label="Add Education" icon="" />
        @endif

        {{-- STEP 4: Skills + Summary --}}
        @if($step === 4)
            <h3 class="text-lg font-semibold mb-3">Skills & Summary</h3>

            <div class="grid grid-cols-2 gap-4">
                <x-input label="Add Skill" wire:model.defer="newSkill" />
                <div class="flex items-end">
                    <x-button wire:click="addSkill" label="Add Skill" />
                </div>
            </div>

            <ul class="list-disc ml-6 mt-2">
                @foreach ($skills as $i => $skill)
                    <li>{{ $skill }}
                        <x-button flat icon="" @click="$wire.skills.splice({{ $i }}, 1)" />
                    </li>
                @endforeach
            </ul>

            <x-textarea label="Professional Summary" wire:model.defer="summary" class="mt-4" />
        @endif

        {{-- STEP 5: Review --}}
        @if($step === 5)
            <h3 class="text-lg font-semibold mb-3">Review Resume</h3>

            <p><strong>Name:</strong> {{ $name }} </p>
            <p><strong>Designation:</strong> {{ $designation }}</p>
            <p><strong>Contact:</strong> {{ $email }} | {{ $phone }}</p>
            <p><strong>Location:</strong> {{ $city }}, {{ $country }}</p>
            <p><strong>Address:</strong> {{ $address }}</p>
            <p><strong>Summary:</strong> {{ $summary }}</p>

            <h4 class="mt-4 font-semibold">Skills</h4>
            <ul class="list-disc ml-6">
                @foreach ($skills as $skill)
                    <li>{{ $skill }}</li>
                @endforeach
            </ul>

            <h4 class="mt-4 font-semibold">Experience</h4>
            @foreach ($experiences as $exp)
                <p>• <strong>{{ $exp['job_title'] }}</strong> at {{ $exp['employer'] }}</p>
            @endforeach

            <h4 class="mt-4 font-semibold">Education</h4>
            @foreach ($educations as $edu)
                <p>• <strong>{{ $edu['degree'] }}</strong> from {{ $edu['school_name'] }}</p>
            @endforeach
        @endif

        {{-- Navigation Buttons --}}
        <div class="mt-6 flex justify-between">
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
</div>