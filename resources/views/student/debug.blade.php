@extends('layouts.student')

@section('title', 'Debug Student Menu')

@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3>Debug Information</h3>
        </div>
        <div class="card-body">
            <h4>Variables Passed to View:</h4>
            
            <div class="alert alert-info">
                <strong>$student:</strong> {{ isset($student) ? 'YES' : 'NO' }}<br>
                @if(isset($student))
                    Username: {{ $student->username_std ?? 'N/A' }}<br>
                    Name: {{ $student->full_name ?? 'N/A' }}
                @endif
            </div>
            
            <div class="alert alert-warning">
                <strong>$myGroup:</strong> {{ isset($myGroup) ? 'YES' : 'NO' }}<br>
                @if(isset($myGroup) && $myGroup)
                    Group ID: {{ $myGroup->group_id }}<br>
                    Status: {{ $myGroup->status_group }}<br>
                    Subject: {{ $myGroup->subject_code }}
                @endif
            </div>
            
            <div class="alert alert-success">
                <strong>$isGroupLeader:</strong> {{ isset($isGroupLeader) ? ($isGroupLeader ? 'YES (true)' : 'NO (false)') : 'NOT SET' }}
            </div>
            
            <div class="alert alert-danger">
                <strong>$pendingInvitations:</strong> {{ isset($pendingInvitations) ? 'YES (' . $pendingInvitations->count() . ' items)' : 'NO' }}
            </div>
            
            <hr>
            
            <h4>Condition Check:</h4>
            <div class="alert alert-dark">
                <strong>Should show proposal card?</strong><br>
                - myGroup exists: {{ isset($myGroup) && $myGroup ? 'YES ✓' : 'NO ✗' }}<br>
                - status is approved: {{ isset($myGroup) && $myGroup && $myGroup->status_group === 'approved' ? 'YES ✓' : 'NO ✗' }}<br>
                - isGroupLeader: {{ isset($isGroupLeader) && $isGroupLeader ? 'YES ✓' : 'NO ✗' }}<br>
                <br>
                <strong class="text-{{ (isset($myGroup) && $myGroup && $myGroup->status_group === 'approved' && isset($isGroupLeader) && $isGroupLeader) ? 'success' : 'danger' }}">
                    FINAL RESULT: {{ (isset($myGroup) && $myGroup && $myGroup->status_group === 'approved' && isset($isGroupLeader) && $isGroupLeader) ? 'CARD SHOULD SHOW ✓✓✓' : 'CARD WILL NOT SHOW ✗✗✗' }}
                </strong>
            </div>
            
            <a href="{{ route('student.menu') }}" class="btn btn-primary">Back to Menu</a>
        </div>
    </div>
</div>
@endsection
