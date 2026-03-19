@extends('layouts.guest')
@section('title', 'Select Your Role')

@section('content')
<div x-data="{ role: '', loading: false }">

    <!-- Progress -->
    <div style="display:flex;justify-content:center;gap:6px;margin-bottom:1.75rem">
        <div style="width:8px;height:8px;border-radius:50%;background:var(--p);transform:scale(1.2)"></div>
        <div style="width:8px;height:8px;border-radius:50%;background:var(--bd)"></div>
        <div style="width:8px;height:8px;border-radius:50%;background:var(--bd)"></div>
    </div>

    <!-- Heading -->
    <div style="text-align:center;margin-bottom:1.75rem">
        <h1 class="font-display" style="font-size:1.5rem;color:var(--tx);letter-spacing:-.02em;margin-bottom:6px">I am a…</h1>
        <p style="font-size:.875rem;color:var(--mt)">This helps us personalise your experience</p>
    </div>

    <!-- Role Cards -->
    <div style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:1.5rem">

        <!-- Doctor -->
        <label style="cursor:pointer">
            <input type="radio" name="role" value="doctor" x-model="role" style="display:none">
            <div class="role-card" :class="role === 'doctor' ? 'sel' : ''"
                 style="display:flex;align-items:center;gap:1rem">
                <div style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0"
                     :style="role==='doctor' ? 'background:var(--p);' : 'background:#f0f4f6;'">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         :stroke="role==='doctor' ? '#fff' : 'var(--p)'"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </div>
                <div style="flex:1">
                    <div style="font-weight:600;color:var(--tx);margin-bottom:2px">Doctor / Specialist</div>
                    <div style="font-size:.8125rem;color:var(--mt)">Manage patients, prescriptions &amp; appointments</div>
                </div>
                <div x-show="role==='doctor'"
                     style="width:20px;height:20px;border-radius:50%;background:var(--p);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
            </div>
        </label>

        <!-- Patient -->
        <label style="cursor:pointer">
            <input type="radio" name="role" value="patient" x-model="role" style="display:none">
            <div class="role-card" :class="role === 'patient' ? 'sel' : ''"
                 style="display:flex;align-items:center;gap:1rem">
                <div style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0"
                     :style="role==='patient' ? 'background:var(--p);' : 'background:#f0f4f6;'">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         :stroke="role==='patient' ? '#fff' : 'var(--p)'"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div style="flex:1">
                    <div style="font-weight:600;color:var(--tx);margin-bottom:2px">Patient / Family</div>
                    <div style="font-size:.8125rem;color:var(--mt)">Track health, prescriptions &amp; appointments</div>
                </div>
                <div x-show="role==='patient'"
                     style="width:20px;height:20px;border-radius:50%;background:var(--p);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
            </div>
        </label>
    </div>

    <!-- Submit -->
    <form method="POST" action="{{ route('auth.setup.role.save') }}" x-ref="form">
        @csrf
        <input type="hidden" name="role" :value="role">

        <button type="button" @click="if(role){loading=true;$refs.form.submit()}"
                :disabled="!role || loading" class="btn-p">
            <span x-show="loading" class="spinner"></span>
            <span x-show="!loading">Continue</span>
            <svg x-show="!loading" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
            </svg>
        </button>
    </form>

    <p x-show="!role" style="text-align:center;font-size:.8125rem;color:#94a3b8;margin-top:.75rem">
        Please select your role to continue
    </p>

</div>
@endsection
