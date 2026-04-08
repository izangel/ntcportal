@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Candidacy Position') }}
        </h2>
        <a href="{{ route('admin.candidacy.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-8">
            <div class="mb-6 border-b pb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    Candidate: {{ ucwords(str_replace('_', ' ', $candidacy->student->first_name ?? '')) }} {{ ucwords(str_replace('_', ' ', $candidacy->student->last_name ?? '')) }}
                </h3>
                <p class="text-gray-500">{{ $candidacy->student->user->email ?? 'N/A' }}</p>
            </div>

            <form action="{{ route('admin.candidacy.update', $candidacy) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="position_applied" class="block font-medium text-sm text-gray-700">{{ __('Position Applied For') }}</label>
                        <select id="position_applied" name="position_applied" class="block w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @foreach($positions as $value => $label)
                                <option value="{{ $value }}" {{ old('position_applied', $candidacy->position_applied) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('position_applied')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="is_independent" class="block font-medium text-sm text-gray-700">{{ __('Candidacy Type') }}</label>
                        <select id="is_independent" name="is_independent" onchange="togglePartylist()" class="block w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="1" {{ old('is_independent', $candidacy->is_independent) == 1 ? 'selected' : '' }}>Independent</option>
                            <option value="0" {{ old('is_independent', $candidacy->is_independent) == 0 ? 'selected' : '' }}>Partylist</option>
                        </select>
                        @error('is_independent')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="partylist_wrapper" class="{{ old('is_independent', $candidacy->is_independent) == 1 ? 'hidden' : '' }}">
                        <label for="partylist" class="block font-medium text-sm text-gray-700">{{ __('Partylist Name') }}</label>
                        <input id="partylist" type="text" name="partylist" class="block w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('partylist', $candidacy->partylist) }}">
                        @error('partylist')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePartylist() {
        if (document.getElementById('is_independent').value == '1') {
            document.getElementById('partylist_wrapper').classList.add('hidden');
        } else {
            document.getElementById('partylist_wrapper').classList.remove('hidden');
        }
    }
</script>
@endsection