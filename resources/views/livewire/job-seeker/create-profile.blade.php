<div>
    <x-card>
        {{-- <div class="max-w-4xl mx-auto px-6 py-10 bg-white shadow-lg rounded-xl"> --}}
            {{-- Step Tracker --}}
            <div class="mb-10">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Step {{ $step }} of 5</h2>
                <div class="w-full bg-purple-100 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full transition-all duration-300"
                        style="width: calc(({{ $step }} / 5) * 100%)"></div>
                </div>
            </div>

            {{-- STEP 1: Profile Info --}}
            @if($step === 1)
                <h3 class="text-xl font-semibold text-gray-700 mb-6">👤 Profile Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-input label="Name *" wire:model.defer="name" placeholder="Enter your full name" />
                    <x-input label="Designation *" wire:model.defer="designation" placeholder="Your job title" />
                    <x-input label="Phone *" wire:model.defer="phone" placeholder="Phone number" />
                    <x-input label="Email *" wire:model.defer="email" placeholder="Email address" />
                    <x-input label="Country *" wire:model.defer="country" placeholder="Country name" />
                    <x-input label="City *" wire:model.defer="city" placeholder="City name" />
                </div>
                <x-textarea label="Address *" wire:model.defer="address" class="mt-5" placeholder="Full address" />
            @endif

            {{-- STEP 2: Experience --}}
            @if($step === 2)
                <h3 class="text-xl font-semibold text-gray-700 mb-6">💼 Work Experience</h3>

                @forelse ($experiences as $index => $exp)
                    <div class=" rounded-lg p-5 mb-5 bg-gray-50 shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input label="Job Title *" wire:model.defer="experiences.{{ $index }}.job_title" />
                            <x-input label="Employer *" wire:model.defer="experiences.{{ $index }}.employer" />
                            <x-input label="Location *" wire:model.defer="experiences.{{ $index }}.location" />
                            <x-input label="Start Date *" type="date" wire:model.defer="experiences.{{ $index }}.start_date" />
                            <x-input label="End Date" type="date" wire:model.defer="experiences.{{ $index }}.end_date" />
                        </div>
                        <x-textarea label="Work Summary" wire:model.defer="experiences.{{ $index }}.work_summary"
                            class="mt-4" />
                        <x-button flat label="Remove" class="mt-3 text-red-600"
                            @click="$wire.experiences.splice({{ $index }}, 1)" />
                    </div>
                @empty
                    <p class="text-gray-500 mb-4">No experiences added yet.</p>
                @endforelse

                <x-button outline wire:click="addExperience" label="Add Experience" icon="o-plus" class="mt-2" />
            @endif

            {{-- STEP 3: Education --}}
            @if($step === 3)
                <h3 class="text-xl font-semibold text-gray-700 mb-6">🎓 Education</h3>

                @forelse ($educations as $index => $edu)
                    <div class="border rounded-lg p-5 mb-5 bg-gray-50 shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input label="School Name *" wire:model.defer="educations.{{ $index }}.school_name" />
                            <x-input label="Location *" wire:model.defer="educations.{{ $index }}.location" />
                            <x-input label="Degree *" wire:model.defer="educations.{{ $index }}.degree" />
                            <x-input label="Field of Study *" wire:model.defer="educations.{{ $index }}.field_of_study" />
                            <x-input label="Start Date *" type="date" wire:model.defer="educations.{{ $index }}.start_date" />
                            <x-input label="End Date" type="date" wire:model.defer="educations.{{ $index }}.end_date" />
                        </div>
                        <x-textarea label="Description" wire:model.defer="educations.{{ $index }}.description" class="mt-4" />
                        <x-button flat label="Remove" class="mt-3 text-red-600"
                            @click="$wire.educations.splice({{ $index }}, 1)" />
                    </div>
                @empty
                    <p class="text-gray-500 mb-4">No education records added.</p>
                @endforelse

                <x-button outline wire:click="addEducation" label="Add Education" icon="o-plus" class="mt-2" />
            @endif

            {{-- STEP 4: Skills + Summary --}}
            @if($step === 4)
                <h3 class="text-xl font-semibold text-gray-700 mb-6">🛠️ Skills & Summary</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <x-input label="Add Skill *" wire:model.defer="newSkill" />
                    <div class="flex items-end">
                        <x-button class="bg-blue-600 text-white rounded" wire:click="addSkill" label="Add Skill" />
                    </div>
                </div>

                @if(count($skills))
                    <ul class="list-disc ml-6 space-y-1 text-gray-700">
                        @foreach ($skills as $i => $skill)
                            <li class="flex items-center justify-between">
                                {{ $skill }}
                                <x-button flat label="Remove" icon="o-x-mark" wire:click="removeSkill({{ $i }})"
                                    class="text-red-600" />
                            </li>
                        @endforeach
                    </ul>
                @endif

                <x-textarea label="Professional Summary *" wire:model.defer="summary" class="mt-6"
                    placeholder="Write a brief professional summary..." />
            @endif

            {{-- STEP 5: Review --}}
            @if($step === 5)
                <h3 class="text-xl font-semibold text-gray-700 mb-6">📝 Review Resume</h3>

                <div class="bg-gray-50 p-4 rounded space-y-3 text-gray-800">
                    <p><strong>Name:</strong> {{ $name }}</p>
                    <p><strong>Designation:</strong> {{ $designation }}</p>
                    <p><strong>Contact:</strong> {{ $email }} | {{ $phone }}</p>
                    <p><strong>Location:</strong> {{ $city }}, {{ $country }}</p>
                    <p><strong>Address:</strong> {{ $address }}</p>
                    <p><strong>Summary:</strong> {{ $summary }}</p>

                    <div>
                        <h4 class="font-semibold mt-4">Skills</h4>
                        <ul class="list-disc ml-5">
                            @foreach ($skills as $skill)
                                <li>{{ $skill }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold mt-4">Experience</h4>
                        <ul class="list-disc ml-5">
                            @foreach ($experiences as $exp)
                                <li><strong>{{ $exp['job_title'] }}</strong> at {{ $exp['employer'] }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold mt-4">Education</h4>
                        <ul class="list-disc ml-5">
                            @foreach ($educations as $edu)
                                <li><strong>{{ $edu['degree'] }}</strong> from {{ $edu['school_name'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Navigation Buttons --}}
            <div class="mt-10 flex justify-between">
                @if($step > 1)
                    <x-button wire:click="back" icon="o-arrow-left" label=" Back"
                        class="btn bg-purple-700 hover:bg-puple-800 text-white rounded" />
                @endif

                @if($step < 5)
                    <x-button wire:click="next" icon="o-arrow-right" label="Next"
                        class="bg-purple-700 text-white hover:bg-purple-700 rounded" />
                @else
                    <x-button wire:click="submit" label="✅ Submit & Generate Resume"
                        class="bg-green-600 text-white hover:bg-green-700" />
                @endif
            </div>
            {{--
        </div> --}}
    </x-card>
</div>