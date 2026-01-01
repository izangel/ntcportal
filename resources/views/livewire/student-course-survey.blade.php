<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">My Course Evaluations</h2>
    
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100 text-left text-sm uppercase">
                <th class="p-3">Course Code</th>
                <th class="p-3">Course Name</th>
                <th class="p-3 text-center">Status</th>
                <th class="p-3 text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjects as $item)
            <tr class="border-b">
                <td class="p-3">{{ $item->course->code }}</td>
                <td class="p-3">{{ $item->course->name }}</td>
                <td class="p-3 text-center">
                    @if($item->survey)
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Answered</span>
                    @else
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">Pending</span>
                    @endif
                </td>
                <td class="p-3 text-right">
                    @if(!$item->survey)
                        <button wire:click="openSurveyModal({{ $item->course_id }})" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                            Rate Satisfaction
                        </button>
                    @else
                        <span class="text-gray-400 italic text-sm">Completed</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>