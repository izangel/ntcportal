@extends('layouts.admin')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Change Temporary Password
</h2>
@endsection

@section('content')

<div class="py-12">
    <div class="max-w-xl mx-auto">
        <div class="bg-white shadow rounded-lg p-6">

            <div class="mb-6">
                <h2 class="text-2xl font-bold">
                    Welcome!
                </h2>

                <p class="text-gray-600 mt-2">
                    This account is using a temporary password.
                    You must create a new password before continuing.
                </p>
            </div>

            <form method="POST" action="{{ route('force.password.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block font-medium">
                        New Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="w-full border rounded p-2"
                        required>
                </div>

                <div class="mb-6">
                    <label class="block font-medium">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        name="password_confirmation"
                        class="w-full border rounded p-2"
                        required>
                </div>

                <button
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded">

                    Save Password

                </button>

            </form>

        </div>
    </div>
</div>

@endsection