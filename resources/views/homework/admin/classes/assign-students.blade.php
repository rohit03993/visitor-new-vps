<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Students to') }} {{ $class->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('homework.admin.classes.store-students', $class) }}">
                        @csrf

                        <p class="text-sm text-gray-600 mb-6">Select the students you want to enroll in {{ $class->name }}</p>

                        @if($students->isEmpty())
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-sm text-yellow-800">No students found. Please create students first.</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                                @foreach($students as $student)
                                    <label class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" 
                                            {{ in_array($student->id, $assignedStudentIds) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <div class="ml-3 flex-1">
                                            <div class="text-sm font-medium text-gray-900">{{ $student->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $student->roll_number ?? '' }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('homework.admin.classes.show', $class) }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <x-primary-button>
                                {{ __('Save Assignments') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

