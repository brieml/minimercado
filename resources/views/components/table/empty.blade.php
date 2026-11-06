@props(['colspan' => 1])

<tr>
    <td colspan="{{ $colspan }}" class="py-10! text-center text-sm text-slate-500">{{ $slot }}</td>
</tr>
