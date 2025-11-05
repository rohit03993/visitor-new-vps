<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Dashboard') }}
            </h2>
            @if($unreadCount > 0)
                <a href="{{ route('homework.notifications.index') }}" class="relative inline-flex items-center px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 h-5 w-5 bg-white text-red-600 rounded-full text-xs font-bold flex items-center justify-center">
                        {{ $unreadCount }}
                    </span>
                    New Homework
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Registration Success Message -->
            @if(session('registration_success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-semibold text-green-800 mb-2">
                                {{ session('registration_success.message') }}
                            </h3>
                            <div class="text-sm text-green-700 space-y-1">
                                <p><strong>Username (Roll Number):</strong> <span class="font-mono bg-green-100 px-2 py-1 rounded">{{ session('registration_success.roll_number') }}</span></p>
                                <p><strong>Default Password:</strong> <span class="font-mono bg-green-100 px-2 py-1 rounded">{{ session('registration_success.password') }}</span></p>
                                <p class="mt-2 text-xs text-green-600">
                                    <strong>Important:</strong> Please save these credentials. You can update your password from your profile page.
                                </p>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()" class="inline-flex text-green-500 hover:text-green-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($classes->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No classes enrolled</h3>
                        <p class="mt-1 text-sm text-gray-500">Contact your administrator to enroll in classes.</p>
                    </div>
                </div>
            @else
                <!-- My Classes -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">My Classes</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($classes as $class)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                                <div class="p-4 sm:p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-base sm:text-lg font-semibold text-gray-900">Your Class - {{ $class->name }}</h4>
                                    </div>
                                    
                                    <a href="{{ route('homework.homework.index', ['class_id' => $class->id]) }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm sm:text-base">
                                        View Homework
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Date Filter -->
                <div class="mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4">
                            <form method="GET" action="{{ route('homework.dashboard') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <label for="date_filter" class="text-sm font-medium text-gray-700 whitespace-nowrap">Filter by Date:</label>
                                <input type="date" 
                                       id="date_filter" 
                                       name="date_filter" 
                                       value="{{ $selectedDate }}" 
                                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <div class="flex gap-2">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm whitespace-nowrap">
                                        Apply
                                    </button>
                                    @if($selectedDate)
                                        <a href="{{ route('homework.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm whitespace-nowrap">
                                            Clear
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Assigned Homework (Unopened) -->
                @if($assignedHomework->isNotEmpty())
                    <div class="mb-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-4 sm:p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Assigned Homework</h3>
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                        {{ $assignedHomework->count() }} New
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($assignedHomework as $item)
                                        <div class="border-2 border-red-500 rounded-lg p-4 hover:shadow-md transition bg-white">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base sm:text-lg font-bold text-gray-900 truncate">{{ $item->title }}</h4>
                                                    <div class="flex items-center gap-2 mt-2">
                                                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 font-medium">
                                                            {{ $item->schoolClass->name }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0 ml-2">
                                                    @if($item->type === 'pdf')
                                                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    @elseif($item->type === 'image')
                                                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="h-8 w-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($item->description)
                                                <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ mb_substr($item->description, 0, 100) }}</p>
                                            @endif
                                            
                                            <div class="flex items-center justify-between mt-4 space-x-2">
                                                <p class="text-xs text-gray-500 flex-shrink-0">
                                                    <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ $item->created_at->format('M d, Y') }}
                                                </p>
                                                <a href="{{ route('homework.homework.show', $item) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    View Now
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Viewed Homework (Recently Opened) -->
                @if($viewedHomework->isNotEmpty())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Homework</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($viewedHomework as $item)
                                    <div class="border border-gray-300 rounded-lg p-4 hover:shadow-md transition bg-white">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-base sm:text-lg font-bold text-gray-900 truncate">{{ $item->title }}</h4>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 font-medium">
                                                        {{ $item->schoolClass->name }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0 ml-2">
                                                @if($item->type === 'pdf')
                                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                @elseif($item->type === 'image')
                                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                @else
                                                    <svg class="h-8 w-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($item->description)
                                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ Str::limit($item->description, 100) }}</p>
                                        @endif
                                        
                                        <div class="flex items-center justify-between mt-4 space-x-2">
                                            <p class="text-xs text-gray-500 flex-shrink-0">
                                                <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $item->created_at->format('M d, Y') }}
                                            </p>
                                            <a href="{{ route('homework.homework.show', $item) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Empty State -->
                @if($assignedHomework->isEmpty() && $viewedHomework->isEmpty())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No homework available</h3>
                            <p class="mt-1 text-sm text-gray-500">Your homework will appear here once it's assigned.</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>

