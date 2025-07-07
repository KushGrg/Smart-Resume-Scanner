<div>
    <x-header title="Your Job Applications">
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search applications..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <x-card>
        @if($applications->count())
            <div class="grid gap-6">
                @foreach($applications as $application)
                    <x-card class="shadow-lg">
                        <div class="font-bold text-xl">{{ $application->jobPost->title }}</div>
                        <div class="text-sm text-gray-600">
                            {{ $application->jobPost->type }} • {{ $application->jobPost->location }}
                        </div>
                        <div class="mt-2 text-sm">
                            <span class="font-medium">Applied on:</span>
                            {{ $application->created_at->format('M d, Y') }}
                        </div>
                        <div class="mt-2 text-sm">
                            <span class="font-medium">Status:</span>
                            <span
                                class="badge badge-{{ $application->status === 'pending' ? 'warning' : ($application->status === 'accepted' ? 'success' : 'error') }}">
                                {{ ucfirst($application->status) }}
                            </span>
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $applications->links() }}
            </div>
        @else
            <div class="p-6 text-center text-gray-500">You haven't applied to any jobs yet.</div>
        @endif
    </x-card>
</div>