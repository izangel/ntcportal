@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-3xl text-gray-900 leading-tight tracking-tight">
                {{ __('Clearance') }}
            </h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-400 font-medium">| What you need to complete clearance</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="py-10 bg-gray-50/50 min-h-screen">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

        @if(($isEmployeeView ?? false) && empty($employeeDeptOffice))
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-10 text-center">
                <h3 class="text-2xl font-semibold text-gray-900 mb-4">Department/Office not set</h3>
                <p class="text-gray-600">Department/Office not set</p>
            </div>
        @else
            @if($isEmployeeView ?? false)
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Department / Office</h3>
                    <p class="mt-2 text-gray-700">{{ $employeeDeptOffice->name }}</p>
                </div>
            @endif

            {{-- Requirements List --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Clearance</h3>

                <div class="grid gap-6 lg:grid-cols-2">
                    @foreach([
                        ['title' => 'Registrar\'s Office', 'items' => ['Grades Evaluation', 'Original Copy of PSA Birth Certificate', 'Form 137']],
                        ['title' => 'Guidance Office', 'items' => ['Photocopy of Career Guidance Certificate', 'Photocopy of GAD/MRP/Entrep Certificate', 'Photocopy of PESLA Certificate']],
                        ['title' => 'SHS Adviser', 'items' => ['Photocopy of NC/CARS']],
                    ] as $item)
                    <div class="border border-gray-100 rounded-3xl p-6 shadow-sm bg-white">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0 text-slate-500">
                                <i class="fas fa-building text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 mb-4">{{ $item['title'] }}</h4>
                                <ul class="space-y-2 text-gray-600 text-sm list-disc list-inside">
                                    @foreach($item['items'] as $requirement)
                                        <li>{{ $requirement }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-8 rounded-3xl border border-gray-100 bg-slate-50 p-6 text-sm text-gray-700">
                    <p class="font-semibold">Note:</p>
                    <p class="mt-2">For inquiries regarding other requirements, please coordinate with your respective advisers, coordinators, or heads.</p>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection