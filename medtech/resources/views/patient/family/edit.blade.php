@extends('layouts.patient')
@section('title', 'Edit '.$member->full_name)
@section('page-title')
    <a href="{{ route('patient.family.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Family Members</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    <a href="{{ route('patient.family.show', $member->id) }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">{{ $member->full_name }}</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Edit
@endsection

@section('content')
@include('patient.family._form')
@endsection
