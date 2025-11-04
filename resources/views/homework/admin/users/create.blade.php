<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Student') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('homework.admin.users.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Full Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Roll Number -->
                        <div class="mb-4">
                            <x-input-label for="roll_number" :value="__('Roll Number')" />
                            <x-text-input id="roll_number" class="block mt-1 w-full" type="text" name="roll_number" :value="old('roll_number')" placeholder="e.g., 2024001" required />
                            <x-input-error :messages="$errors->get('roll_number')" class="mt-2" />
                        </div>

                        <!-- Primary Mobile Number -->
                        <div class="mb-4">
                            <x-input-label for="mobile_number" :value="__('Primary Mobile Number')" />
                            <div class="flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    +91
                                </span>
                                <input type="text" name="mobile_number" id="mobile_number" 
                                       class="block w-full rounded-none rounded-r-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 phone-input" 
                                       value="{{ old('mobile_number') }}" 
                                       placeholder="8320936486" 
                                       maxlength="10" 
                                       required />
                            </div>
                            <x-input-error :messages="$errors->get('mobile_number')" class="mt-2" />
                        </div>

                        <!-- Additional Phone Numbers - Up to 4 -->
                        <div class="mb-4">
                            <x-input-label :value="__('Additional Phone Numbers (Optional - Max 4)')" />
                            <p class="text-sm text-gray-500 mb-3">Add up to 4 additional phone numbers - All will receive WhatsApp notifications</p>
                            <div id="phoneNumbersContainer" class="space-y-3">
                                <!-- First additional phone number input -->
                                <div class="phone-number-row flex items-center gap-3">
                                    <div class="flex-1 flex rounded-md shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                            +91
                                        </span>
                                        <input type="text" name="phone_numbers[]" 
                                               class="block w-full rounded-none rounded-r-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 phone-input" 
                                               placeholder="8320936486" 
                                               maxlength="10" />
                                    </div>
                                    <button type="button" onclick="removePhoneNumber(this)" class="text-red-600 hover:text-red-800 hidden remove-btn">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" onclick="addPhoneNumber()" class="mt-2 text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Another Number
                            </button>
                            <x-input-error :messages="$errors->get('phone_numbers')" class="mt-2" />
                        </div>

                        <!-- Assign Classes -->
                        <div class="mb-4">
                            <x-input-label :value="__('Assign to Classes')" />
                            <p class="text-sm text-gray-500 mb-2">Select the classes this student will be enrolled in</p>
                            <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-4">
                                @forelse($classes as $class)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm">
                                        <span class="ml-2 text-sm text-gray-900">{{ $class->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500">No classes available. <a href="{{ route('homework.admin.classes.create') }}" class="text-blue-600">Create a class first</a></p>
                                @endforelse
                            </div>
                            <x-input-error :messages="$errors->get('class_ids')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-6">
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('homework.admin.users.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <x-primary-button>
                                {{ __('Create Student') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let phoneNumberCount = 1;
        
        function addPhoneNumber() {
            if (phoneNumberCount >= 4) {
                alert('Maximum 4 additional phone numbers allowed');
                return;
            }
            
            const container = document.getElementById('phoneNumbersContainer');
            const newRow = document.createElement('div');
            newRow.className = 'phone-number-row flex items-center gap-3';
            newRow.innerHTML = `
                <div class="flex-1 flex rounded-md shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                        +91
                    </span>
                    <input type="text" name="phone_numbers[]" 
                           class="block w-full rounded-none rounded-r-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 phone-input" 
                           placeholder="8320936486" 
                           maxlength="10" />
                </div>
                <button type="button" onclick="removePhoneNumber(this)" class="text-red-600 hover:text-red-800 remove-btn">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            `;
            container.appendChild(newRow);
            phoneNumberCount++;
            updateRemoveButtons();
        }
        
        function removePhoneNumber(button) {
            const container = document.getElementById('phoneNumbersContainer');
            if (container.children.length > 1) {
                button.closest('.phone-number-row').remove();
                phoneNumberCount--;
                updateRemoveButtons();
            }
        }
        
        function updateRemoveButtons() {
            const container = document.getElementById('phoneNumbersContainer');
            const removeButtons = container.querySelectorAll('.remove-btn');
            removeButtons.forEach(btn => {
                if (container.children.length > 1) {
                    btn.classList.remove('hidden');
                } else {
                    btn.classList.add('hidden');
                }
            });
        }
        
        // Set initial state
        document.addEventListener('DOMContentLoaded', function() {
            updateRemoveButtons();
        });
    </script>
</x-app-layout>

