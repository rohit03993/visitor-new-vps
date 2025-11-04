<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $class->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('homework.admin.classes.assign-students', $class) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Assign Students
                </a>
                <a href="{{ route('homework.admin.classes.edit', $class) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                    Edit Class
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Class Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2">Class Information</h3>
                    <p class="text-gray-600">{{ $class->description ?? 'No description provided.' }}</p>
                </div>
            </div>

            <!-- Students -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Students ({{ $class->students->count() }})</h3>
                    @if($class->students->isEmpty())
                        <p class="text-sm text-gray-500">No students enrolled yet.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($class->students as $student)
                                <div class="flex items-center p-3 border border-gray-300 rounded-lg">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold text-sm">{{ strtoupper(substr($student->name ?? 'U', 0, 1)) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $student->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $student->roll_number ?? '' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Homework -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Homework ({{ $class->homework->count() }})</h3>
                    @if($class->homework->isEmpty())
                        <p class="text-sm text-gray-500">No homework uploaded yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($class->homework->take(5) as $hw)
                                <a href="{{ route('homework.homework.show', $hw) }}" class="flex items-center justify-between p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $hw->title }}</div>
                                        <div class="text-sm text-gray-600">{{ $hw->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">{{ $hw->type }}</span>
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Back Button -->
            <div>
                <a href="{{ route('homework.admin.classes.index') }}" class="text-blue-600 hover:text-blue-800">
                    ← Back to Classes
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

