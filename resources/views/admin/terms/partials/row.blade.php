{{--
 | Contensio - The open content platform for Laravel.
 | Admin — terms partials row.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

<tr class="hover:bg-blue-50/40 transition-colors group">
    <td class="px-5 py-3" style="{{ $depth > 0 ? 'padding-left: ' . ($depth * 2 + 1.25) . 'rem;' : '' }}">
        <div class="flex items-center gap-2">
            @if($depth > 0)
            <span class="text-gray-300 select-none">↳</span>
            @endif
            <span class="font-medium text-gray-900">{{ $trans?->name ?? '—' }}</span>
        </div>
    </td>
    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $trans?->slug ?? '—' }}</td>
    @if($taxonomy->is_hierarchical)
    <td class="px-5 py-3 text-xs text-gray-400">
        @if($depth === 0 && $term->children->isNotEmpty())
        <span class="text-gray-400">{{ $term->children->count() }} {{ $term->children->count() === 1 ? 'child' : 'children' }}</span>
        @endif
    </td>
    @endif
    <td class="px-5 py-3 text-right">
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('cms.admin.terms.edit', [$taxonomy->id, $term->id]) }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-gray-900 px-2.5 py-1 rounded border border-gray-200 hover:bg-white transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Edit
            </a>

            <form id="delete-term-{{ $term->id }}"
                  method="POST"
                  action="{{ route('cms.admin.terms.destroy', [$taxonomy->id, $term->id]) }}"
                  class="hidden">
                @csrf @method('DELETE')
            </form>
            <button type="button"
                    @click="$dispatch('cms:confirm', {
                        title: 'Delete term?',
                        description: 'This will permanently delete &quot;{{ addslashes($trans?->name ?? 'this term') }}&quot;. This action cannot be undone.',
                        confirmLabel: 'Delete',
                        formId: 'delete-term-{{ $term->id }}'
                    })"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 hover:text-red-700 px-2.5 py-1 rounded border border-red-200 hover:bg-red-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </button>
        </div>
    </td>
</tr>
