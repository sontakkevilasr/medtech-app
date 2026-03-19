# MedTech — Complete Route Reference

## Middleware Stack (applied in order)

```
Request
  → StartSession
  → Authenticate          (auth)
  → EnsureUserIsActive    (active)       ← suspends blocked accounts
  → EnsureUserIsVerified  (verified.mobile) ← blocks unverified OTP users
  → RoleMiddleware        (role:X)       ← enforces doctor/patient/admin
  → SetLocale             (locale)       ← sets en/hi/mr
  → [PremiumDoctor]       (premium)      ← optional, premium routes only
  → [DoctorAccessVerified](doctor.access)← optional, patient history only
```

---

## Public Routes

| Method | URL       | Name    | Description          |
|--------|-----------|---------|----------------------|
| GET    | `/`       | `home`  | Landing page         |
| GET    | `/health` | —       | Uptime health check  |

---

## Auth Routes  `/auth/*`

| Method | URL                           | Name                            | Description                      |
|--------|-------------------------------|---------------------------------|----------------------------------|
| GET    | `/auth/login`                 | `auth.login`                    | Mobile number entry              |
| POST   | `/auth/login`                 | `auth.login.send-otp`           | Send OTP to mobile               |
| POST   | `/auth/login/password`        | `auth.login.password`           | Login with password              |
| GET    | `/auth/otp/verify`            | `auth.otp.verify`               | OTP input screen                 |
| POST   | `/auth/otp/verify`            | `auth.otp.verify.submit`        | Verify OTP                       |
| POST   | `/auth/otp/resend`            | `auth.otp.resend`               | Resend OTP                       |
| GET    | `/auth/register/international`| `auth.register.international`   | International number form        |
| POST   | `/auth/register/international`| `auth.register.international.submit` | Register international      |
| GET    | `/auth/setup/role`            | `auth.setup.role`               | Choose Doctor / Patient          |
| POST   | `/auth/setup/role`            | `auth.setup.role.save`          | Save role selection              |
| GET    | `/auth/setup/password`        | `auth.setup.password`           | Set password (optional)          |
| POST   | `/auth/setup/password`        | `auth.setup.password.save`      | Save password                    |
| POST   | `/auth/setup/password/skip`   | `auth.setup.password.skip`      | Skip password setup              |
| GET    | `/auth/setup/profile`         | `auth.setup.profile`            | Complete profile                 |
| POST   | `/auth/setup/profile`         | `auth.setup.profile.save`       | Save profile                     |
| POST   | `/auth/logout`                | `auth.logout`                   | Logout                           |

---

## Shared Routes  (all authenticated roles)

| Method | URL                        | Name                       | Description              |
|--------|----------------------------|----------------------------|--------------------------|
| GET    | `/dashboard`               | `dashboard`                | Role-based redirect      |
| GET    | `/profile`                 | `profile.show`             | View profile             |
| GET    | `/profile/edit`            | `profile.edit`             | Edit profile form        |
| PUT    | `/profile`                 | `profile.update`           | Save profile             |
| POST   | `/profile/photo`           | `profile.photo`            | Upload profile photo     |
| PUT    | `/profile/language`        | `profile.language`         | Change language          |
| PUT    | `/profile/password`        | `profile.password`         | Change password          |
| GET    | `/notifications`           | `notifications.index`      | All notifications        |
| POST   | `/notifications/{id}/read` | `notifications.read`       | Mark one read            |
| POST   | `/notifications/read-all`  | `notifications.read-all`   | Mark all read            |
| GET    | `/notifications/count`     | `notifications.count`      | Unread count (AJAX)      |
| POST   | `/locale/{lang}`           | `locale.switch`            | Switch language          |

---

## Doctor Routes  `/doctor/*`  [role:doctor]

### Dashboard
| GET | `/doctor/` | `doctor.dashboard` | Doctor home |

