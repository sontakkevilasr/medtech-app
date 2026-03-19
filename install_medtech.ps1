# ╔══════════════════════════════════════════════════════════════════════════════╗
# ║  MedTech Laravel Installer — Windows PowerShell                             ║
# ║  Run from your Laravel project root:                                        ║
# ║    powershell -ExecutionPolicy Bypass -File install_medtech.ps1             ║
# ╚══════════════════════════════════════════════════════════════════════════════╝

param(
    [string]$Source = "$PSScriptRoot\medtech"
)

$Project = Get-Location

# ── Colours ───────────────────────────────────────────────────────────────────
function ok   { param($m) Write-Host "  [OK] $m"   -ForegroundColor Green  }
function info { param($m) Write-Host "  --> $m"    -ForegroundColor Cyan   }
function warn { param($m) Write-Host "  [!] $m"    -ForegroundColor Yellow }
function err  { param($m) Write-Host "  [X] $m"    -ForegroundColor Red; exit 1 }

Write-Host ""
Write-Host "  ╔════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "  ║      MedTech Installer — Laravel 11        ║" -ForegroundColor Cyan
Write-Host "  ╚════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Project : $Project"
Write-Host "  Source  : $Source"
Write-Host ""

# ── Verify Laravel root ───────────────────────────────────────────────────────
if (-not (Test-Path "artisan")) {
    err "artisan not found. Run this script from your Laravel project root."
}
if (-not (Test-Path $Source)) {
    err "Source folder 'medtech\' not found next to this script."
}

# ── Helper: copy one file, creating directories as needed ─────────────────────
function Copy-ProjectFile {
    param([string]$RelPath)
    $src = Join-Path $Source $RelPath
    $dst = Join-Path $Project $RelPath
    $dir = Split-Path $dst -Parent
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    if (Test-Path $src) {
        Copy-Item $src $dst -Force
    } else {
        warn "Missing source: $RelPath"
    }
}

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 1 — Composer packages
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n[1/6] Installing Composer packages..." -ForegroundColor Cyan
composer require barryvdh/laravel-dompdf maatwebsite/laravel-excel razorpay/razorpay --no-interaction --quiet
ok "Composer packages installed"

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 2 — Application PHP files
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n[2/6] Copying application files..." -ForegroundColor Cyan

# Enums
"AccessType","AppointmentStatus","HealthLogType","UserRole","SubscriptionPlan" | ForEach-Object {
    Copy-ProjectFile "app\Enums\$_.php"
}
ok "Enums (5)"

# Models
"User","UserProfile","DoctorProfile","FamilyMember","PatientAccessPermission",
"DoctorAccessRequest","MedicalRecord","Prescription","PrescriptionMedicine",
"Appointment","Supporting","Timeline" | ForEach-Object {
    Copy-ProjectFile "app\Models\$_.php"
}
ok "Models (12)"

# Middleware
"RoleMiddleware","EnsureUserIsVerified","EnsureUserIsActive",
"DoctorAccessVerified","PremiumDoctor","SetLocale" | ForEach-Object {
    Copy-ProjectFile "app\Http\Middleware\$_.php"
}
ok "Middleware (6)"

# Services
"OtpService","SubIdService","AccessControlService","WhatsAppService",
"PdfService","ReminderService","ExcelExportService","RazorpayService" | ForEach-Object {
    Copy-ProjectFile "app\Services\$_.php"
}
ok "Services (8)"

# Jobs + Requests + Providers
Copy-ProjectFile "app\Jobs\AllJobs.php"
Copy-ProjectFile "app\Http\Requests\Auth\AuthRequests.php"
Copy-ProjectFile "app\Providers\AppServiceProvider.php"
ok "Jobs, Requests, Providers"

# Auth Controllers
"LoginController","OtpController","PasswordSetupController" | ForEach-Object {
    Copy-ProjectFile "app\Http\Controllers\Auth\$_.php"
}
ok "Auth Controllers (3)"

# Doctor Controllers
"DashboardController","PatientController","PrescriptionController","AppointmentController" | ForEach-Object {
    Copy-ProjectFile "app\Http\Controllers\Doctor\$_.php"
}
ok "Doctor Controllers (4)"

# Patient Controllers
"DashboardController","AppointmentController","AccessPermissionController",
"FamilyMemberController","HealthLogController","MedicationReminderController" | ForEach-Object {
    Copy-ProjectFile "app\Http\Controllers\Patient\$_.php"
}
ok "Patient Controllers (6)"

# Admin Controllers
"DashboardController","UserManagementController","DoctorVerificationController","ReportController" | ForEach-Object {
    Copy-ProjectFile "app\Http\Controllers\Admin\$_.php"
}
ok "Admin Controllers (4)"

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 3 — Routes + Bootstrap
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n[3/6] Copying routes and bootstrap..." -ForegroundColor Cyan

"web","auth","doctor","patient","admin" | ForEach-Object {
    Copy-ProjectFile "routes\$_.php"
}
Copy-ProjectFile "bootstrap\app.php"
ok "Routes (5) + bootstrap\app.php"

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 4 — Config
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n[4/6] Copying config files..." -ForegroundColor Cyan

"medtech","otp","whatsapp" | ForEach-Object {
    Copy-ProjectFile "config\$_.php"
}
ok "Config files (3)"

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 5 — Migrations + Seeders
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n[5/6] Copying migrations and seeders..." -ForegroundColor Cyan

