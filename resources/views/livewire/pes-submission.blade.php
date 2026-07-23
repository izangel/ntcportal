<div class="p-6 max-w-4xl mx-auto">
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
    @endif

    <!-- Upload Panel Form -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Submit New PES Evaluation Sheet</h2>
        <form wire:submit.prevent="save" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload File (PDF Format Only)</label>
                <input type="file" wire:model="pesFile" class="block w-full text-sm border border-gray-300 rounded cursor-pointer p-2">
                @error('pesFile') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-blue-600 text-white rounded font-medium hover:bg-blue-700 disabled:opacity-50">
                <span wire:loading.remove>Upload and Transmit</span>
                <span wire:loading>Processing Integration Sync...</span>
            </button>
        </form>
    </div>

    <!-- Submissions Log Ledger Table -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-bold mb-4 text-gray-800">Your Evaluation Records</h3>
        @if($submissions->isEmpty())
            <p class="text-gray-500 text-sm">No recorded documents located under your profile tracking hash.</p>
        @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b text-sm font-semibold text-gray-600">
                        <th class="py-2">Original Filename</th>
                        <th class="py-2">Submission Timestamp</th>
                        <th class="py-2">Drive Reference Link</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    @foreach($submissions as $submission)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3">{{ $submission->file_name }}</td>
                            <td class="py-3">{{ $submission->created_at->format('M d, Y h:i A') }}</td>
                            <td class="py-3">
                                <a href="{{ route('pes.download', $submission) }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center">
                                    View Document ↗
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>