@extends('layouts.patient')
@section('title', 'Add Family Member')
@section('page-title')
    <a href="{{ route('patient.family.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Family Members</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Add Member
@endsection

@section('content')
@include('patient.family._form')
@endsection
