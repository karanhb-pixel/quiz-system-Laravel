<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate AI Questions for :bank', ['bank' => $bank->name]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ route('question-banks.show', $bank) }}" class="text-blue-600 hover:text-blue-800">
                        ← Back to Question Bank
                    </a>
                </div>

                <!-- AI Generation Form -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">🤖 AI Question Generator</h3>
                    <p class="text-gray-600 mb-4">
                        Generate high-quality quiz questions using artificial intelligence. Specify your topic and requirements below.
                    </p>

                    <form id="ai-generation-form" class="space-y-6">
                        @csrf

                        <!-- Question Type Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Question Type</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="relative">
                                    <input type="radio" name="question_type" value="mcq" class="sr-only peer" checked>
                                    <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 transition-colors">
                                        <div class="flex items-center">
                                            <div class="text-2xl mr-3">📝</div>
                                            <div>
                                                <div class="font-semibold text-gray-900">Multiple Choice</div>
                                                <div class="text-sm text-gray-600">A, B, C, D options</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative">
                                    <input type="radio" name="question_type" value="fill_blank" class="sr-only peer">
                                    <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 transition-colors">
                                        <div class="flex items-center">
                                            <div class="text-2xl mr-3">🔤</div>
                                            <div>
                                                <div class="font-semibold text-gray-900">Fill in the Blank</div>
                                                <div class="text-sm text-gray-600">Text completion</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative">
                                    <input type="radio" name="question_type" value="code" class="sr-only peer">
                                    <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 transition-colors">
                                        <div class="flex items-center">
                                            <div class="text-2xl mr-3">💻</div>
                                            <div>
                                                <div class="font-semibold text-gray-900">Coding Question</div>
                                                <div class="text-sm text-gray-600">Programming challenges</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Topic Input -->
                        <div>
                            <label for="topic" class="block text-sm font-medium text-gray-700 mb-2">Topic</label>
                            <input type="text" id="topic" name="topic" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Laravel, PHP, JavaScript, Algorithms..." required>
                            <p class="text-sm text-gray-500 mt-1">Specify the subject matter for the question</p>
                        </div>

                        <!-- Difficulty Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty Level</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="relative">
                                    <input type="radio" name="difficulty" value="easy" class="sr-only peer">
                                    <div class="p-3 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300 transition-colors text-center">
                                        <div class="text-green-600 text-xl mb-1">🟢</div>
                                        <div class="font-semibold text-gray-900">Easy</div>
                                        <div class="text-sm text-gray-600">Basic concepts</div>
                                    </div>
                                </label>

                                <label class="relative">
                                    <input type="radio" name="difficulty" value="medium" class="sr-only peer" checked>
                                    <div class="p-3 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:border-yellow-300 transition-colors text-center">
                                        <div class="text-yellow-600 text-xl mb-1">🟡</div>
                                        <div class="font-semibold text-gray-900">Medium</div>
                                        <div class="text-sm text-gray-600">Intermediate</div>
                                    </div>
                                </label>

                                <label class="relative">
                                    <input type="radio" name="difficulty" value="hard" class="sr-only peer">
                                    <div class="p-3 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300 transition-colors text-center">
                                        <div class="text-red-600 text-xl mb-1">🔴</div>
                                        <div class="font-semibold text-gray-900">Hard</div>
                                        <div class="text-sm text-gray-600">Advanced</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Programming Language (shown only for code questions) -->
                        <div id="language-section" class="hidden">
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-2">Programming Language</label>
                            <select id="language" name="language" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="php">PHP</option>
                                <option value="javascript">JavaScript</option>
                                <option value="python">Python</option>
                                <option value="java">Java</option>
                                <option value="cpp">C++</option>
                            </select>
                        </div>

                        <!-- Additional Context -->
                        <div>
                            <label for="additional_context" class="block text-sm font-medium text-gray-700 mb-2">Additional Context (Optional)</label>
                            <textarea id="additional_context" name="additional_context" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Provide any specific requirements, constraints, or context for the question generation..."></textarea>
                            <p class="text-sm text-gray-500 mt-1">Help the AI generate more relevant questions</p>
                        </div>

                        <!-- Generate Button -->
                        <div class="flex justify-center">
                            <button type="submit" id="generate-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition-colors duration-200 flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" id="loading-spinner" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                🚀 Generate Question
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Generated Question Preview -->
                <div id="question-preview" class="hidden bg-gray-50 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">📋 Generated Question Preview</h3>
                        <div id="quality-score" class="hidden">
                            <span class="text-sm text-gray-600">Quality Score: </span>
                            <span id="quality-score-value" class="font-bold text-green-600"></span>
                        </div>
                    </div>

                    <!-- Quality Validation Section -->
                    <div id="quality-validation" class="mb-4 p-4 bg-blue-50 rounded-lg hidden">
                        <h4 class="text-sm font-semibold text-blue-800 mb-2">🔍 Quality Check</h4>
                        <div id="quality-checks" class="space-y-2 text-sm">
                            <!-- Quality checks will be populated here -->
                        </div>
                    </div>

                    <div id="question-content" class="mb-6">
                        <!-- Question content will be loaded here -->
                    </div>

                    <!-- Review Options -->
                    <div class="mb-4 p-4 bg-yellow-50 rounded-lg">
                        <h4 class="text-sm font-semibold text-yellow-800 mb-2">👀 Review Options</h4>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="needs-review" class="mr-2">
                                <span class="text-sm text-gray-700">Mark for manual review</span>
                            </label>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-700">Confidence:</span>
                                <select id="confidence-level" class="text-sm border rounded px-2 py-1">
                                    <option value="high">High</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-4 justify-center">
                        <button id="regenerate-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                            🔄 Regenerate
                        </button>
                        <button id="edit-question-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                            ✏️ Edit Question
                        </button>
                        <button id="save-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                            💾 Save Question
                        </button>
                    </div>
                </div>

                <!-- Edit Question Modal -->
                <div id="edit-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Edit Generated Question</h3>
                            <button id="close-edit-modal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div id="edit-form-content">
                            <!-- Edit form will be populated here -->
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button id="cancel-edit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                                Cancel
                            </button>
                            <button id="save-edits" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bulk Generation Section -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6 mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">📦 Bulk Generation</h3>
                    <p class="text-gray-600 mb-4">
                        Generate multiple questions at once for efficient content creation.
                    </p>

                    <form id="bulk-generation-form" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="bulk_count" class="block text-sm font-medium text-gray-700 mb-2">Number of Questions</label>
                                <select id="bulk_count" name="count" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="5">5 Questions</option>
                                    <option value="10">10 Questions</option>
                                    <option value="15">15 Questions</option>
                                    <option value="20">20 Questions</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" id="bulk-generate-btn" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" id="bulk-loading-spinner" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Generate Bulk Questions
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('ai-generation-form');
            const bulkForm = document.getElementById('bulk-generation-form');
            const preview = document.getElementById('question-preview');
            const questionContent = document.getElementById('question-content');
            const generateBtn = document.getElementById('generate-btn');
            const bulkGenerateBtn = document.getElementById('bulk-generate-btn');
            const loadingSpinner = document.getElementById('loading-spinner');
            const bulkLoadingSpinner = document.getElementById('bulk-loading-spinner');
            const languageSection = document.getElementById('language-section');
            const qualityValidation = document.getElementById('quality-validation');
            const qualityChecks = document.getElementById('quality-checks');
            const qualityScore = document.getElementById('quality-score');
            const qualityScoreValue = document.getElementById('quality-score-value');
            const editModal = document.getElementById('edit-modal');
            const editFormContent = document.getElementById('edit-form-content');

            let currentGeneratedQuestion = null;
            let currentQuestionType = null;

            // Show/hide language selection based on question type
            document.querySelectorAll('input[name="question_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'code') {
                        languageSection.classList.remove('hidden');
                    } else {
                        languageSection.classList.add('hidden');
                    }
                });
            });

            // Single question generation
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());

                // Show loading
                generateBtn.disabled = true;
                loadingSpinner.classList.remove('hidden');
                generateBtn.innerHTML = '<span class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></span> Generating...';

                try {
                    const response = await fetch(`{{ route('ai-questions.generate', $bank) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        currentGeneratedQuestion = result.question;
                        currentQuestionType = document.querySelector('input[name="question_type"]:checked').value;
                        displayQuestion(result.question);
                        performQualityCheck(result.question);
                        preview.classList.remove('hidden');
                        preview.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    alert('Network error occurred. Please try again.');
                } finally {
                    // Hide loading
                    generateBtn.disabled = false;
                    loadingSpinner.classList.add('hidden');
                    generateBtn.innerHTML = '🚀 Generate Question';
                }
            });

            // Bulk generation
            bulkForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const singleFormData = new FormData(form);
                const data = {
                    ...Object.fromEntries(singleFormData.entries()),
                    ...Object.fromEntries(formData.entries())
                };

                // Show loading
                bulkGenerateBtn.disabled = true;
                bulkLoadingSpinner.classList.remove('hidden');

                try {
                    const response = await fetch(`{{ route('ai-questions.bulk-generate', $bank) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert(`Successfully generated ${result.generated_count} out of ${result.requested_count} questions! Check your question bank.`);
                        // Optionally redirect to question bank
                        // window.location.href = '{{ route('question-banks.show', $bank) }}';
                    } else {
                        alert('Error: ' + result.errors.join(', '));
                    }
                } catch (error) {
                    alert('Network error occurred. Please try again.');
                } finally {
                    // Hide loading
                    bulkGenerateBtn.disabled = false;
                    bulkLoadingSpinner.classList.add('hidden');
                }
            });

            // Regenerate button
            document.getElementById('regenerate-btn').addEventListener('click', function() {
                form.dispatchEvent(new Event('submit'));
            });

            // Edit question button
            document.getElementById('edit-question-btn').addEventListener('click', function() {
                if (!currentGeneratedQuestion) return;
                openEditModal(currentGeneratedQuestion);
            });

            // Close edit modal
            document.getElementById('close-edit-modal').addEventListener('click', function() {
                editModal.classList.add('hidden');
            });

            document.getElementById('cancel-edit').addEventListener('click', function() {
                editModal.classList.add('hidden');
            });

            // Save button
            document.getElementById('save-btn').addEventListener('click', async function() {
                if (!currentGeneratedQuestion) return;

                const needsReview = document.getElementById('needs-review').checked;
                const confidenceLevel = document.getElementById('confidence-level').value;

                // Calculate quality score based on validation results
                const qualityScore = qualityScoreValue ? parseInt(qualityScoreValue.textContent) : 85;

                // Add review metadata to question
                const questionToSave = {
                    ...currentGeneratedQuestion,
                    needs_review: needsReview,
                    quality_score: qualityScore,
                    generation_metadata: {
                        confidence_level: confidenceLevel,
                        reviewed_by_user: needsReview,
                        generated_at: new Date().toISOString(),
                        topic: document.getElementById('topic').value,
                        difficulty: document.querySelector('input[name="difficulty"]:checked').value
                    }
                };

                try {
                    const response = await fetch(`{{ route('ai-questions.store', $bank) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            question_type: currentQuestionType,
                            question_data: questionToSave,
                            confirm_save: true
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('Question saved successfully!');
                        preview.classList.add('hidden');
                        currentGeneratedQuestion = null;
                        // Reset review options
                        document.getElementById('needs-review').checked = false;
                        document.getElementById('confidence-level').value = 'medium';
                        // Optionally refresh the page or update UI
                        // window.location.reload();
                    } else {
                        alert('Error saving question: ' + result.message);
                    }
                } catch (error) {
                    alert('Network error occurred. Please try again.');
                }
            });

            // Quality check function
            function performQualityCheck(question) {
                const checks = [];
                let score = 100;

                // Check question text length
                const questionLength = question.question_text ? question.question_text.length : 0;
                if (questionLength < 10) {
                    checks.push({ type: 'warning', message: 'Question text is very short' });
                    score -= 20;
                } else if (questionLength > 500) {
                    checks.push({ type: 'warning', message: 'Question text is very long' });
                    score -= 10;
                } else {
                    checks.push({ type: 'success', message: 'Question length is appropriate' });
                }

                // Check for special characters or formatting issues
                if (question.question_text && question.question_text.includes('�')) {
                    checks.push({ type: 'error', message: 'Question contains encoding issues' });
                    score -= 30;
                }

                // Check for duplicate options (MCQ)
                if (question.option_a && question.option_b && question.option_c && question.option_d) {
                    const options = [question.option_a, question.option_b, question.option_c, question.option_d];
                    const uniqueOptions = new Set(options.map(opt => opt.toLowerCase().trim()));
                    if (uniqueOptions.size < 4) {
                        checks.push({ type: 'error', message: 'Some answer options are duplicates' });
                        score -= 25;
                    } else {
                        checks.push({ type: 'success', message: 'All answer options are unique' });
                    }
                }

                // Check for code quality (code questions)
                if (question.expected_code) {
                    if (question.expected_code.length < 10) {
                        checks.push({ type: 'warning', message: 'Expected code is very short' });
                        score -= 15;
                    } else if (question.expected_code.includes('echo "test"') || question.expected_code.includes('console.log("test")')) {
                        checks.push({ type: 'warning', message: 'Code appears to be placeholder/example' });
                        score -= 10;
                    } else {
                        checks.push({ type: 'success', message: 'Code appears well-structured' });
                    }
                }

                // Check for explanation quality
                if (question.explanation) {
                    if (question.explanation.length < 20) {
                        checks.push({ type: 'warning', message: 'Explanation is too brief' });
                        score -= 10;
                    } else {
                        checks.push({ type: 'success', message: 'Explanation is detailed' });
                    }
                } else {
                    checks.push({ type: 'warning', message: 'No explanation provided' });
                    score -= 15;
                }

                // Ensure score doesn't go below 0
                score = Math.max(0, score);

                displayQualityResults(checks, score);
            }

            function displayQualityResults(checks, score) {
                qualityChecks.innerHTML = '';

                checks.forEach(check => {
                    const checkElement = document.createElement('div');
                    checkElement.className = 'flex items-center space-x-2';

                    const icon = check.type === 'success' ? '✅' :
                               check.type === 'warning' ? '⚠️' : '❌';
                    const colorClass = check.type === 'success' ? 'text-green-700' :
                                     check.type === 'warning' ? 'text-yellow-700' : 'text-red-700';

                    checkElement.innerHTML = `
                        <span>${icon}</span>
                        <span class="${colorClass}">${check.message}</span>
                    `;

                    qualityChecks.appendChild(checkElement);
                });

                qualityScoreValue.textContent = score + '%';
                qualityScoreValue.className = score >= 80 ? 'font-bold text-green-600' :
                                            score >= 60 ? 'font-bold text-yellow-600' :
                                            'font-bold text-red-600';

                qualityValidation.classList.remove('hidden');
                qualityScore.classList.remove('hidden');
            }

            function openEditModal(question) {
                let editForm = '';

                switch (currentQuestionType) {
                    case 'mcq':
                        editForm = `
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                                    <textarea id="edit-question-text" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3">${question.question_text || ''}</textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Option A</label>
                                        <input type="text" id="edit-option-a" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${question.option_a || ''}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Option B</label>
                                        <input type="text" id="edit-option-b" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${question.option_b || ''}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Option C</label>
                                        <input type="text" id="edit-option-c" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${question.option_c || ''}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Option D</label>
                                        <input type="text" id="edit-option-d" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${question.option_d || ''}">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer</label>
                                    <select id="edit-correct-answer" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="a" ${question.correct_answer === 'a' ? 'selected' : ''}>A</option>
                                        <option value="b" ${question.correct_answer === 'b' ? 'selected' : ''}>B</option>
                                        <option value="c" ${question.correct_answer === 'c' ? 'selected' : ''}>C</option>
                                        <option value="d" ${question.correct_answer === 'd' ? 'selected' : ''}>D</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Explanation</label>
                                    <textarea id="edit-explanation" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3">${question.explanation || ''}</textarea>
                                </div>
                            </div>
                        `;
                        break;

                    case 'fill_blank':
                        editForm = `
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text (use ______ for blank)</label>
                                    <textarea id="edit-question-text" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3">${question.question_text || ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Expected Answer</label>
                                    <input type="text" id="edit-expected-answer" class="w-full px-3 py-2 border border-gray-300 rounded-md" value="${question.expected_answer || ''}">
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" id="edit-case-sensitive" class="mr-2" ${question.case_sensitive ? 'checked' : ''}>
                                        <span class="text-sm text-gray-700">Case sensitive</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Evaluation Hints</label>
                                    <textarea id="edit-evaluation-hints" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3">${question.evaluation_hints || ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Explanation</label>
                                    <textarea id="edit-explanation" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3">${question.explanation || ''}</textarea>
                                </div>
                            </div>
                        `;
                        break;

                    case 'code':
                        editForm = `
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                                    <textarea id="edit-question-text" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3">${question.question_text || ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Expected Code</label>
                                    <textarea id="edit-expected-code" class="w-full px-3 py-2 border border-gray-300 rounded-md font-mono text-sm" rows="8">${question.expected_code || ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                    <select id="edit-language" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="php" ${question.language === 'php' ? 'selected' : ''}>PHP</option>
                                        <option value="javascript" ${question.language === 'javascript' ? 'selected' : ''}>JavaScript</option>
                                        <option value="python" ${question.language === 'python' ? 'selected' : ''}>Python</option>
                                        <option value="java" ${question.language === 'java' ? 'selected' : ''}>Java</option>
                                        <option value="cpp" ${question.language === 'cpp' ? 'selected' : ''}>C++</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Evaluation Criteria</label>
                                    <textarea id="edit-evaluation-criteria" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3">${question.evaluation_criteria || ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Test Cases</label>
                                    <textarea id="edit-test-cases" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3">${question.test_cases || ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Explanation</label>
                                    <textarea id="edit-explanation" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3">${question.explanation || ''}</textarea>
                                </div>
                            </div>
                        `;
                        break;
                }

                editFormContent.innerHTML = editForm;
                editModal.classList.remove('hidden');

                // Save edits functionality
                document.getElementById('save-edits').onclick = function() {
                    saveEdits();
                };
            }

            function saveEdits() {
                // Collect edited data
                const editedQuestion = { ...currentGeneratedQuestion };

                switch (currentQuestionType) {
                    case 'mcq':
                        editedQuestion.question_text = document.getElementById('edit-question-text').value;
                        editedQuestion.option_a = document.getElementById('edit-option-a').value;
                        editedQuestion.option_b = document.getElementById('edit-option-b').value;
                        editedQuestion.option_c = document.getElementById('edit-option-c').value;
                        editedQuestion.option_d = document.getElementById('edit-option-d').value;
                        editedQuestion.correct_answer = document.getElementById('edit-correct-answer').value;
                        editedQuestion.explanation = document.getElementById('edit-explanation').value;
                        break;

                    case 'fill_blank':
                        editedQuestion.question_text = document.getElementById('edit-question-text').value;
                        editedQuestion.expected_answer = document.getElementById('edit-expected-answer').value;
                        editedQuestion.case_sensitive = document.getElementById('edit-case-sensitive').checked;
                        editedQuestion.evaluation_hints = document.getElementById('edit-evaluation-hints').value;
                        editedQuestion.explanation = document.getElementById('edit-explanation').value;
                        break;

                    case 'code':
                        editedQuestion.question_text = document.getElementById('edit-question-text').value;
                        editedQuestion.expected_code = document.getElementById('edit-expected-code').value;
                        editedQuestion.language = document.getElementById('edit-language').value;
                        editedQuestion.evaluation_criteria = document.getElementById('edit-evaluation-criteria').value;
                        editedQuestion.test_cases = document.getElementById('edit-test-cases').value;
                        editedQuestion.explanation = document.getElementById('edit-explanation').value;
                        break;
                }

                currentGeneratedQuestion = editedQuestion;
                displayQuestion(editedQuestion);
                performQualityCheck(editedQuestion);
                editModal.classList.add('hidden');
            }

            function displayQuestion(question) {
                let html = '';

                switch (question.question_type || document.querySelector('input[name="question_type"]:checked').value) {
                    case 'mcq':
                        html = `
                            <div class="bg-white p-6 rounded-lg border">
                                <h4 class="text-lg font-semibold mb-4">${question.question_text}</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center mr-3 font-bold">A</span>
                                        <span>${question.option_a}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center mr-3 font-bold">B</span>
                                        <span>${question.option_b}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center mr-3 font-bold">C</span>
                                        <span>${question.option_c}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center mr-3 font-bold">D</span>
                                        <span>${question.option_d}</span>
                                    </div>
                                </div>
                                <div class="mt-4 p-3 bg-green-50 rounded">
                                    <strong>Correct Answer:</strong> ${question.correct_answer.toUpperCase()}
                                </div>
                                ${question.explanation ? `<div class="mt-2 p-3 bg-blue-50 rounded"><strong>Explanation:</strong> ${question.explanation}</div>` : ''}
                            </div>
                        `;
                        break;

                    case 'fill_blank':
                        html = `
                            <div class="bg-white p-6 rounded-lg border">
                                <h4 class="text-lg font-semibold mb-4">${question.question_text}</h4>
                                <div class="mt-4 p-3 bg-green-50 rounded">
                                    <strong>Expected Answer:</strong> ${question.expected_answer}
                                </div>
                                ${question.evaluation_hints ? `<div class="mt-2 p-3 bg-blue-50 rounded"><strong>Evaluation Hints:</strong> ${question.evaluation_hints}</div>` : ''}
                                ${question.explanation ? `<div class="mt-2 p-3 bg-yellow-50 rounded"><strong>Explanation:</strong> ${question.explanation}</div>` : ''}
                            </div>
                        `;
                        break;

                    case 'code':
                        html = `
                            <div class="bg-white p-6 rounded-lg border">
                                <h4 class="text-lg font-semibold mb-4">${question.question_text}</h4>
                                <div class="mt-4">
                                    <strong>Language:</strong> ${question.language || 'Not specified'}
                                </div>
                                ${question.expected_code ? `
                                    <div class="mt-4">
                                        <strong>Expected Solution:</strong>
                                        <pre class="bg-gray-100 p-4 rounded mt-2 overflow-x-auto"><code>${question.expected_code}</code></pre>
                                    </div>
                                ` : ''}
                                ${question.evaluation_criteria ? `<div class="mt-2 p-3 bg-blue-50 rounded"><strong>Evaluation Criteria:</strong> ${question.evaluation_criteria}</div>` : ''}
                                ${question.test_cases ? `<div class="mt-2 p-3 bg-green-50 rounded"><strong>Test Cases:</strong> ${question.test_cases}</div>` : ''}
                                ${question.explanation ? `<div class="mt-2 p-3 bg-yellow-50 rounded"><strong>Explanation:</strong> ${question.explanation}</div>` : ''}
                            </div>
                        `;
                        break;
                }

                questionContent.innerHTML = html;
            }
        });
    </script>
</x-app-layout>