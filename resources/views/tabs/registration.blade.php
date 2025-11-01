@php($active = $activeTab ?? 'queue')
<div id="registrationTab" class="tab-content{{ $active !== 'registration' ? ' hidden' : '' }}">
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <div class="card-title">New Patient Registration</div>
            <div class="card-description">Register a new patient to the queue</div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif
            <form id="patientRegistrationForm" action="{{ url('/patients/add') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2">
                    <div class="form-group">
                            <label class="form-label" for="patientFirstName">First Name</label>
                            <input type="text" id="patientFirstName" name="firstName" class="form-input" placeholder="First name" required value="{{ old('firstName') }}">
                            @error('firstName')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    <div class="form-group">
                        <label class="form-label" for="patientLastName">Last Name</label>
                        <input type="text" id="patientLastName" name="lastName" class="form-input" placeholder="Last name" required value="{{ old('lastName') }}">
                        @error('lastName')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="patientAge">Age</label>
                        <input type="number" id="patientAge" name="age" class="form-input" placeholder="Age" required value="{{ old('age') }}">
                        @error('age')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="patientContact">Contact Number</label>
                        <input type="tel" id="patientContact" name="contact" class="form-input" placeholder="Contact number" required value="{{ old('contact') }}">
                        @error('contact')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="patientGender">Gender</label>
                    <select id="patientGender" name="gender" class="form-select" required>
                        <option value="">Select gender</option>
                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="patientSymptoms">Symptoms / Reason for Visit</label>
                    <textarea id="patientSymptoms" name="symptoms" class="form-textarea" rows="3" placeholder="Describe symptoms or reason for visit" required>{{ old('symptoms') }}</textarea>
                    @error('symptoms')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="patientPriority">Priority</label>
                        <select id="patientPriority" name="priority" class="form-select" required>
                                <option value="Regular" {{ old('priority','Regular') == 'Regular' ? 'selected' : '' }}>Regular</option>
                                <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="patientServiceType">Service Type</label>
                            <select id="patientServiceType" name="serviceType" class="form-select" required>
                                <option value="">Select service type</option>
                                <option value="Consultation" {{ old('serviceType') == 'Consultation' ? 'selected' : '' }}>Consultation</option>
                                <option value="Emergency" {{ old('serviceType') == 'Emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="Pediatric" {{ old('serviceType') == 'Pediatric' ? 'selected' : '' }}>Pediatric Care</option>
                                <option value="Follow-up" {{ old('serviceType') == 'Follow-up' ? 'selected' : '' }}>Follow-up</option>
                            </select>
                            @error('serviceType')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
        </div>
        <div class="card-footer">
                <button type="submit" class="btn btn-primary" id="addPatientBtn">Add to Queue</button>
                </form>
        </div>
    </div>
</div>