### Patients
| Method | URL                                       | Name                            |
|--------|-------------------------------------------|---------------------------------|
| GET    | `/doctor/patients`                        | `doctor.patients.index`         |
| GET    | `/doctor/patients/search`                 | `doctor.patients.search`        |
| GET    | `/doctor/patients/{patient}`              | `doctor.patients.show`          |
| GET    | `/doctor/patients/{patient}/history`      | `doctor.patients.history` 🔒    |
| POST   | `/doctor/patients/{patient}/notes`        | `doctor.patients.notes`         |
| GET    | `/doctor/patients/{patient}/request-access` | `doctor.patients.request-access` |
| POST   | `/doctor/patients/{patient}/request-access` | `doctor.patients.request-access.send` |
| POST   | `/doctor/patients/{patient}/verify-otp`   | `doctor.patients.verify-otp`    |

🔒 = requires `doctor.access` middleware (checks access grant)

### Medical Records
| Method | URL                                  | Name                    |
|--------|--------------------------------------|-------------------------|
| GET    | `/doctor/records/create/{patient}`   | `doctor.records.create` |
| POST   | `/doctor/records/{patient}`          | `doctor.records.store`  |
| GET    | `/doctor/records/{record}`           | `doctor.records.show`   |
| GET    | `/doctor/records/{record}/edit`      | `doctor.records.edit`   |
| PUT    | `/doctor/records/{record}`           | `doctor.records.update` |
| DELETE | `/doctor/records/{record}`           | `doctor.records.destroy`|
| POST   | `/doctor/records/{record}/attachment`| `doctor.records.attachment` |

### Prescriptions
| Method | URL                                           | Name                           |
|--------|-----------------------------------------------|--------------------------------|
| GET    | `/doctor/prescriptions`                       | `doctor.prescriptions.index`   |
| GET    | `/doctor/prescriptions/create/{patient}`      | `doctor.prescriptions.create`  |
| POST   | `/doctor/prescriptions`                       | `doctor.prescriptions.store`   |
| GET    | `/doctor/prescriptions/{prescription}`        | `doctor.prescriptions.show`    |
| GET    | `/doctor/prescriptions/{prescription}/pdf`    | `doctor.prescriptions.pdf`     |
| GET    | `/doctor/prescriptions/{prescription}/preview`| `doctor.prescriptions.preview` |
| POST   | `/doctor/prescriptions/{prescription}/send-whatsapp` | `doctor.prescriptions.whatsapp` |
| POST   | `/doctor/prescriptions/{prescription}/cancel` | `doctor.prescriptions.cancel`  |

### Appointments
| Method | URL                                        | Name                              |
|--------|--------------------------------------------|-----------------------------------|
| GET    | `/doctor/appointments`                     | `doctor.appointments.index`       |
| GET    | `/doctor/appointments/calendar`            | `doctor.appointments.calendar`    |
| GET    | `/doctor/appointments/today`               | `doctor.appointments.today`       |
| GET    | `/doctor/appointments/slots/manage`        | `doctor.appointments.slots`       |
| POST   | `/doctor/appointments/slots/save`          | `doctor.appointments.slots.save`  |
| GET    | `/doctor/appointments/slots/available`     | `doctor.appointments.slots.available` |
| GET    | `/doctor/appointments/{appointment}`       | `doctor.appointments.show`        |
| POST   | `/doctor/appointments/{appointment}/confirm`  | `doctor.appointments.confirm`  |
| POST   | `/doctor/appointments/{appointment}/complete` | `doctor.appointments.complete` |
| POST   | `/doctor/appointments/{appointment}/cancel`   | `doctor.appointments.cancel`   |
| POST   | `/doctor/appointments/{appointment}/remind`   | `doctor.appointments.remind`   |

### Timelines [PREMIUM ⭐]
| Method | URL                                                   | Name                              |
|--------|-------------------------------------------------------|-----------------------------------|
| GET    | `/doctor/timelines`                                   | `doctor.timelines.index`          |
| GET    | `/doctor/timelines/create`                            | `doctor.timelines.create`         |
| POST   | `/doctor/timelines`                                   | `doctor.timelines.store`          |
| GET    | `/doctor/timelines/{template}`                        | `doctor.timelines.show`           |
| GET    | `/doctor/timelines/assign/{patient}`                  | `doctor.timelines.assign`         |
| POST   | `/doctor/timelines/assign/{patient}`                  | `doctor.timelines.assign.save`    |
| POST   | `/doctor/timelines/{template}/milestones`             | `doctor.timelines.milestones.store` |
| PUT    | `/doctor/timelines/{template}/milestones/{milestone}` | `doctor.timelines.milestones.update` |

