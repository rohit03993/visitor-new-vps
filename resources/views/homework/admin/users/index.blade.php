<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Management') }}
            </h2>
            <div class="flex items-center gap-3 flex-wrap">
                @auth('web')
                    @if(auth()->guard('web')->user()->isAdmin())
                        <a href="{{ route('homework.admin.users.export-all') }}" class="inline-flex items-center px-4 py-2 rounded-lg transition shadow-md hover:shadow-lg font-semibold border-2" title="Download all student data as backup (Admin Only)" style="display: inline-flex !important; visibility: visible !important; opacity: 1 !important; background-color: #7c3aed !important; color: #ffffff !important; border-color: #6d28d9 !important;">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ffffff !important;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span style="color: #ffffff !important; font-weight: 600;">Download All Data</span>
                        </a>
                    @endif
                @endauth
                <a href="{{ route('homework.admin.users.bulk-upload') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Bulk Upload
                </a>
                <a href="{{ route('homework.admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Student
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Bulk Upload Errors -->
                    @if(session('bulk_upload_errors'))
                        <div class="mb-6 bg-yellow-50 border border-yellow-400 rounded-lg p-4">
                            <h4 class="font-semibold text-yellow-900 mb-2">Bulk Upload Errors:</h4>
                            <ul class="list-disc list-inside text-sm text-yellow-800 max-h-40 overflow-y-auto">
                                @foreach(session('bulk_upload_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- Users Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roll Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Password</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $user->roll_number ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $phoneNumbers = collect([$user->mobile_number])->merge($user->phoneNumbers->pluck('phone_number'))->filter();
                                            @endphp
                                            @if($phoneNumbers->count() > 0)
                                                <div class="space-y-1">
                                                    @foreach($phoneNumbers as $phone)
                                                        <div class="flex items-center space-x-2">
                                                            <span class="text-sm text-gray-900">{{ $phone }}</span>
                                                            @php
                                                                $phoneNumber = $user->phoneNumbers->firstWhere('phone_number', $phone) ?? ($user->mobile_number === $phone ? (object)['whatsapp_enabled' => true] : null);
                                                            @endphp
                                                            @if($phoneNumber && ($phoneNumber->whatsapp_enabled ?? true))
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                                                    </svg>
                                                                    WA
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($user->password_plain)
                                                <div class="flex items-center space-x-2">
                                                    <span id="password-{{ $user->id }}" class="text-sm font-mono font-semibold text-gray-900">••••••••</span>
                                                    <button onclick="togglePassword({{ $user->id }}, '{{ $user->password_plain }}')" class="text-blue-600 hover:text-blue-800">
                                                        <svg id="icon-{{ $user->id }}" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $classes = $user->schoolClasses;
                                            @endphp
                                            @if($classes && $classes->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($classes as $class)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $class->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('homework.admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <form method="POST" action="{{ route('homework.admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No students found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(userId, password) {
            const passwordElement = document.getElementById('password-' + userId);
            const iconElement = document.getElementById('icon-' + userId);
            
            if (passwordElement.textContent === '••••••••') {
                passwordElement.textContent = password;
                iconElement.classList.add('hidden');
            } else {
                passwordElement.textContent = '••••••••';
                iconElement.classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>

