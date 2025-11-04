<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notifications') }}
            </h2>
            @if($notifications->count() > 0)
                <form method="POST" action="{{ route('homework.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Mark All as Read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($notifications->isEmpty())
                <!-- Empty State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Notifications</h3>
                        <p class="text-gray-500">You're all caught up! Check back later for new homework.</p>
                    </div>
                </div>
            @else
                <!-- Notification List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="divide-y divide-gray-200">
                        @foreach($notifications as $notification)
                            <div class="p-6 hover:bg-gray-50 transition {{ $notification->is_read ? 'bg-white' : 'bg-blue-50' }}">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            @if(!$notification->is_read)
                                                <span class="inline-block w-2 h-2 bg-blue-600 rounded-full mr-3"></span>
                                            @endif
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $notification->title }}
                                            </h3>
                                        </div>
                                        <p class="text-gray-600 mb-3">
                                            {{ $notification->message }}
                                        </p>
                                        @if($notification->homework)
                                            <div class="flex items-center text-sm text-gray-500 space-x-4 mb-3">
                                                <span class="flex items-center">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                    {{ $notification->homework->schoolClass->name ?? 'Unknown Class' }}
                                                </span>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('homework.homework.show', $notification->homework) }}" 
                                                   class="inline-flex items-center text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                                    View Homework
                                                </a>
                                                @if(!$notification->is_read)
                                                    <form method="POST" action="{{ route('homework.notifications.read', $notification) }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
                                                            Mark as Read
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4 text-xs text-gray-400">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-200">
                        {{ $notifications->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

