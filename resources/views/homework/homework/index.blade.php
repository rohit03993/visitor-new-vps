<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('All Homework') }}
            </h2>
            @php
                $staffUser = Auth::guard('web')->user();
                $isStaff = $staffUser && ($staffUser->isAdmin() || $staffUser->isStaff());
            @endphp
            @if($isStaff)
                <a href="{{ route('homework.homework.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Upload New
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($homework->isEmpty())
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Homework Found</h3>
                        <p class="text-gray-500">No homework assignments have been created yet.</p>
                    </div>
                </div>
            @else
                <!-- Homework List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Title
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Class
                                    </th>
                                    @php
                                        $student = Auth::guard('student')->user();
                                        $isStudent = $student && !Auth::guard('web')->user();
                                    @endphp
                                    @if($isStudent)
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Teacher
                                        </th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Created Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                    @if($isStaff)
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            View Stats
                                        </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($homework as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->title }}</div>
                                            @if($item->description)
                                                <div class="text-xs text-gray-500">{{ Str::limit($item->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $item->schoolClass->name }}</span>
                                        </td>
                                        @if($isStudent)
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-900">{{ $item->teacher->name }}</span>
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $item->created_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->created_at->format('g:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $item->type === 'pdf' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $item->type === 'image' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $item->type === 'text' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $item->type === 'link' ? 'bg-green-100 text-green-800' : '' }}">
                                                {{ strtoupper($item->type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('homework.homework.show', $item) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                                View
                                            </a>
                                            @php
                                                $currentStaff = Auth::guard('web')->user();
                                                // Admin and Staff can edit/delete all homework (matching controller logic)
                                                $canEdit = $currentStaff && ($currentStaff->isAdmin() || $currentStaff->isStaff());
                                            @endphp
                                            @if($canEdit)
                                                <a href="{{ route('homework.homework.edit', $item) }}" class="text-green-600 hover:text-green-900 mr-3">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('homework.homework.destroy', $item) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this homework?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        @if($isStaff)
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $viewed = $item->viewed_students_count;
                                                    $total = $item->total_students_count;
                                                    $percentage = $item->view_percentage;
                                                @endphp
                                                <div class="text-sm">
                                                    <a href="{{ route('homework.homework.stats', $item) }}" class="flex items-center hover:text-blue-600 transition cursor-pointer">
                                                        <svg class="h-4 w-4 text-green-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                        <span class="font-semibold {{ $percentage >= 80 ? 'text-green-600' : ($percentage >= 50 ? 'text-orange-600' : 'text-red-600') }}">
                                                            {{ $viewed }}/{{ $total }}
                                                        </span>
                                                    </a>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        {{ $percentage }}% viewed
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-200">
                        {{ $homework->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

