<?php

namespace App\Livewire\Hr;

use App\Models\Hr\JobPost as JobPosts;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class JobPost extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'title', 'direction' => 'asc'];

    public int $perPage = 10;

    // Form properties
    public ?int $editing_id = null;

    public string $title = '';

    public string $description = '';

    public string $location = '';

    public string $type = 'full-time';

    public string $deadline = '';

    public string $requirement = '';

    public string $experience = '';

    public string $status = 'active';

    public array $jobTypes = [
        ['id' => 'full-time', 'name' => 'Full-Time'],
        ['id' => 'part-time', 'name' => 'Part-Time'],
        ['id' => 'remote', 'name' => 'Remote'],
    ];

    public array $statuses = [
        ['id' => 'active', 'name' => 'Active'],
        ['id' => 'inactive', 'name' => 'Inactive'],
    ];

    public function mount()
    {
        $this->authorize('view job posts');
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'title', 'label' => 'Title', 'sortable' => true],
            ['key' => 'location', 'label' => 'Location', 'sortable' => true],
            ['key' => 'type', 'label' => 'Type', 'sortable' => true],
            ['key' => 'deadline', 'label' => 'Deadline', 'sortable' => true],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true],
            ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-1 text-center', 'sortable' => false],
        ];
    }

    public function jobPosts()
    {
        return JobPosts::query()
            ->where('hid', Auth::id())
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function edit(JobPosts $jobPost): void
    {
        $this->authorize('edit job posts');

        $this->editing_id = $jobPost->id;
        $this->title = $jobPost->title;
        $this->description = $jobPost->description;
        $this->location = $jobPost->location;
        $this->type = $jobPost->type;
        $this->deadline = $jobPost->deadline;
        $this->requirement = $jobPost->requirement;
        $this->experience = $jobPost->experience;
        $this->status = $jobPost->status;

        $this->drawer = true;
    }

    public function create(): void
    {
        $this->authorize('create job posts');

        $this->editing_id = null;
        $this->title = '';
        $this->description = '';
        $this->location = '';
        $this->type = 'full-time';
        $this->deadline = '';
        $this->requirement = '';
        $this->experience = '';
        $this->status = 'active';

        $this->drawer = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'type' => 'required|string',
            'deadline' => 'nullable|date',
            'requirement' => 'nullable|string',
            'experience' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        if ($this->editing_id) {
            $this->authorize('edit job posts');

            $jobPost = JobPosts::find($this->editing_id);
            $jobPost->update($data);
            $this->success('Job post updated successfully');
        } else {
            $this->authorize('create job posts');

            JobPosts::create(array_merge($data, ['hid' => Auth::id()]));
            $this->success('Job post created successfully');
        }

        $this->drawer = false;
    }

    public function delete(JobPosts $jobPost): void
    {
        $this->authorize('delete job posts');

        $jobPost->delete();
        $this->success('Job post deleted successfully');
    }

    public function render()
    {
        return view('livewire.hr.job-post', [
            'jobPosts' => $this->jobPosts(),
            'headers' => $this->headers(),
        ]);
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return $this->render();
    }
}
