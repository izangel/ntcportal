<tr class="hover:bg-[#FFF5F5] transition-colors">
    <td class="px-3 py-0.5 font-bold text-slate-700">{{ $block->course->code }}</td>
    <td class="px-3 py-0.5 text-slate-500 uppercase truncate max-w-[220px]">{{ $block->course->name }}</td>
    <td class="px-3 py-0.5 font-semibold text-[#4A5568] whitespace-nowrap">{{ $block->schedule_string }}</td>
    <td class="px-3 py-0.5 text-[#3182CE] font-bold uppercase">{{ $block->room_name }}</td>
    <td class="px-3 py-0.5 text-slate-700 font-semibold">
        <span class="text-slate-500">{{ $block->faculty->last_name ?? 'N/A' }}, {{ substr($block->faculty->first_name ?? 'N/A', 0, 1) }}.</span>
    </td>
    <td class="px-3 py-0.5 text-slate-400 whitespace-nowrap">
        {{ $block->semester }} | {{ $block->academicYear->start_year }}-{{ $block->academicYear->end_year }}
    </td>
    <td class="px-3 py-0.5 text-right whitespace-nowrap">
        <div class="flex justify-end gap-3 text-[9px] font-bold">
            <a href="{{ route('course_blocks.edit', $block->id) }}" class="text-[#D69E2E] hover:text-[#B7791F]">EDIT</a>
            <form action="{{ route('course_blocks.destroy', $block->id) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="text-[#FEB2B2] hover:text-[#E53E3E]" onclick="return confirm('Delete?')">DEL</button>
            </form>
        </div>
    </td>
</tr>