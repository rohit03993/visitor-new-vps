<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bulk Upload Students') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Upload Student Data</h3>
                        <p class="text-sm text-gray-600">Upload a CSV file to bulk import students into the system.</p>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Instructions:</h4>
                        <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                            <li>Download the CSV template below</li>
                            <li>Fill in student information in the template</li>
                            <li>For classes, use "YES" to assign a student to that class</li>
                            <li>All students will have default password: <strong>password123</strong></li>
                            <li>You can add up to 4 additional phone numbers per student</li>
                            <li>Phone numbers must be 10 digits starting with 6-9</li>
                        </ul>
                    </div>

                    <!-- Download Template -->
                    <div class="mb-6">
                        <a href="{{ route('homework.admin.users.template') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download CSV Template
                        </a>
                    </div>

                    <!-- Upload Form -->
                    <form method="POST" action="{{ route('homework.admin.users.bulk-upload.process') }}" enctype="multipart/form-data">
                        @csrf

                        @if ($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- File Upload -->
                        <div class="mb-6">
                            <x-input-label for="csv_file" :value="__('CSV File')" />
                            <x-text-input id="csv_file" class="block mt-1 w-full" type="file" name="csv_file" accept=".csv" required />
                            <p class="mt-1 text-sm text-gray-500">Upload a CSV file (Max: 10MB)</p>
                            <x-input-error :messages="$errors->get('csv_file')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('homework.admin.users.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <x-primary-button>
                                {{ __('Upload Students') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Sample CSV Format -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Sample CSV Format:</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-gray-50 border border-gray-300 text-xs">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-3 py-2 border">name</th>
                                        <th class="px-3 py-2 border">roll_number</th>
                                        <th class="px-3 py-2 border">mobile_number</th>
                                        <th class="px-3 py-2 border">additional_phone_number_1</th>
                                        <th class="px-3 py-2 border">additional_phone_number_2</th>
                                        <th class="px-3 py-2 border">additional_phone_number_3</th>
                                        <th class="px-3 py-2 border">additional_phone_number_4</th>
                                        @php
                                            $sampleClasses = \App\Models\SchoolClass::take(2)->get();
                                        @endphp
                                        @foreach($sampleClasses as $class)
                                            <th class="px-3 py-2 border bg-blue-100">{{ $class->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="px-3 py-2 border">John Doe</td>
                                        <td class="px-3 py-2 border">2024001</td>
                                        <td class="px-3 py-2 border">9876543210</td>
                                        <td class="px-3 py-2 border">8765432109</td>
                                        <td class="px-3 py-2 border"></td>
                                        <td class="px-3 py-2 border"></td>
                                        <td class="px-3 py-2 border"></td>
                                        @foreach($sampleClasses as $class)
                                            <td class="px-3 py-2 border bg-blue-50">YES</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 border">Jane Smith</td>
                                        <td class="px-3 py-2 border">2024002</td>
                                        <td class="px-3 py-2 border">7654321098</td>
                                        <td class="px-3 py-2 border"></td>
                                        <td class="px-3 py-2 border"></td>
                                        <td class="px-3 py-2 border"></td>
                                        <td class="px-3 py-2 border"></td>
                                        @foreach($sampleClasses as $class)
                                            <td class="px-3 py-2 border bg-blue-50">NO</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 text-xs text-gray-600 italic">Note: Class columns (highlighted in blue) will automatically adjust based on your existing classes. Use "YES" to assign a student to that class, "NO" or leave blank to skip.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

