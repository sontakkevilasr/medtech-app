#!/usr/bin/env bash
# ╔══════════════════════════════════════════════════════════════════════════════╗
# ║  MedTech Laravel Installer                                                  ║
# ║  Run from your Laravel project root:  bash install_medtech.sh               ║
# ║  Requires: Laravel 11 project already created (composer create-project)     ║
# ╚══════════════════════════════════════════════════════════════════════════════╝

set -e  # exit on any error

# ── Colours ───────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'; BLUE='\033[0;34m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
ok()   { echo -e "${GREEN}  ✓ $1${NC}"; }
info() { echo -e "${BLUE}  → $1${NC}"; }
warn() { echo -e "${YELLOW}  ⚠ $1${NC}"; }
err()  { echo -e "${RED}  ✗ $1${NC}"; exit 1; }

# ── Verify we're in a Laravel root ────────────────────────────────────────────
[[ -f "artisan" ]] || err "artisan not found. Run this script from your Laravel project root."

PROJECT=$(pwd)
SOURCE="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/medtech"

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║      MedTech Installer — Laravel 11        ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  Project : ${PROJECT}"
echo -e "  Source  : ${SOURCE}"
echo ""

[[ -d "$SOURCE" ]] || err "Source directory 'medtech/' not found next to this script."

# ── Step 1: Composer packages ─────────────────────────────────────────────────
echo -e "${BLUE}[1/6] Installing Composer packages...${NC}"
composer require \
    barryvdh/laravel-dompdf \
    maatwebsite/laravel-excel \
    razorpay/razorpay \
    --no-interaction --quiet
ok "Composer packages installed"

# ── Step 2: Copy all PHP files ────────────────────────────────────────────────
echo -e "${BLUE}[2/6] Copying application files...${NC}"

copy_file() {
    local src="$SOURCE/$1"
    local dst="$PROJECT/$1"
    mkdir -p "$(dirname "$dst")"
    cp "$src" "$dst"
}

# ── Enums
copy_file "app/Enums/AccessType.php"
copy_file "app/Enums/AppointmentStatus.php"
copy_file "app/Enums/HealthLogType.php"
copy_file "app/Enums/UserRole.php"
ok "Enums"

# ── Models
for f in User UserProfile DoctorProfile FamilyMember PatientAccessPermission \
          DoctorAccessRequest MedicalRecord Prescription PrescriptionMedicine \
          Appointment Supporting Timeline; do
    copy_file "app/Models/${f}.php"
done
ok "Models (11)"

# ── Middleware
for f in RoleMiddleware EnsureUserIsVerified EnsureUserIsActive \
          DoctorAccessVerified PremiumDoctor SetLocale; do
    copy_file "app/Http/Middleware/${f}.php"
done
ok "Middleware (6)"

# ── Services
for f in OtpService SubIdService AccessControlService WhatsAppService \
          PdfService ReminderService ExcelExportService RazorpayService; do
    copy_file "app/Services/${f}.php"
done
ok "Services (8)"

# ── Jobs
copy_file "app/Jobs/AllJobs.php"
ok "Jobs"

# ── Requests
copy_file "app/Http/Requests/Auth/AuthRequests.php"
ok "Form Requests"

# ── Controllers — Auth
for f in LoginController OtpController PasswordSetupController; do
    copy_file "app/Http/Controllers/Auth/${f}.php"
done
ok "Auth Controllers (3)"

# ── Controllers — Doctor
for f in DashboardController PatientController PrescriptionController AppointmentController; do
    copy_file "app/Http/Controllers/Doctor/${f}.php"
done
ok "Doctor Controllers (4)"

# ── Controllers — Patient
for f in DashboardController AppointmentController AccessPermissionController \
          FamilyMemberController HealthLogController MedicationReminderController; do
    copy_file "app/Http/Controllers/Patient/${f}.php"
done
ok "Patient Controllers (6)"

# ── Controllers — Admin
for f in DashboardController UserManagementController DoctorVerificationController ReportController; do
    copy_file "app/Http/Controllers/Admin/${f}.php"
done
ok "Admin Controllers (4)"

# ── Providers
copy_file "app/Providers/AppServiceProvider.php"
ok "AppServiceProvider"

# ── Step 3: Routes + Bootstrap ────────────────────────────────────────────────
echo -e "${BLUE}[3/6] Copying routes and bootstrap...${NC}"
for f in web.php auth.php doctor.php patient.php admin.php; do
    copy_file "routes/${f}"
done
copy_file "bootstrap/app.php"
ok "Routes (5) + bootstrap/app.php"

# ── Step 4: Config ────────────────────────────────────────────────────────────
echo -e "${BLUE}[4/6] Copying config files...${NC}"
for f in medtech.php otp.php whatsapp.php; do
    copy_file "config/${f}"
done
ok "Config files (3)"

# ── Step 5: Database (migrations + seeders) ───────────────────────────────────
echo -e "${BLUE}[5/6] Copying migrations and seeders...${NC}"

