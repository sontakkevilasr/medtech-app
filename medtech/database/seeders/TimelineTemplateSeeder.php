<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimelineTemplate;
use App\Models\TimelineMilestone;

class TimelineTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPregnancyTimeline();
        $this->seedPediatricVaccinationTimeline();
        $this->seedIvfTimeline();
        $this->seedDentalOrthoTimeline();

        $this->command->info('✔ 4 system timeline templates seeded (Pregnancy, Pediatric, IVF, Dental Ortho)');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. OBG — 9-Month Pregnancy Journey (week-by-week)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedPregnancyTimeline(): void
    {
        $template = TimelineTemplate::create([
            'doctor_user_id'       => null,
            'specialty_type'       => 'obstetrics',
            'title'                => '9-Month Pregnancy Journey',
            'description'          => 'Complete week-by-week guide for expectant mothers — visits, scans, tests, and precautions.',
            'total_duration_days'  => 280,
            'duration_unit'        => 'week',
            'is_system_template'   => true,
            'is_active'            => true,
        ]);

        $milestones = [
            [4,  'week', 'Pregnancy Confirmed', 'visit', 'Confirm pregnancy with urine/blood HCG test. Register with your OBG doctor.', 'Avoid alcohol, smoking, raw fish. Start folic acid 400mcg daily.', null, '🌱', '#A8E6CF', [3,1]],
            [6,  'week', 'First OBG Consultation', 'visit', 'First prenatal visit. Medical history, blood pressure, weight, urine test. Prescribe prenatal vitamins.', 'Avoid heavy lifting. Take folic acid, iron, calcium as prescribed.', 'Light walking 20 min/day is safe.', '👩‍⚕️', '#88D8B0', [3,1]],
            [8,  'week', 'Transvaginal Ultrasound', 'scan', 'Confirm fetal heartbeat, gestational age, rule out ectopic pregnancy.', 'Rest if any spotting. Avoid stress.', null, '🔬', '#FFD3B6', [3,1]],
            [10, 'week', 'Blood Tests Panel', 'test', 'CBC, blood group, Rh factor, VDRL, HIV, Hepatitis B, thyroid (TSH), blood sugar.', 'Note any unusual symptoms like severe nausea or spotting.', null, '🩸', '#FFAAA5', [3,1]],
            [12, 'week', 'NT Scan (Nuchal Translucency)', 'scan', 'Screen for chromosomal abnormalities (Down syndrome). Combined with maternal serum screening (Double Marker).', 'Results take 5–7 days. Do not panic — positive screen needs confirmatory NIPT.', null, '📡', '#FF8B94', [7,3,1]],
            [14, 'week', 'Second Trimester Begins', 'info', 'Baby is fully formed. Morning sickness usually subsides. Energy improves.', 'Start sleeping on left side. Avoid lying flat on back.', 'Gentle yoga, swimming safe now.', '🌤️', '#C7E9B0', [3,1]],
            [16, 'week', 'Routine Check-up', 'visit', 'Weight, BP, fundal height, fetal heartbeat check. Review blood test reports.', 'Start wearing maternity clothes. Watch for signs of UTI.', null, '⚖️', '#88D8B0', [3,1]],
            [18, 'week', 'Anomaly Scan (Level 2)', 'scan', 'Detailed scan to check all fetal organs, amniotic fluid, placenta position. Most important scan of pregnancy.', 'Drink 4–5 glasses of water 1 hour before scan for full bladder.', null, '🧬', '#FFD3B6', [7,3,1]],
            [20, 'week', 'Mid-Pregnancy Check-up', 'visit', 'Fundal height, fetal heartbeat, BP check. Review anomaly scan report.', 'Watch for round ligament pain (normal). Call doctor if severe.', 'Kegel exercises recommended.', '📋', '#88D8B0', [3,1]],
            [24, 'week', 'Glucose Challenge Test (GCT)', 'test', 'Screen for gestational diabetes. 50g glucose drink, blood drawn after 1 hour.', 'If GCT > 140mg/dL, proceed to 3-hour OGTT.', null, '🍬', '#FFAAA5', [7,3,1]],
            [28, 'week', 'Third Trimester Begins', 'visit', 'Growth scan, cervical length check. Rh-negative mothers get Anti-D injection. CBC repeat.', 'Baby movements should be felt 10 times/day. Keep kick count diary.', 'Reduce high-impact exercise. Avoid long travel.', '🌙', '#FFD3B6', [7,3,1]],
            [30, 'week', 'Growth Scan', 'scan', 'Check fetal weight, position, amniotic fluid index (AFI), placental grading.', 'Left lateral sleep position strictly. Elevate feet when sitting.', null, '📏', '#FFD3B6', [7,3,1]],
            [32, 'week', 'Routine Check-up', 'visit', 'BP, weight, fundal height, presentation check. Plan for delivery discussion.', 'Pack hospital bag. Discuss birth plan with doctor.', 'Stop heavy exercise.', '🏥', '#88D8B0', [7,3,1]],
            [34, 'week', 'Biophysical Profile (BPP)', 'scan', 'Assess fetal well-being: movements, tone, breathing, AFI. Score 8/10 is normal.', 'Report any decrease in fetal movements immediately.', null, '📊', '#FFD3B6', [7,3,1]],
            [36, 'week', 'Group B Strep Swab + Check-up', 'test', 'Vaginal-rectal swab for Group B Streptococcus. Pelvic exam for cervical dilation.', 'Baby may "drop" (lightening). Increased pelvic pressure is normal.', null, '🔍', '#FFAAA5', [7,3,1]],
            [37, 'week', 'Full-Term — Weekly Visits Begin', 'visit', 'Weekly check-ups from now. Cervical check, NST if required. Discuss induction plan.', 'Watch for: regular contractions, water breaking, bloody show. Go to hospital immediately.', null, '⏰', '#FF8B94', [7,3,1]],
            [38, 'week', 'Weekly Check-up', 'visit', 'NST (Non-Stress Test), cervical ripening assessment. Discuss signs of labour.', 'Rest. Avoid stress. Stay near hospital.', null, '🕐', '#FF8B94', [7,3,1]],
            [39, 'week', 'Weekly Check-up', 'visit', 'Final growth assessment. Review delivery plan (vaginal vs C-section). Hospital admission if required.', 'Have car ready. Hospital bag packed. Inform family.', null, '🚗', '#FF8B94', [7,3,1]],
            [40, 'week', 'Due Date / Delivery', 'visit', 'Expected Date of Delivery. If no spontaneous labour, discuss induction or elective C-section.', 'Stay calm. Labour can start any time between 38–42 weeks.', null, '👶', '#FFD700', [7,3,1]],
            [42, 'week', 'Post-natal Visit', 'visit', 'Mother: BP, lochia, wound check (if C-section). Baby: weight, jaundice, feeding check.', 'Start contraception discussion. Breastfeeding support.', 'Pelvic floor exercises.', '🤱', '#C7E9B0', [3,1]],
        ];

        foreach ($milestones as $i => [$offset, $unit, $title, $type, $desc, $precautions, $exercise, $icon, $color, $reminders]) {
            TimelineMilestone::create([
                'template_id'         => $template->id,
                'title'               => $title,
                'description'         => $desc,
                'offset_value'        => $offset,
                'offset_unit'         => $unit,
                'milestone_type'      => $type,
                'precautions'         => $precautions,
                'exercise_advice'     => $exercise,
                'reminder_days_before'=> $reminders,
                'icon'                => $icon,
                'color'               => $color,
                'sort_order'          => $i,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Pediatrics — Vaccination Chart (0–5 years, India IAP schedule)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedPediatricVaccinationTimeline(): void
    {
        $template = TimelineTemplate::create([
            'doctor_user_id'       => null,
            'specialty_type'       => 'pediatrics',
            'title'                => 'Child Vaccination Schedule (IAP)',
            'description'          => 'Indian Academy of Pediatrics (IAP) recommended immunization schedule from birth to 5 years.',
            'total_duration_days'  => 1825, // 5 years
            'duration_unit'        => 'day',
            'is_system_template'   => true,
            'is_active'            => true,
        ]);

        $milestones = [
            // [offset_days, title, vaccines, precautions, reminders_before]
            [0,   'Birth Vaccines', 'vaccination', 'BCG (tuberculosis), OPV-0 (oral polio), Hepatitis B-1. Vitamin K injection.', 'Normal to have small lump at BCG injection site. Do not rub.', [3,1]],
            [42,  '6 Weeks — First Round', 'vaccination', 'DTwP/DTaP-1 (diphtheria, tetanus, pertussis), IPV-1, Hepatitis B-2, Hib-1, Rotavirus-1, PCV-1.', 'Baby may have mild fever (99–100°F). Give paracetamol if prescribed.', [7,3,1]],
            [70,  '10 Weeks — Second Round', 'vaccination', 'DTwP/DTaP-2, IPV-2, Hib-2, Rotavirus-2, PCV-2.', 'Keep injection site clean and dry. Mild soreness is normal.', [7,3,1]],
            [98,  '14 Weeks — Third Round', 'vaccination', 'DTwP/DTaP-3, IPV-3, Hib-3, Rotavirus-3, PCV-3, OPV-1.', 'Ensure complete 3-dose series is completed.', [7,3,1]],
            [180, '6 Months — Booster + New', 'vaccination', 'Hepatitis B-3 (if not given at birth), Influenza-1 (annual thereafter), OPV-2.', 'Influenza vaccine to be repeated every year.', [7,3,1]],
            [270, '9 Months', 'vaccination', 'OPV-3, MMR-1 (measles, mumps, rubella), Typhoid conjugate vaccine (TCV)-1.', 'MMR may cause mild rash 5–12 days after vaccination. Normal.', [7,3,1]],
            [365, '1 Year — Check-up', 'visit', 'Growth assessment, developmental milestones review. Weight, height, head circumference.', 'Ensure child is walking or attempting by 12–15 months.', [7,3,1]],
            [396, '13 Months', 'vaccination', 'Hepatitis A-1, Varicella (chickenpox)-1.', 'After varicella vaccine, mild rash may occur. Avoid aspirin.', [7,3,1]],
            [456, '15 Months', 'vaccination', 'MMR-2, Varicella-2, PCV booster, Hib booster.', 'This completes MMR series. Important catch-up deadline.', [7,3,1]],
            [548, '18 Months', 'vaccination', 'DTwP/DTaP booster-1, IPV booster, Hepatitis A-2, Typhoid booster.', 'Keep vaccination card updated.', [7,3,1]],
            [730, '2 Years', 'visit', 'Growth and developmental review. Language, motor skills, social development.', 'Dental check. Start fluoride toothpaste.', [7,3]],
            [913, '2.5 Years', 'vaccination', 'Typhoid booster (if not given at 18 months). Annual influenza vaccine.', null, [7,3,1]],
            [1095,'3 Years', 'visit', 'Annual health check. Vision screening, hearing check, dental exam.', 'Preschool readiness evaluation.', [7,3]],
            [1460,'4 Years', 'vaccination', 'DTwP/DTaP booster-2, OPV booster, MMR catch-up if missed.', 'Prepare for school immunization requirement.', [7,3,1]],
            [1825,'5 Years — School Entry', 'vaccination', 'Varicella booster if missed. Annual influenza vaccine. Meningococcal (if not given).', 'Ensure all vaccines complete before school admission.', [30,7,3,1]],
        ];

        foreach ($milestones as $i => [$offset, $title, $type, $desc, $precautions, $reminders]) {
            TimelineMilestone::create([
                'template_id'          => $template->id,
                'title'                => $title,
                'description'          => $desc,
                'offset_value'         => $offset,
                'offset_unit'          => 'day',
                'milestone_type'       => $type,
                'precautions'          => $precautions,
                'reminder_days_before' => $reminders,
                'icon'                 => $type === 'vaccination' ? '💉' : '👶',
                'color'                => $type === 'vaccination' ? '#B5EAD7' : '#FFDAC1',
                'sort_order'           => $i,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. IVF Treatment Journey
    // ─────────────────────────────────────────────────────────────────────────
    private function seedIvfTimeline(): void
    {
        $template = TimelineTemplate::create([
            'doctor_user_id'       => null,
            'specialty_type'       => 'ivf',
            'title'                => 'IVF Treatment Cycle',
            'description'          => 'Step-by-step IVF cycle from initial consultation to embryo transfer and pregnancy test.',
            'total_duration_days'  => 42,
            'duration_unit'        => 'day',
            'is_system_template'   => true,
            'is_active'            => true,
        ]);

        $milestones = [
            [0,  'Initial Consultation & Workup', 'visit', 'Semen analysis, AMH, AFC count, HSG/SIS for uterine evaluation. Review previous history.', 'Both partners must attend. Bring all previous reports.', [3,1]],
            [3,  'Pre-cycle Blood Tests', 'test', 'Day 2/3 FSH, LH, E2, AFC ultrasound. Confirm baseline status before stimulation.', 'Report on Day 2 or Day 3 of menstrual cycle.', [1]],
            [5,  'Ovarian Stimulation Begins', 'medication', 'Daily FSH/HMG injections. Stimulate multiple follicle development. Duration 8–14 days.', 'Self-inject as instructed. Refrigerate medications. Note injection site reactions.', [1]],
            [9,  'Monitoring Scan — Day 1', 'scan', 'Transvaginal ultrasound to count and measure developing follicles. Adjust medication dose.', 'Come with full bladder. Report any severe bloating or pain immediately.', [1]],
            [12, 'Monitoring Scan — Day 2', 'scan', 'Continue monitoring follicle growth. Check E2 levels. Adjust trigger timing.', 'Avoid strenuous activity. Stay hydrated.', [1]],
            [14, 'Trigger Injection (hCG / GnRH agonist)', 'medication', 'Final maturation trigger when follicles ≥18mm. Egg retrieval exactly 34–36 hours later.', 'Timing is CRITICAL. Inject at exact prescribed time. Set alarm.', [1]],
            [16, 'Egg Retrieval (OPU)', 'procedure', 'Transvaginal oocyte pickup under sedation. 15–30 minute procedure. Rest for 2–3 hours after.', 'Do not eat or drink 6 hours before. Bring companion. Rest at home after.', [3,1]],
            [16, 'Sperm Collection / ICSI', 'procedure', 'Male partner provides sample same day. ICSI performed in lab to fertilise eggs.', 'Male partner: abstain 2–5 days prior. No alcohol/tobacco 3 months before.', [3,1]],
            [18, 'Fertilization Report', 'info', 'Embryologist calls with fertilization result. Normal fertilization: 60–80% of mature eggs.', 'Do not panic if fewer eggs fertilize — quality matters more than quantity.', [1]],
            [21, 'Day 3 Embryo Assessment', 'info', 'Embryos checked for cell count (8-cell ideal) and grade. Decision: Day 3 or Day 5 transfer.', 'Rest, avoid stress. Continue luteal support medications.', [1]],
            [23, 'Day 5 Blastocyst Assessment (if applicable)', 'info', 'Blastocyst grading. Good blastocysts: 3AA–4AB. Excess embryos vitrified (frozen).', 'Higher success rates with blastocyst transfer.', [1]],
            [23, 'Embryo Transfer', 'procedure', 'Soft catheter transfers 1–2 embryos into uterus. 15-minute procedure. No sedation needed.', 'Full bladder required. Rest 15 min after. Bed rest not needed — normal activity OK.', [3,1]],
            [24, 'Post-Transfer — Luteal Support', 'medication', 'Progesterone pessaries/injections for 14+ days. Estradiol if FET. Continue prenatal vitamins.', 'Do not stop medications unless instructed. Avoid: hot baths, heavy lifting, intercourse.', [1]],
            [37, 'Beta HCG Blood Test (14 days post transfer)', 'test', 'Quantitative serum HCG. Positive: >25 mIU/mL. Result determines next steps.', 'Do NOT do urine pregnancy test before this — misleading. Wait for blood test.', [3,1]],
            [42, 'Confirmatory Scan (if HCG positive)', 'scan', 'Transvaginal ultrasound to confirm intrauterine pregnancy, fetal pole and heartbeat.', 'Heartbeat usually visible at 6–7 weeks. Transition to OBG care.', [3,1]],
        ];

        foreach ($milestones as $i => [$offset, $title, $type, $desc, $precautions, $reminders]) {
            TimelineMilestone::create([
                'template_id'          => $template->id,
                'title'                => $title,
                'description'          => $desc,
                'offset_value'         => $offset,
                'offset_unit'          => 'day',
                'milestone_type'       => $type,
                'precautions'          => $precautions,
                'reminder_days_before' => $reminders,
                'icon'                 => match($type) {
                    'visit'      => '👩‍⚕️',
                    'scan'       => '📡',
                    'test'       => '🩸',
                    'medication' => '💊',
                    'procedure'  => '⚕️',
                    default      => 'ℹ️',
                },
                'color'     => '#E2B4F0',
                'sort_order'=> $i,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Dental Orthodontic Treatment (Braces / Aligner journey)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedDentalOrthoTimeline(): void
    {
        $template = TimelineTemplate::create([
            'doctor_user_id'       => null,
            'specialty_type'       => 'dental',
            'title'                => 'Orthodontic Treatment — Braces Journey',
            'description'          => 'Complete orthodontic treatment timeline for patients undergoing braces or aligner therapy.',
            'total_duration_days'  => 730, // ~24 months
            'duration_unit'        => 'month',
            'is_system_template'   => true,
            'is_active'            => true,
        ]);

        $milestones = [
            [0,  'month', 'Initial Orthodontic Consultation', 'visit', 'Clinical examination, X-rays (OPG + lateral cephalogram), dental photographs, impressions/scan.', 'Bring all previous dental records. Note all complaints.', [3,1]],
            [1,  'month', 'Treatment Plan Presentation', 'visit', 'Doctor explains treatment plan, duration, cost, type of braces (metal / ceramic / lingual / aligners).', 'Ask all questions. Confirm payment plan.', [3,1]],
            [1,  'month', 'Extractions (if required)', 'procedure', 'Pre-orthodontic extractions to create space. Usually premolars. Local anesthesia used.', 'Soft food diet 24 hours. No hot beverages. Pain normal for 2–3 days.', [3,1]],
            [2,  'month', 'Braces Placement / Aligner Start', 'procedure', 'Bond brackets, place arch wire. Aligners: issue set 1–10 trays. Duration varies: 12–24 months.', 'Mild soreness 3–5 days after each wire change. Use orthodontic wax on sharp brackets.', [3,1]],
            [3,  'month', 'First Check-up (4 weeks)', 'visit', 'Wire activation/change. Check tooth movement. Aligner patients: issue next set.', 'Carry wax, orthodontic brush kit. Report any bracket debonding.', [7,3,1]],
            [4,  'month', 'Second Check-up', 'visit', 'Review alignment progress. Adjust wire / switch aligners. Before-after comparison photos.', 'Strict oral hygiene — brush after every meal. Floss daily.', [7,3,1]],
            [6,  'month', 'Mid-Treatment Review', 'scan', 'Panoramic X-ray to review root resorption, bone levels, tooth movement progress.', 'Do not miss any appointments — gaps delay treatment.', [7,3,1]],
            [9,  'month', 'Quarterly Check-up', 'visit', 'Wire change / next aligner tray. Assess bite correction, spaces closing.', 'Avoid hard / sticky foods: peanuts, caramel, hard candy, crusty bread.', [7,3,1]],
            [12, 'month', '1-Year Progress Review', 'visit', 'Progress photos, X-rays, study models. Patient motivation counselling.', 'Retention planning discussion begins.', [7,3,1]],
            [18, 'month', 'Final Detailing Phase', 'visit', 'Fine-tuning occlusion, closing residual spaces, torque adjustment. Finishing elastics if needed.', 'Wear elastics (rubber bands) as instructed — non-compliance delays finish.', [7,3,1]],
            [22, 'month', 'Debond Planning Visit', 'visit', 'Confirm treatment is complete. Impressions for retainer fabrication.', 'Retainer is essential — teeth will relapse without it.', [7,3,1]],
            [24, 'month', 'Debonding Day — Braces Removed!', 'procedure', 'Remove brackets and wires. Polish teeth. Fit fixed lingual retainer (lower). Issue removable retainer (upper).', 'Wear removable retainer 22 hours/day for first 6 months, then night-only lifelong.', [7,3,1]],
            [27, 'month', 'Retention Check (3 months post)', 'visit', 'Check retainer fit, tooth stability, gum health. Aligner patients: Vivera retainer issue.', 'Report immediately if retainer breaks or feels loose.', [7,3,1]],
            [36, 'month', 'Annual Retention Review', 'visit', 'Confirm stability. Photos for long-term record. Discharge if stable.', 'Night retainer is lifelong. Never stop without doctor\'s advice.', [14,7,3]],
        ];

        foreach ($milestones as $i => [$offset, $unit, $title, $type, $desc, $precautions, $reminders]) {
            TimelineMilestone::create([
                'template_id'          => $template->id,
                'title'                => $title,
                'description'          => $desc,
                'offset_value'         => $offset,
                'offset_unit'          => $unit,
                'milestone_type'       => $type,
                'precautions'          => $precautions,
                'reminder_days_before' => $reminders,
                'icon'                 => match($type) {
                    'visit'     => '🦷',
                    'procedure' => '⚙️',
                    'scan'      => '📷',
                    default     => 'ℹ️',
                },
                'color'      => '#B5D8F7',
                'sort_order' => $i,
            ]);
        }
    }
}
