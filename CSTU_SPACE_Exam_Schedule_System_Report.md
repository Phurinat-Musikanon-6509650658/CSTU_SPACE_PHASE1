# CSTU SPACE - Exam Schedule and System Settings Implementation Report

## Overview
This report documents the implementation of the Exam Schedule Management system and System Status Control features for the CSTU SPACE project management system.

## Implementation Date
November 27, 2025

## Features Implemented

### 1. System Status Control (Admin Only)

#### Database Schema
- **Table**: `system_settings`
- **Fields**:
  - `setting_id` (Primary Key, Auto Increment)
  - `setting_key` (VARCHAR 255, UNIQUE)
  - `setting_value` (TEXT)
  - `description` (TEXT, nullable)
  - `created_at`, `updated_at` (Timestamps)

- **Default Settings**:
  - `system_status`: 'open' (Controls whether students can submit/modify data)
  - `current_year`: '2568' (Current academic year)
  - `current_semester`: '1' (Current semester: 1, 2, or 3 for summer)

#### System Status Features
- **Toggle System Open/Close**: Admin can open or close the entire system
  - When **OPEN**: Students can submit and modify their data
  - When **CLOSED**: Students cannot submit or modify data
- **Year/Semester Management**: Admin can update current academic year and semester
- **Visual Indicators**: 
  - Badge showing current status (Green for OPEN, Red for CLOSED)
  - Lock/Unlock icons
  - Clear messaging about system state

#### Controller Methods
**SystemSettingsController**:
- `index()` - Enhanced to show system status controls
- `toggleSystemStatus()` - AJAX endpoint to toggle system open/close
- `updateSettings()` - Update year/semester settings

#### Routes
```php
POST   /admin/system/toggle-status
PUT    /admin/system/settings
```

---

### 2. Exam Schedule Management (Admin Only)

#### Database Schema
- **Table**: `exam_schedule`
- **Fields**:
  - `ex_id` (Primary Key, Auto Increment)
  - `project_id` (Foreign Key → projects.project_id, ON DELETE CASCADE)
  - `ex_start_time` (DATETIME)
  - `ex_end_time` (DATETIME)
  - `location` (VARCHAR 255, nullable)
  - `notes` (TEXT, nullable)
  - `created_at`, `updated_at` (Timestamps)
- **Indexes**: 
  - Primary key on `ex_id`
  - Index on `ex_start_time` for performance
  - Foreign key constraint with cascade delete

#### Features
- **Create Exam Schedule**: Schedule exams for specific projects
- **View All Schedules**: List all exam schedules with project details
- **Edit Schedule**: Modify existing exam schedules
- **Delete Schedule**: Remove exam schedules
- **Smart Duration Display**: Shows exam duration in hours and minutes
- **Auto-fill End Time**: Automatically suggests end time 2 hours after start time

#### Controller Methods
**SystemSettingsController**:
- `examScheduleIndex()` - List all exam schedules (paginated, 20 per page)
- `examScheduleCreate()` - Show create form
- `examScheduleStore()` - Save new exam schedule
- `examScheduleEdit($id)` - Show edit form
- `examScheduleUpdate($id)` - Update exam schedule
- `examScheduleDestroy($id)` - Delete exam schedule (AJAX)

#### Routes
```php
GET    /admin/exam-schedules           → Index
GET    /admin/exam-schedules/create    → Create Form
POST   /admin/exam-schedules           → Store
GET    /admin/exam-schedules/{id}/edit → Edit Form
PUT    /admin/exam-schedules/{id}      → Update
DELETE /admin/exam-schedules/{id}      → Delete (AJAX)
```

---

### 3. Models

#### SystemSetting Model
**Location**: `app/Models/SystemSetting.php`

**Static Helper Methods**:
- `get($key, $default = null)` - Retrieve a setting value
- `set($key, $value, $description = null)` - Update or create a setting
- `isSystemOpen()` - Check if system is currently open

**Example Usage**:
```php
// Get system status
$status = SystemSetting::get('system_status', 'open');

// Check if system is open
if (SystemSetting::isSystemOpen()) {
    // Allow student operations
}

// Update a setting
SystemSetting::set('current_year', '2568', 'Current academic year');
```

#### ExamSchedule Model
**Location**: `app/Models/ExamSchedule.php`

**Properties**:
- Table: `exam_schedule`
- Primary Key: `ex_id`
- Fillable: `project_id`, `ex_start_time`, `ex_end_time`, `location`, `notes`
- Casts: `ex_start_time` and `ex_end_time` to `datetime`

**Relationships**:
- `belongsTo(Project::class)` - Each exam schedule belongs to one project

#### Project Model Enhancement
**Location**: `app/Models/Project.php`

**New Relationship**:
- `hasOne(ExamSchedule::class)` - Each project can have one exam schedule