$migrations = @(
    "0001_01_01_000000_create_users_table",
    "0002_create_user_profiles_table",
    "0003_create_family_members_table",
    "0004_create_doctor_profiles_table",
    "0005_create_access_control_tables",
    "0006_create_medical_records_table",
    "0007_create_prescriptions_table",
    "0008_create_appointments_table",
    "0009_create_timeline_tables",
    "0010_create_payment_tables",
    "0011_create_health_tracking_tables",
    "0012_create_notifications_table"
)
$migrations | ForEach-Object { Copy-ProjectFile "database\migrations\$_.php" }
ok "Migrations (12)"

$seeders = @(
    "DatabaseSeeder","AdminUserSeeder","DoctorSeeder","DoctorProfileSeeder",
    "PatientSeeder","FamilyMemberSeeder","AccessPermissionSeeder",
    "AppointmentSeeder","MedicalRecordSeeder","HealthLogSeeder","TimelineTemplateSeeder"
)
$seeders | ForEach-Object { Copy-ProjectFile "database\seeders\$_.php" }
ok "Seeders (11)"

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 6 — Blade Views
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n[6/6] Copying Blade views..." -ForegroundColor Cyan

# Layouts
"guest","doctor","patient","admin" | ForEach-Object {
    Copy-ProjectFile "resources\views\layouts\$_.blade.php"
}
ok "Layouts (4)"

# Auth
"login","otp-verify","register-international" | ForEach-Object {
    Copy-ProjectFile "resources\views\auth\$_.blade.php"
}
"role","password","profile" | ForEach-Object {
    Copy-ProjectFile "resources\views\auth\setup\$_.blade.php"
}
ok "Auth views (6)"

# Doctor
Copy-ProjectFile "resources\views\doctor\dashboard.blade.php"
"index","history"         | ForEach-Object { Copy-ProjectFile "resources\views\doctor\patients\$_.blade.php" }
"create","show","pdf"     | ForEach-Object { Copy-ProjectFile "resources\views\doctor\prescriptions\$_.blade.php" }
"index","calendar","slots"| ForEach-Object { Copy-ProjectFile "resources\views\doctor\appointments\$_.blade.php" }
ok "Doctor views (9)"

# Patient
Copy-ProjectFile "resources\views\patient\dashboard.blade.php"
"index","history"                          | ForEach-Object { Copy-ProjectFile "resources\views\patient\access\$_.blade.php" }
"index","book-doctor","book-slots","_doctor-card" | ForEach-Object { Copy-ProjectFile "resources\views\patient\appointments\$_.blade.php" }
"index","show","create","edit","_form"     | ForEach-Object { Copy-ProjectFile "resources\views\patient\family\$_.blade.php" }
"index","logs"                             | ForEach-Object { Copy-ProjectFile "resources\views\patient\health\$_.blade.php" }
Copy-ProjectFile "resources\views\patient\reminders\index.blade.php"
ok "Patient views (16)"

# Admin
Copy-ProjectFile "resources\views\admin\dashboard.blade.php"
"index","show" | ForEach-Object { Copy-ProjectFile "resources\views\admin\users\$_.blade.php" }
"index","show" | ForEach-Object { Copy-ProjectFile "resources\views\admin\verification\$_.blade.php" }
Copy-ProjectFile "resources\views\admin\reports\index.blade.php"
ok "Admin views (6)"

# PDF
Copy-ProjectFile "resources\views\pdf\prescription.blade.php"
ok "PDF views"

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 7 — .env additions
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`nChecking .env for required keys..." -ForegroundColor Cyan

function Add-EnvKey {
    param([string]$Key, [string]$Value = "")
    if (Test-Path ".env") {
        $content = Get-Content ".env" -Raw
        if ($content -notmatch "^$Key=") {
            Add-Content ".env" "`n$Key=$Value"
        }
    }
}

Add-EnvKey "SUB_ID_PREFIX"       "MED"
Add-EnvKey "MSG91_API_KEY"       ""
Add-EnvKey "MSG91_SENDER_ID"     "MEDTCH"
Add-EnvKey "FAST2SMS_API_KEY"    ""
Add-EnvKey "WATI_API_URL"        ""
Add-EnvKey "WATI_API_TOKEN"      ""
Add-EnvKey "RAZORPAY_KEY_ID"     ""
Add-EnvKey "RAZORPAY_KEY_SECRET" ""
Add-EnvKey "QUEUE_CONNECTION"    "database"
ok ".env keys added"

# ═══════════════════════════════════════════════════════════════════════════════
# STEP 8 — Artisan cache clear
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`nClearing caches..." -ForegroundColor Cyan
php artisan config:clear | Out-Null
php artisan cache:clear  | Out-Null
php artisan route:clear  | Out-Null
php artisan view:clear   | Out-Null
ok "Caches cleared"

# ═══════════════════════════════════════════════════════════════════════════════
# Done
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host ""
Write-Host "  ╔══════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "  ║  All files installed successfully!               ║" -ForegroundColor Green
Write-Host "  ╚══════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""
Write-Host "  Next steps:" -ForegroundColor Yellow
Write-Host "    1. Edit .env  --  fill in DB_*, MSG91, WATI, Razorpay keys"
Write-Host "    2. php artisan migrate:fresh --seed"
Write-Host "    3. php artisan queue:work          (new terminal)"
Write-Host "    4. php artisan serve"
Write-Host ""
Write-Host "  Test logins:" -ForegroundColor Cyan
Write-Host "    Admin   :  +91 9000000000  /  Admin@1234"
Write-Host "    Doctor  :  +91 9100000001  /  Doctor@1234"
Write-Host "    Patient :  +91 9200000001  /  Patient@1234"
Write-Host ""