for f in \
    0001_01_01_000000_create_users_table.php \
    0002_create_user_profiles_table.php \
    0003_create_family_members_table.php \
    0004_create_doctor_profiles_table.php \
    0005_create_access_control_tables.php \
    0006_create_medical_records_table.php \
    0007_create_prescriptions_table.php \
    0008_create_appointments_table.php \
    0009_create_timeline_tables.php \
    0010_create_payment_tables.php \
    0011_create_health_tracking_tables.php \
    0012_create_notifications_table.php; do
    copy_file "database/migrations/${f}"
done
ok "Migrations (12)"

for f in DatabaseSeeder AdminUserSeeder DoctorSeeder DoctorProfileSeeder \
          PatientSeeder FamilyMemberSeeder AccessPermissionSeeder \
          AppointmentSeeder MedicalRecordSeeder HealthLogSeeder \
          TimelineTemplateSeeder; do
    copy_file "database/seeders/${f}.php"
done
ok "Seeders (11)"

# ── Step 6: Views ─────────────────────────────────────────────────────────────
echo -e "${BLUE}[6/6] Copying Blade views...${NC}"

# Layouts
for f in guest doctor patient admin; do
    copy_file "resources/views/layouts/${f}.blade.php"
done
ok "Layouts (4)"

# Auth views
for f in login otp-verify register-international; do
    copy_file "resources/views/auth/${f}.blade.php"
done
for f in role password profile; do
    copy_file "resources/views/auth/setup/${f}.blade.php"
done
ok "Auth views (6)"

# Doctor views
copy_file "resources/views/doctor/dashboard.blade.php"
for f in index history; do
    copy_file "resources/views/doctor/patients/${f}.blade.php"
done
for f in create show pdf; do
    copy_file "resources/views/doctor/prescriptions/${f}.blade.php"
done
for f in index calendar slots; do
    copy_file "resources/views/doctor/appointments/${f}.blade.php"
done
ok "Doctor views (9)"

# Patient views
copy_file "resources/views/patient/dashboard.blade.php"
for f in index history; do
    copy_file "resources/views/patient/access/${f}.blade.php"
done
for f in index book-doctor book-slots _doctor-card; do
    copy_file "resources/views/patient/appointments/${f}.blade.php"
done
for f in index show create edit _form; do
    copy_file "resources/views/patient/family/${f}.blade.php"
done
for f in index logs; do
    copy_file "resources/views/patient/health/${f}.blade.php"
done
copy_file "resources/views/patient/reminders/index.blade.php"
ok "Patient views (16)"

# Admin views
copy_file "resources/views/admin/dashboard.blade.php"
for f in index show; do
    copy_file "resources/views/admin/users/${f}.blade.php"
done
for f in index show; do
    copy_file "resources/views/admin/verification/${f}.blade.php"
done
copy_file "resources/views/admin/reports/index.blade.php"
ok "Admin views (6)"

# PDF view
copy_file "resources/views/pdf/prescription.blade.php" 2>/dev/null || true

# ── Step 7: .env additions ────────────────────────────────────────────────────
echo ""
echo -e "${BLUE}Checking .env for required keys...${NC}"

add_env() {
    grep -q "^${1}=" .env 2>/dev/null || echo "${1}=${2}" >> .env
}

add_env "SUB_ID_PREFIX"           "MED"
add_env "MSG91_API_KEY"           ""
add_env "MSG91_SENDER_ID"         "MEDTCH"
add_env "FAST2SMS_API_KEY"        ""
add_env "WATI_API_URL"            ""
add_env "WATI_API_TOKEN"          ""
add_env "RAZORPAY_KEY_ID"         ""
add_env "RAZORPAY_KEY_SECRET"     ""
add_env "QUEUE_CONNECTION"        "database"
ok ".env keys added (edit values as needed)"

# ── Step 8: Artisan setup ─────────────────────────────────────────────────────
echo ""
echo -e "${BLUE}Running artisan setup...${NC}"

php artisan config:clear  --quiet
php artisan cache:clear   --quiet
php artisan route:clear   --quiet
php artisan view:clear    --quiet

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✓  All files installed successfully!           ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${YELLOW}Next steps:${NC}"
echo -e "  1. Edit ${YELLOW}.env${NC} — set DB_*, MSG91/WATI/Razorpay keys"
echo -e "  2. ${YELLOW}php artisan migrate:fresh --seed${NC}"
echo -e "  3. ${YELLOW}php artisan queue:work${NC}  (in a separate terminal)"
echo -e "  4. ${YELLOW}php artisan serve${NC}"
echo ""
echo -e "  ${BLUE}Test logins:${NC}"
echo -e "    Admin   : +91 9000000000  /  Admin\@1234"
echo -e "    Doctor  : +91 9100000001  /  Doctor\@1234"
echo -e "    Patient : +91 9200000001  /  Patient\@1234"
echo ""
