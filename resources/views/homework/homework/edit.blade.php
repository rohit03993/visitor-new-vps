<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Homework') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('homework.homework.update', $homework) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <!-- Display Validation Errors -->
                        @if ($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Class Selection -->
                        <div class="mb-4">
                            <x-input-label for="class_id" :value="__('Class')" />
                            <select name="class_id" id="class_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Select a class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id', $homework->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('class_id')" class="mt-2" />
                        </div>

                        <!-- Title -->
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $homework->title)" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('description', $homework->description) }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Write all the homework details and instructions here</p>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Current File (if applicable) -->
                        @if($homework->type === 'pdf' || $homework->type === 'image')
                            <div class="mb-4 bg-gray-50 p-4 rounded">
                                <x-input-label :value="__('Current File')" />
                                <p class="text-sm text-gray-600 mt-1">{{ basename($homework->file_path) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Upload a new file to replace it</p>
                            </div>
                        @endif

                        <!-- Attach Files (Optional) -->
                        <div class="mb-6">
                            <x-input-label for="file" :value="__('Attach Files (Optional)')" />
                            <x-text-input id="file" class="block mt-1 w-full" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" />
                            <p class="mt-1 text-sm text-gray-500">Upload PDF or Image files if needed (Max: 10MB)</p>
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('homework.dashboard') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <x-primary-button>
                                {{ __('Update Homework') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

