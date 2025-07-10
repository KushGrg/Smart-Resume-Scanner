# HR Applications Management Feature

## Overview
Created a comprehensive HR applications management system that allows HR users to view, review, and manage job applications with resume scoring and status updates.

## Files Created/Modified

### 1. Component: `app/Livewire/Hr/ViewApplications.php`
- **Purpose**: Main component for HR to view and manage job applications
- **Features**:
  - View all applications for jobs posted by the logged-in HR user
  - Filter by job post, application status, minimum score
  - Sort by various columns (score, applicant name, job title, etc.)
  - View resume details in modal
  - Download resumes
  - Update application status (pending, reviewed, shortlisted, rejected)

### 2. View: `resources/views/livewire/hr/view-applications.blade.php`
- **Purpose**: Blade template for the applications management interface
- **Features**:
  - Responsive table with sortable columns
  - Advanced filtering system
  - Resume preview modal with PDF support
  - Status update modal with confirmation
  - Color-coded scoring system
  - Intuitive action buttons

### 3. Route: `routes/web.php`
- **Added**: `/hr/applications` route with proper middleware and permissions
- **Permission Required**: `view job posts` (leveraging existing HR permissions)

### 4. Navigation: `resources/views/components/layouts/app.blade.php`
- **Added**: "View Applications" menu item for HR role users

## Key Features

### 1. Resume Scoring Display
- Visual score representation with color coding:
  - Green (≥80%): Excellent match
  - Blue (≥60%): Good match  
  - Yellow (≥40%): Fair match
  - Red (<40%): Poor match
- Progress bars for visual score representation
- N/A display for unprocessed resumes

### 2. Status Management
- Four status levels: Pending, Reviewed, Shortlisted, Rejected
- Color-coded badges for quick status identification
- Modal-based status update with confirmation
- Proper authorization checks before status changes

### 3. Filtering & Sorting
- Filter by specific job posts
- Filter by application status
- Filter by minimum score threshold
- Sort by score (default: highest first), applicant name, job title, application date
- Search functionality across job titles and applicant details

### 4. Resume Viewing
- Full-screen modal for resume details
- Applicant information panel with key details
- PDF preview for PDF files (with fallback for unsupported browsers)
- Download functionality for all file types
- File size and type information display

### 5. Security & Authorization
- Policy-based authorization using existing `ResumePolicy`
- HR users can only view applications for their own job posts
- Proper validation for status updates
- Error handling with user-friendly messages

## Usage Workflow

1. **HR Login**: HR user logs into the system
2. **Navigation**: Click "View Applications" in the sidebar menu
3. **Filter**: Use filters to narrow down applications by job, status, or score
4. **Review**: Click eye icon to view resume details and applicant information
5. **Evaluate**: Review the resume content and similarity score
6. **Update Status**: Use pencil icon to change application status
7. **Download**: Download resumes for offline review if needed

## Technical Implementation

### Database Relationships
- Uses existing relationships between User → HR → JobPost → Resume → JobSeekerDetail
- Leverages Resume model's status accessors and scoring functionality
- Maintains data integrity through foreign key constraints

### Performance Optimizations
- Eager loading of relationships (`with()` clauses)
- Proper indexing on foreign keys and status fields
- Pagination for large datasets
- Efficient query scoping for HR-specific data

### UI/UX Considerations
- Responsive design works on desktop and mobile
- Mary UI components for consistent styling
- Toast notifications for user feedback
- Loading states during status updates
- Clear visual hierarchy and intuitive actions

## Future Enhancements
- Bulk status updates for multiple applications
- Application notes/comments system
- Email notifications to applicants on status changes
- Advanced analytics and reporting
- Interview scheduling integration
- Export functionality for application data

## Permissions Required
- HR users need `view job posts` permission (already configured)
- Uses existing authorization through ResumePolicy
- Respects role-based access control