**Example Usage**:
```php
// Get exam schedule for a project
$project = Project::with('examSchedule')->find($id);
if ($project->examSchedule) {
    echo $project->examSchedule->ex_start_time;
}
```

---

### 4. Views

#### System Settings Dashboard
**File**: `resources/views/admin/system/index.blade.php`

**Enhancements**:
- System Status Card (new)
  - Visual status indicator
  - Toggle button to open/close system
  - Year/Semester update form
  - Real-time status updates via AJAX
- Quick Actions section now includes link to Exam Schedules

**Features**:
- AJAX-based system status toggle
- Confirmation dialogs before critical actions
- Color-coded status indicators
- Responsive layout

#### Exam Schedule Views

**Index Page** (`resources/views/admin/exam-schedules/index.blade.php`):
- Table listing all exam schedules
- Shows project name, code, start/end times, location, duration
- Edit and Delete buttons for each schedule
- AJAX delete with confirmation
- Pagination (20 items per page)
- Empty state message

**Create Form** (`resources/views/admin/exam-schedules/create.blade.php`):
- Project dropdown selection
- DateTime pickers for start and end times
- Location input (optional)
- Notes textarea (optional)
- Auto-fill end time (2 hours after start)
- Form validation

**Edit Form** (`resources/views/admin/exam-schedules/edit.blade.php`):
- Pre-filled form with existing data
- Same fields as create form
- Shows creation and last update timestamps
- Form validation

---

### 5. User Interface Features

#### System Status Control
- **Visual Feedback**: 
  - Green badge with unlock icon when OPEN
  - Red badge with lock icon when CLOSED
- **Toggle Button**: Large, prominent button to change status
- **Confirmation**: Asks admin to confirm before changing status
- **Loading State**: Shows spinner while processing
- **Auto-reload**: Page refreshes after successful toggle

#### Exam Schedule Management
- **Duration Calculation**: Automatically calculates and displays exam duration
- **Smart Time Selection**: Auto-suggests end time when start time is selected
- **Responsive Table**: Works well on all screen sizes
- **Icon-based Actions**: Edit (pencil) and Delete (trash) icons
- **Confirmation Dialogs**: Prevents accidental deletions
- **Empty States**: Friendly message when no schedules exist

---

## Security & Permissions

### Admin-Only Access
All new features are restricted to Admin role only:
- System status control
- Year/Semester settings
- Exam schedule CRUD operations

### Permission Checks
Every controller method includes:
```php
if (!PermissionHelper::isAdmin()) {
    return redirect()->route('menu')->with('error', 'Unauthorized access');
}
```

### CSRF Protection
- All forms include `@csrf` token
- AJAX requests send `X-CSRF-TOKEN` header

---

## Technical Details

### Database Migrations
1. **2025_11_27_004802_create_exam_schedule_table.php**
   - Creates exam_schedule table
   - Adds foreign key to projects table
   - Status: ✅ Migrated (44.21ms)

2. **2025_11_27_005049_create_system_settings_table.php**
   - Creates system_settings table
   - Inserts default settings
   - Status: ✅ Migrated (18.12ms)

### JavaScript Features
- System status toggle (AJAX)
- Exam schedule deletion (AJAX)
- Auto-fill end time on schedule creation
- Form validation
- Loading states and error handling

### Validation Rules
**Exam Schedule**:
- `project_id`: required, must exist in projects table
- `ex_start_time`: required, must be valid datetime
- `ex_end_time`: required, must be valid datetime, must be after start time
- `location`: optional, max 255 characters
- `notes`: optional, text

**System Settings**:
- `current_year`: required, numeric, 4 digits
- `current_semester`: required, must be 1, 2, or 3

---

## Integration Points

### Menu Integration
System Settings page now includes:
- Link to Exam Schedules in Quick Actions
- Visual system status indicator (planned for menu badge)

### Project Integration
Projects can now have associated exam schedules:
```php
$project->examSchedule->ex_start_time
```

### Future Enhancements (Planned)
1. Student-facing exam schedule view
2. Calendar view for exam schedules
3. Email notifications for upcoming exams
4. Conflict detection (multiple exams at same time/location)
5. Export exam schedules to CSV/PDF
6. System status indicator in main menu
7. Automatic system close/open based on schedule

---

## Files Modified/Created

### Controllers
- ✅ `app/Http/Controllers/SystemSettingsController.php` (Enhanced)
  - Added 8 new methods for system status and exam schedule management

### Models
- ✅ `app/Models/SystemSetting.php` (New)
- ✅ `app/Models/ExamSchedule.php` (New)
- ✅ `app/Models/Project.php` (Enhanced - added relationship)

### Migrations
- ✅ `database/migrations/2025_11_27_004802_create_exam_schedule_table.php` (New)
- ✅ `database/migrations/2025_11_27_005049_create_system_settings_table.php` (New)

