@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto py-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">

        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                Advisory Details
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                View the complete memorandum or advisory information.
            </p>
        </div>

        <div class="flex gap-3">

            <a href="{{ route('admin.memos.index') }}"
                class="inline-flex items-center px-5 py-2.5 rounded-lg bg-white text-black hover:bg-slate-700 transition">

                <i class="fa-solid fa-arrow-left mr-2"></i>

                Back

            </a>

            @if(auth()->check() && auth()->user()->roles->pluck('name')->intersect(['teacher', 'staff'])->isNotEmpty())


            <button onclick="if(confirm('Delete this advisory?')) document.getElementById('delete-form').submit();"
                class="inline-flex items-center px-5 py-2.5 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">

                <i class="fa-regular fa-trash-can mr-2"></i>

                Delete

            </button>

            <form id="delete-form" action="{{ route('admin.memos.destroy',$advisory->id) }}" method="POST"
                style="display:none;">

                @csrf
                @method('DELETE')

            </form>

            @endif

            {{-- Success Message --}}
            @if(session('success'))
            <div class="px-4 py-2 rounded-lg bg-green-100 text-green-700 border border-green-300">
                {{ session('success') }}
            </div>
            @endif

            {{-- Information Message --}}
            @if(session('info'))
            <div class="px-4 py-2 rounded-lg bg-blue-100 text-blue-700 border border-blue-300">
                {{ session('info') }}
            </div>
            @endif

            {{-- Print Button --}}
            <button onclick="window.print()"
                class="no-print inline-flex items-center px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">

                <i class="fa-solid fa-print mr-2"></i>

                Click to Print

            </button>

            {{-- Faculty / Staff Acknowledge Button --}}
            @unless(
            Auth::user()->hasRole('admin') ||
            Auth::user()->hasRole('academic_head') ||
            Auth::user()->hasRole('registrar') ||
            Auth::user()->hasRole('hr')
            )

            @if(!$acknowledged)


            <form action="{{ route('admin.acknowledgements.store', ['advisory_no' => $advisory->id]) }}" method="POST">
                @csrf

                <button type="submit"
                    class="inline-flex items-center px-5 py-2.5 rounded-lg bg-green-600 text-white hover:bg-green-700 transition"
                    onclick="this.disabled=true; this.innerHTML='Submitting...'; this.form.submit();">
                    Acknowledge this Letter




                </button>
            </form>




            @endif

            @endunless

            </script>

        </div>

    </div>



    {{-- Advisory Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- Card Header --}}
        <div class="px-8 py-6 border-b bg-gray-50">

            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">

                <div>

                    <span class="text-sm font-semibold text-blue-600">
                        {{ $advisory->advisory_no }}
                    </span>

                    <h2 class="text-3xl font-bold text-gray-900 mt-2">
                        {{ $advisory->subject }}
                    </h2>

                </div>

                <div class="text-sm text-gray-500">

                    <div>
                        <strong>Date:</strong>
                        {{ optional($advisory->date)->format('F d, Y') }}
                    </div>

                </div>

            </div>

        </div>



        {{-- Information Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8 border-b">

            {{-- From --}}
            <div>

                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                    From
                </h4>

                <div class="text-gray-900 font-semibold">
                    {{ $advisory->from }}
                </div>

            </div>



            {{-- Target Audience --}}
            <div>

                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                    To
                </h4>

                @php

                $targets = $advisory->to;

                if(is_null($targets)){
                $targets = [];
                }
                elseif(is_string($targets)){
                $targets = json_decode($targets,true) ?? [];
                }
                elseif(!is_array($targets)){
                $targets = [];
                }

                $labels = [
                'all_students'=>'All Students',
                'all_staff'=>'All Staff',
                'all_shs_faculty'=>'SHS Faculty',
                'all_college_faculty'=>'College Faculty',
                'admin_personnel'=>'Admin Personnel',
                'specific_personnel'=>'Specific Personnel',
                ];

                @endphp

                @forelse($targets as $target)

                <span
                    class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold mr-2 mb-2">

                    {{ $labels[$target] ?? ucfirst(str_replace('_',' ',$target)) }}

                </span>

                @empty

                <span class="text-gray-400">
                    No Target Audience
                </span>

                @endforelse

            </div>

        </div>



        {{-- Advisory Body --}}
        <div class="p-8">

            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-5">
                Advisory Content
            </h4>

            <div class="prose max-w-none prose-blue">

                {!! $advisory->body !!}

            </div>

        </div>




        {{-- Acknowledgement Monitoring --}}
        <div class="border-t bg-gray-50">

            <div class="px-8 py-6">

                <div class="flex items-center justify-between mb-5">

                    <div>

                        <p class="text-sm text-gray-500 mt-1">
                            Employees who have confirmed reading this advisory.
                        </p>
                    </div>

                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">

                    <table class="min-w-full">

                        <thead class="bg-gray-100">

                            <tr class="text-xs uppercase tracking-wider text-gray-600">

                                <th class="px-6 py-3 text-left">
                                    Employee ID
                                </th>

                                <th class="px-6 py-3 text-left">
                                    Employee Name
                                </th>

                                <th class="px-6 py-3 text-left">
                                    Department
                                </th>

                                <th class="px-6 py-3 text-left">
                                    Date Acknowledged
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-200">

                            @forelse($advisory->acknowledgements as $ack)

                            <tr class="hover:bg-gray-50">

                                <td class="px-6 py-4 font-mono text-blue-600 font-semibold">
                                    {{ $ack->employee->id ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $ack->employee->first_name ?? '' }}
                                    {{ $ack->employee->last_name ?? '' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $ack->employee->role ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-gray-500">
                                    {{ \Carbon\Carbon::parse($ack->acknowledged_at)->format('M d, Y h:i A') }}
                                </td>

                            </tr>

                            @empty

                            <tr>

                                <td colspan="4" class="py-10 text-center text-gray-400">

                                    <i class="fa-solid fa-user-check text-4xl mb-3 block text-gray-300"></i>

                                    No employee has acknowledged this advisory yet.

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    </div>
</div>
@endsection