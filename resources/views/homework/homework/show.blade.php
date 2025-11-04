<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Homework Details') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Title -->
                    <h1 class="text-3xl font-bold mb-4">{{ $homework->title }}</h1>
                    
                    <!-- Meta Information -->
                    <div class="flex flex-wrap gap-4 mb-6 text-sm text-gray-600">
                        <div class="flex items-center">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            {{ $homework->schoolClass->name }}
                        </div>
                        <div class="flex items-center">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $homework->teacher->name }}
                        </div>
                    </div>

                    <!-- Description -->
                    @if($homework->description)
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-semibold mb-2">Description</h3>
                            <p class="text-gray-700">{{ $homework->description }}</p>
                        </div>
                    @endif

                    <!-- Homework Content Based on Type -->
                    @if($homework->type === 'pdf' || $homework->type === 'image')
                        @if($homework->file_path)
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold text-lg">File Preview</h3>
                                    <a href="{{ route('homework.homework.download', $homework) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download File
                                    </a>
                                </div>
                                
                                @if($homework->type === 'pdf')
                                    <div class="border border-gray-300 rounded-lg overflow-hidden bg-white mb-4">
                                        <iframe src="{{ asset('storage/' . $homework->file_path) }}" class="w-full h-[600px]" frameborder="0"></iframe>
                                    </div>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-sm text-blue-800">
                                            <strong>Can't see the PDF?</strong> Click the "Download File" button above to view it.
                                        </p>
                                    </div>
                                @elseif($homework->type === 'image')
                                    <div class="border border-gray-300 rounded-lg overflow-hidden bg-white">
                                        <img src="{{ asset('storage/' . $homework->file_path) }}" alt="{{ $homework->title }}" class="w-full h-auto">
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif

                    <!-- Created Date Info -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm">
                            <span class="font-semibold">Created:</span>
                            {{ $homework->created_at->format('F j, Y g:i A') }}
                        </p>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-6">
                        <a href="{{ route('homework.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