### Views
- ✅ `resources/views/admin/system/index.blade.php` (Enhanced)
- ✅ `resources/views/admin/exam-schedules/index.blade.php` (New)
- ✅ `resources/views/admin/exam-schedules/create.blade.php` (New)
- ✅ `resources/views/admin/exam-schedules/edit.blade.php` (New)

### Routes
- ✅ `routes/web.php` (Enhanced - added 8 new routes)

---

## Testing Checklist

### System Status Control
- [ ] Admin can view current system status
- [ ] Admin can toggle system from OPEN to CLOSED
- [ ] Admin can toggle system from CLOSED to OPEN
- [ ] Confirmation dialog appears before toggle
- [ ] Status updates correctly in database
- [ ] Status badge color changes after toggle
- [ ] Year/Semester form validates correctly
- [ ] Year/Semester updates save to database
- [ ] Non-admin users cannot access these features

### Exam Schedule Management
- [ ] Admin can view list of all exam schedules
- [ ] List shows correct project information
- [ ] Pagination works correctly
- [ ] Admin can access create form
- [ ] Project dropdown loads all projects
- [ ] Date/time pickers work correctly
- [ ] End time auto-fills 2 hours after start
- [ ] Form validation prevents invalid submissions
- [ ] New schedule saves correctly to database
- [ ] Admin can edit existing schedule
- [ ] Edit form pre-fills with existing data
- [ ] Updates save correctly
- [ ] Admin can delete schedule
- [ ] Delete confirmation dialog appears
- [ ] Schedule deletes from database
- [ ] Non-admin users cannot access these features

---

## Database Schema Diagram

```
┌─────────────────────────────┐
│      system_settings        │
├─────────────────────────────┤
│ PK setting_id (INT)         │
│    setting_key (VARCHAR)    │ ← UNIQUE
│    setting_value (TEXT)     │
│    description (TEXT)       │
│    created_at, updated_at   │
└─────────────────────────────┘

┌─────────────────────────────┐       ┌─────────────────────────────┐
│        projects             │       │      exam_schedule          │
├─────────────────────────────┤       ├─────────────────────────────┤
│ PK project_id (INT)         │◄──────│ PK ex_id (INT)              │
│    project_name             │       │ FK project_id (INT)         │
│    project_code             │       │    ex_start_time (DATETIME) │
│    ...                      │       │    ex_end_time (DATETIME)   │
│    created_at, updated_at   │       │    location (VARCHAR)       │
└─────────────────────────────┘       │    notes (TEXT)             │
                                      │    created_at, updated_at   │
                                      └─────────────────────────────┘
```

---

## Usage Examples

### For Administrators

#### Opening/Closing the System
1. Navigate to **System Settings** from menu
2. View current system status in the top card
3. Click "Close System" or "Open System" button
4. Confirm the action
5. System status updates immediately

#### Updating Academic Year/Semester
1. Navigate to **System Settings**
2. In the System Status Control card, locate the form
3. Enter new year (e.g., 2568)
4. Select semester (1, 2, or 3)
5. Click "Update Settings"

#### Creating an Exam Schedule
1. Navigate to **System Settings** → **Exam Schedules**
2. Click "Add Exam Schedule"
3. Select a project from dropdown
4. Choose start date and time
5. End time auto-fills (can be modified)
6. Optionally add location and notes
7. Click "Create Exam Schedule"

#### Managing Exam Schedules
- **View**: All schedules displayed in table with project info, times, location
- **Edit**: Click edit icon, modify fields, save
- **Delete**: Click delete icon, confirm deletion

### For Developers

#### Checking System Status in Code
```php
use App\Models\SystemSetting;

// Check if system is open
if (SystemSetting::isSystemOpen()) {
    // Allow student operations
} else {
    // Show "system closed" message
}

// Get specific settings
$year = SystemSetting::get('current_year');
$semester = SystemSetting::get('current_semester');
```

#### Working with Exam Schedules
```php
use App\Models\ExamSchedule;
use App\Models\Project;

// Get all upcoming exams
$upcomingExams = ExamSchedule::where('ex_start_time', '>', now())
    ->orderBy('ex_start_time', 'asc')
    ->get();

// Get exam for specific project
$project = Project::with('examSchedule')->find($projectId);
if ($project->examSchedule) {
    $examTime = $project->examSchedule->ex_start_time;
}

// Create new exam schedule
ExamSchedule::create([
    'project_id' => $projectId,
    'ex_start_time' => '2025-12-15 09:00:00',
    'ex_end_time' => '2025-12-15 11:00:00',
    'location' => 'Room 301',
    'notes' => 'Bring your ID card'
]);
```

---

## Conclusion

The Exam Schedule Management and System Status Control features have been successfully implemented with:
- ✅ Complete database schema
- ✅ Full CRUD functionality
- ✅ Admin-only access control
- ✅ User-friendly interface
- ✅ AJAX-based interactions
- ✅ Form validation
- ✅ Responsive design

All routes are registered, migrations are run, and the system is ready for testing and deployment.