### Analytics & Exports [PREMIUM ⭐]
| Method | URL                                     | Name                                 |
|--------|-----------------------------------------|--------------------------------------|
| GET    | `/doctor/analytics`                     | `doctor.analytics.index`             |
| GET    | `/doctor/analytics/export/patients`     | `doctor.analytics.export.patients`   |
| GET    | `/doctor/analytics/export/appointments` | `doctor.analytics.export.appointments` |
| GET    | `/doctor/analytics/export/prescriptions`| `doctor.analytics.export.prescriptions` |

### Subscription & Payments
| GET  | `/doctor/subscription/plans`   | `doctor.subscription.plans`   | Upgrade plans page |
| POST | `/doctor/subscription/checkout/{plan}` | `doctor.subscription.checkout` | Start payment |
| GET  | `/doctor/payments/qr-setup`   | `doctor.payments.qr-setup`    | UPI QR setup   |

---

## Patient Routes  `/patient/*`  [role:patient]

### Family Members
| Method | URL                                      | Name                          |
|--------|------------------------------------------|-------------------------------|
| GET    | `/patient/family`                        | `patient.family.index`        |
| GET    | `/patient/family/create`                 | `patient.family.create`       |
| POST   | `/patient/family`                        | `patient.family.store`        |
| GET    | `/patient/family/{member}`               | `patient.family.show`         |
| PUT    | `/patient/family/{member}`               | `patient.family.update`       |
| DELETE | `/patient/family/{member}`               | `patient.family.destroy`      |
| POST   | `/patient/family/{member}/delink`        | `patient.family.delink`       |
| POST   | `/patient/family/{member}/relink`        | `patient.family.relink`       |

### Medical History
| GET | `/patient/history`                       | `patient.history.index`       | Own records     |
| GET | `/patient/history/{record}`              | `patient.history.show`        | Single record   |
| GET | `/patient/history/member/{member}`       | `patient.history.member`      | Family member   |

### Appointments
| Method | URL                                     | Name                          |
|--------|-----------------------------------------|-------------------------------|
| GET    | `/patient/appointments`                 | `patient.appointments.index`  |
| GET    | `/patient/appointments/book`            | `patient.appointments.book`   |
| GET    | `/patient/appointments/book/{doctor}`   | `patient.appointments.book.slots` |
| POST   | `/patient/appointments/book/{doctor}`   | `patient.appointments.store`  |
| POST   | `/patient/appointments/{appointment}/cancel` | `patient.appointments.cancel` |

### Access Permissions
| Method | URL                                        | Name                       |
|--------|--------------------------------------------|----------------------------|
| GET    | `/patient/access`                          | `patient.access.index`     |
| PUT    | `/patient/access/type`                     | `patient.access.type`      |
| GET    | `/patient/access/requests`                 | `patient.access.requests`  |
| POST   | `/patient/access/requests/{request}/approve` | `patient.access.approve` |
| POST   | `/patient/access/requests/{request}/deny`  | `patient.access.deny`      |
| POST   | `/patient/access/requests/{request}/send-otp` | `patient.access.send-otp` |

### Health Tracker
| Method | URL                          | Name                       |
|--------|------------------------------|----------------------------|
| GET    | `/patient/health`            | `patient.health.index`     |
| GET    | `/patient/health/logs`       | `patient.health.logs`      |
| POST   | `/patient/health/logs`       | `patient.health.logs.store`|
| GET    | `/patient/health/chart/{type}` | `patient.health.chart`   |

---

## Admin Routes  `/admin/*`  [role:admin]

| GET  | `/admin/`                         | `admin.dashboard`            | Platform overview        |
| GET  | `/admin/users`                    | `admin.users.index`          | All users                |
| POST | `/admin/users/{user}/suspend`     | `admin.users.suspend`        | Suspend account          |
| POST | `/admin/users/{user}/activate`    | `admin.users.activate`       | Restore account          |
| GET  | `/admin/verification`             | `admin.verification.index`   | Doctor verification queue |
| POST | `/admin/verification/{doctor}/approve` | `admin.verification.approve` | Approve doctor      |
| GET  | `/admin/reports`                  | `admin.reports.index`        | Platform reports         |
| GET  | `/admin/reports/export/users`     | `admin.reports.export.users` | Export users to Excel    |
