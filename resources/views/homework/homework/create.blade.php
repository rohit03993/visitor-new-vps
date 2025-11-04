<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Homework') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('homework.homework.store') }}" enctype="multipart/form-data">
                        @csrf

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
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('class_id')" class="mt-2" />
                        </div>

                        <!-- Title -->
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('description') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Write all the homework details and instructions here</p>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Attach Files (Optional) -->
                        <div class="mb-4">
                            <x-input-label for="file" :value="__('Attach Files (Optional)')" />
                            <x-text-input id="file" class="block mt-1 w-full" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" />
                            <p class="mt-1 text-sm text-gray-500">Upload PDF or Image files if needed (Max: 10MB)</p>
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        </div>

                        <!-- WhatsApp Notification Toggle -->
                        <div class="mb-6">
                            <div class="flex items-center p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <input type="checkbox" name="send_whatsapp" id="send_whatsapp" value="1" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                                <label for="send_whatsapp" class="ml-3 flex items-center">
                                    <svg class="h-5 w-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">Send WhatsApp notifications to all students</span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">If unchecked, only in-app notifications will be sent. No WhatsApp messages will be delivered.</p>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('homework.dashboard') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <x-primary-button>
                                {{ __('Upload Homework') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

