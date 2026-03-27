@extends('layouts.admin')

@section('header')
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Leave Credits') }}
        </h2>
@endsection

@section('content')


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

               <h1>My Leave Credits</h1>

                @if(!empty($remainingCredits))
                    @foreach ($remainingCredits as $type => $credits)
                        <p>{{ Str::of($type)->replace('_', ' ')->title() }}: {{ fmod((float) $credits, 1.0) == 0.0 ? number_format((float) $credits, 0) : number_format((float) $credits, 1) }} remaining</p>
                    @endforeach
                @else
                    <p>No leave credits found for this employee.</p>
                @endif

                <h2>My Leave Transactions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Days Taken</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->leaveType->name }}</td>
                                <td>{{ $transaction->start_date }}</td>
                                <td>{{ $transaction->end_date }}</td>
                                <td>{{ fmod((float) $transaction->total_days, 1.0) == 0.0 ? number_format((float) $transaction->total_days, 0) : number_format((float) $transaction->total_days, 1) }}</td>
                                <td>{{ $transaction->approval_status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

               
            </div>
        </div>
    </div>
@endsection