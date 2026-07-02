@extends('layouts.admin')

@section('content')
    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold mb-6 text-gray-800">Submit New Leave Application</h2>
            
            <livewire:leave-application-form :isHrRecordingMode="false" />
        </div>
    </div>
@endsection