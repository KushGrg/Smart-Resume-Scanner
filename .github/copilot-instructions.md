# Smart Resume Scanner - AI Coding Agent Instructions

## Architecture Overview
This is a **Smart Resume Scanner** built with Laravel + Livewire that automates resume ranking for recruitment using TF-IDF algorithm and cosine similarity. The system supports four user roles (job_seeker, hr, admin, super_admin) with permission-based workflows using Spatie Laravel Permission.

**Core Purpose:** Automate resume screening by analyzing and ranking resumes based on relevance to job descriptions using PHP-implemented NLP techniques, eliminating manual resume screening and improving hiring workflow efficiency.

**Key Technologies:**
- Laravel Framework (with Eloquent ORM, middleware, authentication)
- Laravel Volt (single-file Livewire components)
- Livewire (reactive components with real-time updates)
- Mary UI (Tailwind CSS + DaisyUI components)
- Spatie Laravel Permission (role/permission system)
- MySQL with role-specific table relationships
- PHP (custom NLP processing, TF-IDF, cosine similarity algorithms)

## Development Patterns

### Component Architecture
- **Laravel Volt**: Use for simple reactive components (`resources/views/livewire/*.blade.php`)
- **Class-based Livewire**: Use for complex logic (`app/Livewire/[Role]/*.php`)
- **Mary UI components**: Always use for forms, tables, modals (see `JobPost.php` for examples)

```php
// Class-based Livewire pattern
class JobPost extends Component {
    use Toast, WithPagination;  // Always include Mary Toast trait
    
    public bool $drawer = false;  // For Mary UI modals/drawers
    public array $sortBy = ['column' => 'title', 'direction' => 'asc'];  // For sortable tables
}
```

### Role-Based Structure
- Organize by user roles: `app/Livewire/Hr/`, `app/Livewire/Jobseeker/`
- Models follow role pattern: `app/Models/Hr/`, `app/Models/Job_seeker/`
- Each role has specific permissions checked with `$this->authorize()` in components
- **Four main roles:** Job Seeker (upload resumes, apply for jobs), HR (post jobs, view ranked candidates), Admin (manage users, control job visibility), Super Admin (full system control, permission management)

### Database Relationships
- User model has role-specific relationships: `hrDetail()`, `jobSeekerDetail()`
- Foreign keys use abbreviations: `hid` (HR ID), `jsid` (Job Seeker ID), `jpostid` (Job Post ID)
- Resume ranking data flows: User → JobSeekerDetail → Resume → JobPost
- **Critical tables:** users, job_posts, resumes, job_seeker_details, hr_details
- Resume scoring stored in resume records with calculated similarity scores

### Permission System
- Check permissions in component `mount()` methods: `$this->authorize('view job posts')`
- Route middleware: `->middleware('permission:view job posts')`
- Role hierarchy: admin (all permissions) → hr (job management) → job_seeker (apply jobs)

## Development Workflow

### Running the Application
```bash
composer dev  # Runs server, queue, logs, and Vite concurrently
```
This single command starts Laravel server, queue worker, log monitoring (Pail), and Vite dev server.

### Key Commands
```bash
php artisan migrate --seed    # Set up database with roles/permissions
php artisan route:list        # View all routes including Volt routes  
php artisan permission:cache-reset  # Reset permission cache after changes
```

### Mary UI Component Patterns
- Always use `use Toast;` trait for notifications
- Form validation with live feedback: `#[Rule('required|string|max:255')]`
- Tables with sorting/pagination: see `JobPost::headers()` method
- Drawers for create/edit forms: `public bool $drawer = false;`

### File Upload Handling
- Use `WithFileUploads` trait
- Validate file types: `'resume' => 'mimes:pdf,doc,docx|max:2048'`
- Store in public disk: `$this->resume->store('resumes', 'public')`
- Resume text extraction for NLP processing (PDF/DOC parsing required)

### Resume Ranking Algorithm (Core Feature)
The system implements **TF-IDF with Cosine Similarity** in PHP for automated resume ranking:

```php
// Core ranking workflow
class ResumeRanker {
    public function calculateSimilarity($resumeText, $jobDescription) {
        $documents = [$resumeText, $jobDescription];
        $tfidfVectors = $this->calculateTFIDF($documents);
        return $this->cosineSimilarity($tfidfVectors[0], $tfidfVectors[1]);
    }
    
    // Custom NLP preprocessing: tokenization, stopword removal, stemming
    private function preprocessText($text) {
        // Convert to lowercase, remove punctuation, filter stopwords
        // Basic stemming for common suffixes
    }
}
```

**Implementation Pattern:**
- Create service classes in `app/Services/ResumeRanker.php`
- Store ranking scores in resume records
- Use Laravel queues for background processing of large resume batches
- Cosine similarity range: 0-1 (higher = better match)

### Testing Role-Based Features
- Default users (from seeder): admin@example.com, user@example.com (password: 'password')
- Test permission boundaries by switching roles
- Verify drawer/modal behavior with Mary UI components
- Test resume ranking accuracy with sample job descriptions
- Validate file upload limits and supported formats (PDF, DOC, DOCX)

## Algorithm Implementation Details

### TF-IDF Cosine Similarity Formula
```
Cosine Similarity (A, B) = (A · B) / (||A|| × ||B||)
```

**Example Calculation:**
- Job Vector: [2, 3, 0, 1, 0]  
- Resume Vector: [1, 2, 0, 1, 1]
- Dot Product: 2×1 + 3×2 + 0×0 + 1×1 + 0×1 = 9
- Similarity: 9 / (√14 × √7) ≈ 0.91 (strong match)

### PHP NLP Processing Pipeline
1. **Text Preprocessing:** Lowercase, punctuation removal, tokenization
2. **Stopword Filtering:** Remove common words ('the', 'and', 'or', etc.)
3. **Basic Stemming:** Remove suffixes ('ing', 'ed', 'er', 'est', 'ly')
4. **TF-IDF Calculation:** Term frequency × Inverse document frequency
5. **Vector Comparison:** Cosine similarity between resume and job vectors

## Critical Files to Understand
- `database/seeders/RolesAndPermissionsSeeder.php` - Permission structure
- `app/Models/User.php` - Role relationships
- `routes/web.php` - Volt routing patterns
- `app/Livewire/Hr/JobPost.php` - Complex Livewire component example
- `database/migrations/*` - Table relationships and foreign key patterns
- `app/Services/ResumeRanker.php` - Core NLP ranking algorithm (to be implemented)

## System Constraints & Scope
- **Language Support:** Currently English only
- **File Formats:** PDF, DOC, DOCX (max 2MB)
- **Algorithm:** Basic TF-IDF + Cosine Similarity (no deep learning yet)
- **Architecture:** Monolithic Laravel app (microservices planned for future)
- **Scalability:** Designed for SME recruitment, enterprise optimization planned

## Resume Processing (Implementation Priority)
When implementing the core ranking feature:
1. Create `app/Services/ResumeRanker.php` with TF-IDF algorithm
2. Add resume content extraction from uploaded files
3. Store calculated similarity scores in `resumes.score` column
4. Use Laravel queues for background processing: `php artisan queue:work`
5. Display ranked results in HR dashboard using Mary UI tables

Whenever you run a command in the terminal, pipe stdout and stderr to a temp file, then cat it, and finally echo a newline. For example:

{ your-command; } > output.txt 2>&1
cat output.txt
echo ""
