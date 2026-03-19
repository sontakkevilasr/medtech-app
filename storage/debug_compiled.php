<?php $__env->startSection('title', isset($record) ? 'Edit Record' : 'New Medical Record'); ?>
<?php $__env->startSection('page-title'); ?>
    <a href="<?php echo e(route('doctor.patients.history', $patient->id)); ?>" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">
        <?php echo e($patient->profile?->full_name ?? 'Patient'); ?>

    </a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    <?php echo e(isset($record) ? 'Edit Record' : 'New Record'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.field-label {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--txt-lt); display: block; margin-bottom: 5px;
}
.field-inp {
    width: 100%; padding: .6rem .9rem;
    border: 1.5px solid var(--warm-bd); border-radius: 10px;
    font-size: .875rem; color: var(--txt); background: #fff;
    outline: none; font-family: 'Outfit', sans-serif; transition: border-color .15s;
}
.field-inp:focus { border-color: var(--leaf); }
.field-ta {
    width: 100%; padding: .6rem .9rem;
    border: 1.5px solid var(--warm-bd); border-radius: 10px;
    font-size: .875rem; color: var(--txt); background: #fff;
    outline: none; font-family: 'Outfit', sans-serif; resize: vertical;
    transition: border-color .15s; line-height: 1.6;
}
.field-ta:focus { border-color: var(--leaf); }
.section-head {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1rem; font-weight: 500; color: var(--txt);
    padding-bottom: 10px; margin-bottom: 16px;
    border-bottom: 1.5px solid var(--warm-bd);
}
.vital-chip {
    display: flex; flex-direction: column; gap: 4px;
}
.vital-chip .vl { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--txt-lt); }
.vital-chip input {
    width: 100%; padding: .5rem .7rem;
    border: 1.5px solid var(--warm-bd); border-radius: 9px;
    font-size: .875rem; color: var(--txt); background: #fff;
    outline: none; font-family: 'Outfit', sans-serif;
}
.vital-chip input:focus { border-color: var(--leaf); }
.drop-zone {
    border: 2px dashed var(--warm-bd); border-radius: 12px;
    padding: 24px; text-align: center; cursor: pointer;
    transition: all .2s; background: var(--parch);
}
.drop-zone.over { border-color: var(--leaf); background: #eef5f3; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php $editing = isset($record); ?>

<div class="fade-in">
<form method="POST"
      action="<?php echo e($editing
        ? route('doctor.records.update', $record)
        : route('doctor.records.store', $patient->id)); ?>"
      enctype="multipart/form-data"
      x-data="recordForm()">
    <?php echo csrf_field(); ?>
    <?php if($editing): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

    <?php if($errors->any()): ?>
    <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:18px">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div style="font-size:.8rem;color:#dc2626">• <?php echo e($e); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

    
    <div style="display:flex;flex-direction:column;gap:18px">

        
        <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:20px 22px">
            <div class="section-head">Visit Information</div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px">
                <div>
                    <label class="field-label">Visit Date *</label>
                    <input type="date" name="visit_date" class="field-inp"
                           value="<?php echo e(old('visit_date', $record?->visit_date?->format('Y-m-d') ?? today()->format('Y-m-d'))); ?>"
                           max="<?php echo e(today()->format('Y-m-d')); ?>" required>
                </div>
                <div>
                    <label class="field-label">Visit Type *</label>
                    <select name="visit_type" class="field-inp">
                        <?php $__currentLoopData = ['consultation'=>'Consultation','follow_up'=>'Follow-up','emergency'=>'Emergency','procedure'=>'Procedure','teleconsultation'=>'Teleconsultation']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php echo e(old('visit_type', $record?->visit_type ?? 'consultation') === $val ? 'selected':''); ?>><?php echo e($lbl); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="field-label">For</label>
                    <select name="family_member_id" class="field-inp">
                        <option value=""><?php echo e($patient->profile?->full_name ?? 'Patient'); ?> (self)</option>
                        <?php $__currentLoopData = $patient->familyMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($fm->id); ?>"
                                <?php echo e(old('family_member_id', $record?->family_member_id ?? $selectedMemberId) == $fm->id ? 'selected':''); ?>>
                            <?php echo e($fm->full_name); ?> (<?php echo e($fm->relation); ?>)
                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:14px">
                <label class="field-label">Chief Complaint *</label>
                <textarea name="chief_complaint" class="field-ta" rows="2"
                          placeholder="Patient's main complaints in their own words…"
                          required><?php echo e(old('chief_complaint', $record?->chief_complaint)); ?></textarea>
            </div>

            <div>
                <label class="field-label">Diagnosis *</label>
                <textarea name="diagnosis" class="field-ta" rows="3"
                          placeholder="ICD-10 or descriptive diagnosis…"
                          required><?php echo e(old('diagnosis', $record?->diagnosis)); ?></textarea>
            </div>
        </div>

        
        <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:20px 22px">
            <div class="section-head">Vitals</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
                <?php
                $vitals = [
                    'height'      => ['Height',      'cm  / ft-in', 'e.g. 165cm'],
                    'weight'      => ['Weight',      'kg',          'e.g. 68'],
                    'bp'          => ['Blood Pressure','mmHg',      'e.g. 120/80'],
                    'pulse'       => ['Pulse',       'bpm',         'e.g. 72'],
                    'temperature' => ['Temperature', '°C',          'e.g. 37.0'],
                    'spo2'        => ['SpO₂',        '%',           'e.g. 98'],
                ];
                ?>
                <?php $__currentLoopData = $vitals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$label, $unit, $ph]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="vital-chip">
                    <span class="vl"><?php echo e($label); ?> <span style="font-weight:400;text-transform:none">(<?php echo e($unit); ?>)</span></span>
                    <input type="<?php echo e(in_array($key,['weight','pulse','spo2']) ? 'number' : 'text'); ?>"
                           name="vitals[<?php echo e($key); ?>]"
                           value="<?php echo e(old('vitals.'.$key, $record?->vitals[$key] ?? '')); ?>"
                           placeholder="<?php echo e($ph); ?>"
                           step="<?php echo e(in_array($key,['weight','temperature']) ? '0.1' : '1'); ?>">
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:20px 22px">
            <div class="section-head">Clinical Notes</div>

            <div style="margin-bottom:14px">
                <label class="field-label">Examination Notes</label>
                <textarea name="examination_notes" class="field-ta" rows="4"
                          placeholder="General examination, systemic findings…"><?php echo e(old('examination_notes', $record?->examination_notes)); ?></textarea>
            </div>

            <div style="margin-bottom:14px">
                <label class="field-label">Treatment Plan</label>
                <textarea name="treatment_plan" class="field-ta" rows="4"
                          placeholder="Medications, procedures, lifestyle advice…"><?php echo e(old('treatment_plan', $record?->treatment_plan)); ?></textarea>
            </div>

            <div>
                <label class="field-label" style="display:flex;align-items:center;gap:6px">
                    Doctor's Private Notes
                    <span style="font-size:.65rem;padding:1px 7px;border-radius:20px;background:#fef9ec;color:#92400e;border:1px solid #fde68a;font-weight:600">Private</span>
                </label>
                <textarea name="doctor_notes" class="field-ta" rows="3"
                          placeholder="Internal notes — not visible to patient…"><?php echo e(old('doctor_notes', $record?->doctor_notes)); ?></textarea>
            </div>
        </div>

    </div>

    
    <div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

        
        <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:16px 18px">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Patient</div>
            <div style="display:flex;align-items:center;gap:11px">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--leaf);display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:700;color:#fff;flex-shrink:0">
                    <?php echo e(strtoupper(substr($patient->profile?->full_name ?? 'P', 0, 1))); ?>

                </div>
                <div>
                    <div style="font-weight:600;font-size:.9rem;color:var(--txt)"><?php echo e($patient->profile?->full_name); ?></div>
                    <div style="font-size:.75rem;color:var(--txt-lt)">
                        <?php echo e($patient->profile?->date_of_birth ? 'Age ' . $patient->profile->date_of_birth->age . ' · ' : ''); ?><?php echo e(ucfirst($patient->profile?->gender ?? '')); ?><?php echo e($patient->profile?->blood_group ? ' · ' . $patient->profile->blood_group : ''); ?>

                    </div>
                </div>
            </div>
        </div>

        
        <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:16px 18px">
            <label class="field-label" style="margin-bottom:8px">Follow-up Date</label>
            <input type="date" name="follow_up_date" class="field-inp"
                   value="<?php echo e(old('follow_up_date', $record?->follow_up_date?->format('Y-m-d'))); ?>"
                   min="<?php echo e(today()->addDay()->format('Y-m-d')); ?>">
            <div style="font-size:.72rem;color:var(--txt-lt);margin-top:6px">
                Patient will see this date as their next appointment reminder.
            </div>
        </div>

        
        <?php if(!$editing): ?>
        <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:16px 18px"
             x-data="{ files: [], isDragging: false }">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">
                Attachments
                <span style="font-weight:400;text-transform:none;font-size:.65rem">(lab reports, scans — max 5MB each)</span>
            </div>

            <div class="drop-zone"
                 :class="isDragging ? 'over' : ''"
                 x-on:dragover.prevent="isDragging=true"
                 x-on:dragleave="isDragging=false"
                 x-on:drop.prevent="isDragging=false; handleFiles($event.dataTransfer.files)"
                 x-on:click="$refs.fileInput.click()">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color:var(--txt-lt);margin:0 auto 8px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div style="font-size:.8rem;color:var(--txt-lt)">Drop files here or <span style="color:var(--leaf);font-weight:600">browse</span></div>
                <div style="font-size:.7rem;color:var(--txt-lt);margin-top:3px">PDF, JPG, PNG — max 5MB</div>
                <input type="file" x-ref="fileInput" name="attachments[]"
                       multiple accept=".pdf,.jpg,.jpeg,.png,.webp"
                       style="display:none"
                       x-on:change="handleFiles($event.target.files)">
            </div>

            <div style="margin-top:10px;display:flex;flex-direction:column;gap:5px">
                <template x-for="(f, i) in files" :key="i">
                    <div style="display:flex;align-items:center;gap:8px;padding:6px 10px;background:var(--parch);border-radius:8px;font-size:.78rem">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:var(--txt-lt)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span x-text="f.name" style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--txt-md)"></span>
                        <span x-text="formatSize(f.size)" style="color:var(--txt-lt);flex-shrink:0"></span>
                        <button type="button" x-on:click="removeFile(i)"
                                style="width:18px;height:18px;border:none;background:transparent;color:var(--txt-lt);cursor:pointer;font-size:.85rem;line-height:1;padding:0">×</button>
                    </div>
                </template>
            </div>
        </div>
        <?php else: ?>
        
        <?php if($record->attachments): ?>
        <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:16px 18px">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Attachments</div>
            <?php $__currentLoopData = $record->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="display:flex;align-items:center;gap:8px;padding:7px 10px;background:var(--parch);border-radius:8px;font-size:.78rem;margin-bottom:5px">
                <span style="flex:1;color:var(--txt-md)"><?php echo e($att['name']); ?></span>
                <a href="<?php echo e(Storage::url($att['path'])); ?>" target="_blank"
                   style="color:var(--leaf);text-decoration:none;font-size:.72rem">View</a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        
        <button type="submit"
                style="width:100%;padding:.8rem;background:var(--leaf);color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:opacity .15s"
                onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
            <?php echo e($editing ? '✓ Save Changes' : '✓ Create Record'); ?>

        </button>
        <a href="<?php echo e(route('doctor.patients.history', $patient->id)); ?>"
           style="display:block;text-align:center;font-size:.8rem;color:var(--txt-lt);text-decoration:none;padding:4px">
            Cancel
        </a>
    </div>

    </div>
</form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function recordForm() {
    return {
        files: [],
        handleFiles(fileList) {
            Array.from(fileList).forEach(f => {
                if (f.size <= 5 * 1024 * 1024) this.files.push(f);
            });
            // Sync to actual input
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        },
        removeFile(index) {
            this.files.splice(index, 1);
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            this.$refs.fileInput.files = dt.files;
        },
        formatSize(bytes) {
            return bytes < 1024 * 1024
                ? (bytes / 1024).toFixed(0) + ' KB'
                : (bytes / 1024 / 1024).toFixed(1) + ' MB';
        }
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.doctor', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>