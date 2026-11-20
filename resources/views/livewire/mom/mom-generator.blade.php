<div class="{{ $hasGenerated ? 'min-h-screen flex flex-col' : 'h-screen max-h-screen overflow-hidden flex flex-col' }}">
    <!-- TOP FLOATING BAR (Clean, Transparent, Right-aligned, Deep Navy Texts) -->
    <header class="w-full select-none pt-3.5 pb-1 px-4 sm:px-6 lg:px-8 bg-transparent shrink-0">
        <div class="max-w-5xl mx-auto flex items-center justify-end gap-6">
            <!-- HELP (Click Popover Tutor - Floating Navy Text) -->
            <div x-data="{ helpOpen: false }" @click.outside="helpOpen = false" @keydown.escape.window="helpOpen = false" class="relative">
                <button
                    type="button"
                    @click="helpOpen = !helpOpen"
                    class="inline-flex items-center gap-2 py-1 text-sm sm:text-base font-bold text-[#002878] hover:text-[#00236f] transition-colors cursor-pointer select-none"
                    :aria-expanded="helpOpen"
                    aria-haspopup="true"
                >
                    <svg class="w-4.5 h-4.5 text-[#002878]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Help</span>
                    <svg class="w-3.5 h-3.5 text-[#002878]/60 transition-transform duration-200" :class="helpOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Hover Tutorial Popover Card -->
                <div
                    x-show="helpOpen"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                    class="absolute right-0 mt-2 p-6 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 text-left"
                    style="display: none; width: 440px; max-width: calc(100vw - 2rem);"
                >
                    <div class="flex items-center gap-3 pb-3 mb-4 border-b border-slate-100">
                        <div class="w-6 h-6 rounded-md bg-[#002878] text-white flex items-center justify-center text-xs font-bold shrink-0">i</div>
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-[#002878]">
                                {{ $lang === 'id' ? 'Panduan Penggunaan' : 'Quick Guide' }}
                            </h3>
                            <p class="text-xs sm:text-sm text-[#002878]/75 font-medium">
                                {{ $lang === 'id' ? '3 langkah mudah membuat MoM resmi' : '3 simple steps to generate official MoM' }}
                            </p>
                        </div>
                    </div>

                    <!-- 3 Steps -->
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-[#002878] text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                            <div>
                                <p class="text-sm font-bold text-[#002878]">
                                    {{ $lang === 'id' ? 'Input Catatan / Audio' : 'Input Notes or Audio' }}
                                </p>
                                <p class="text-xs sm:text-sm text-[#002878]/80 mt-1 leading-relaxed font-normal">
                                    {{ $lang === 'id' ? 'Tempel catatan meeting apa adanya atau unggah rekaman audio Voice Memo HP (.m4a/.mp3).' : 'Paste meeting notes as-is or upload a Voice Memo audio recording (.m4a/.mp3).' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-[#002878] text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                            <div>
                                <p class="text-sm font-bold text-[#002878]">
                                    {{ $lang === 'id' ? 'AI Merapikan Otomatis' : 'AI Structuring' }}
                                </p>
                                <p class="text-xs sm:text-sm text-[#002878]/80 mt-1 leading-relaxed font-normal">
                                    {{ $lang === 'id' ? 'Gemini AI merapikan tata bahasa bisnis, memilah daftar hadir, agenda diskusi, dan action items dengan PIC & deadline.' : 'Gemini AI structures business grammar, extracts attendees, agenda topics, and action tasks with PIC & deadlines.' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-[#002878] text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                            <div>
                                <p class="text-sm font-bold text-[#002878]">
                                    {{ $lang === 'id' ? 'Download Excel Resmi (.xlsx)' : 'Download Official Excel' }}
                                </p>
                                <p class="text-xs sm:text-sm text-[#002878]/80 mt-1 leading-relaxed font-normal">
                                    {{ $lang === 'id' ? 'Koreksi rincian jika perlu, lalu download file Excel resmi YIID MoM yang siap diarsip atau ditandatangani.' : 'Review details, then export directly to the official formatted Yusen Logistics Excel file.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EARTH WIREFRAME LANGUAGE TOGGLE (Floating Navy Text + Icon) -->
            <button
                type="button"
                wire:click="toggleLang"
                class="inline-flex items-center gap-2 py-1 text-sm sm:text-base font-bold text-[#002878] hover:text-[#00236f] transition-colors cursor-pointer select-none"
                title="{{ $lang === 'id' ? 'Switch to English' : 'Ganti ke Bahasa Indonesia' }}"
            >
                <!-- Earth Wireframe Grid Lines SVG Icon -->
                <svg class="w-5 h-5 text-[#002878]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
                <span class="tracking-wide uppercase font-bold text-sm sm:text-base text-[#002878]">{{ $lang === 'id' ? 'ID' : 'EN' }}</span>
            </button>

            @auth
                <!-- User Profile & Logout -->
                <div class="inline-flex items-center gap-2 pl-2 sm:pl-3 border-l border-[#002878]/20">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-bold text-[#002878] leading-tight">
                            {{ auth()->user()->name }}
                        </span>
                        <span class="text-[10px] text-[#002878]/60 font-semibold uppercase">
                            {{ auth()->user()->department ?: 'YIID' }}
                        </span>
                    </div>
                    <button
                        type="button"
                        wire:click="logout"
                        class="inline-flex items-center gap-1.5 py-1 px-2 rounded-lg text-xs font-bold text-[#002878] hover:text-rose-600 hover:bg-rose-50/70 transition-colors cursor-pointer select-none"
                        title="{{ $lang === 'id' ? 'Keluar' : 'Sign out' }}"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="hidden md:inline">{{ $lang === 'id' ? 'Keluar' : 'Logout' }}</span>
                    </button>
                </div>
            @endauth
        </div>
    </header>

    <main class="{{ $hasGenerated ? 'max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 flex-1' : 'max-w-3xl w-full mx-auto px-4 sm:px-6 pt-13 sm:pt-16 pb-2 space-y-6 sm:space-y-7' }}">
        @if (! $hasGenerated)
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 pb-1">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#002878] tracking-tight">
                        Minutes of Meeting Generator
                    </h1>
                    <p class="text-xs sm:text-sm text-[#002878]/80 font-medium mt-0.5">
                        {{ $lang === 'id' 
                            ? 'Rapikan catatan atau rekaman meeting dalam hitungan detik' 
                            : 'Structure meeting notes and audio recordings in seconds' 
                        }}
                    </p>
                </div>
            </div>
        @endif

        @if ($errorMessage)
            <div class="p-3.5 sm:p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm flex items-start gap-2.5 shadow-2xs">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1 font-semibold">{{ $errorMessage }}</div>
            </div>
        @endif

        @if (! $hasGenerated)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="inline-flex shrink-0 p-1 bg-slate-100 rounded-lg text-xs font-semibold">
                        <button
                            type="button"
                            wire:click="setMode('text')"
                            class="whitespace-nowrap px-5 py-1.5 rounded-md transition-all cursor-pointer {{ $inputMode === 'text' ? 'bg-[#002878] text-white font-bold shadow-2xs' : 'text-[#002878]/70 hover:text-[#002878]' }}"
                        >
                            {{ $lang === 'id' ? 'Catatan Teks' : 'Text Notes' }}
                        </button>
                        <button
                            type="button"
                            wire:click="setMode('audio')"
                            class="whitespace-nowrap px-5 py-1.5 rounded-md transition-all cursor-pointer {{ $inputMode === 'audio' ? 'bg-[#002878] text-white font-bold shadow-2xs' : 'text-[#002878]/70 hover:text-[#002878]' }}"
                        >
                            {{ $lang === 'id' ? 'Rekaman Suara' : 'Voice Memo' }}
                        </button>
                    </div>

                    <!-- Department & Format Template Selector -->
                    <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto justify-between sm:justify-end sm:mr-2">
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-bold text-[#002878] uppercase tracking-wider shrink-0">
                                {{ $lang === 'id' ? 'Departemen:' : 'Department:' }}
                            </label>
                            <div class="w-40 sm:w-44 shrink-0">
                                <select
                                    wire:model.live="department"
                                    class="w-full text-xs font-bold rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] py-1.5 pl-3 pr-7 text-[#002878] bg-slate-50/70 transition cursor-pointer"
                                >
                                    @foreach ($this->departments as $deptKey => $deptInfo)
                                        <option value="{{ $deptKey }}">
                                            {{ $deptInfo['label'] ?? $deptInfo['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if (count($this->availableTemplates) > 1)
                            <div class="flex items-center gap-1.5">
                                <label class="text-xs font-bold text-[#002878] uppercase tracking-wider shrink-0">
                                    {{ $lang === 'id' ? 'Format:' : 'Format:' }}
                                </label>
                                <div class="w-44 sm:w-48 shrink-0">
                                    <select
                                        wire:model.live="templateVariant"
                                        class="w-full text-xs font-bold rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] py-1.5 pl-3 pr-7 text-[#002878] bg-slate-50/70 transition cursor-pointer"
                                    >
                                        @foreach ($this->availableTemplates as $vKey => $vInfo)
                                            <option value="{{ $vKey }}">
                                                {{ $vInfo['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tab Body -->
                <div class="space-y-3">
                    @if ($inputMode === 'text')
                        <div>
                            <div class="mb-1.5">
                                <label class="block text-xs font-bold text-[#002878] uppercase tracking-wider">
                                    {{ $lang === 'id' ? 'Catatan Rapat / Transkrip Meeting' : 'Meeting Notes / Transcript' }}
                                </label>
                            </div>
                            <textarea
                                wire:model="rawText"
                                rows="4"
                                placeholder="{{ $lang === 'id' ? 'Catatan rapat...' : 'Meeting notes...' }}"
                                class="w-full text-xs sm:text-sm rounded-xl border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-3 text-[#002878] placeholder-[#002878]/40 bg-slate-50/50 transition font-normal resize-none"
                            ></textarea>
                            @error('rawText')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-bold text-[#002878] uppercase tracking-wider mb-1.5">
                                {{ $lang === 'id' ? 'Unggah Berkas Audio Rekaman Meeting (.mp3, .m4a, .wav)' : 'Upload Meeting Audio Recording (.mp3, .m4a, .wav)' }}
                            </label>
                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 sm:p-7 text-center bg-slate-50/40 hover:bg-slate-50 transition cursor-pointer relative">
                                <input
                                    type="file"
                                    wire:model="audioFile"
                                    accept="audio/*,.mp3,.m4a,.wav,.ogg,.aac"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                />
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-[#002878] flex items-center justify-center mb-2">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                        </svg>
                                    </div>
                                    <p class="text-xs sm:text-sm font-bold text-[#002878]">
                                        @if ($audioFile)
                                            {{ $lang === 'id' ? 'File terpilih:' : 'Selected file:' }} <span class="text-[#002878] underline">{{ $audioFile->getClientOriginalName() }}</span>
                                        @else
                                            {{ $lang === 'id' ? 'Klik atau seret rekaman audio ke sini' : 'Click or drag & drop audio recording here' }}
                                        @endif
                                    </p>
                                    <p class="text-[11px] text-[#002878]/70 font-medium mt-0.5">
                                        {{ $lang === 'id' ? 'Mendukung Voice Memo iPhone (.m4a), MP3, WAV (Maksimal 50 MB)' : 'Supports iPhone Voice Memo (.m4a), MP3, WAV (Max 50 MB)' }}
                                    </p>
                                </div>
                            </div>
                            @error('audioFile')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="flex justify-end pt-1">
                        <button
                            type="button"
                            wire:click="processMoM"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#002878] hover:bg-[#001a52] text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95 disabled:opacity-50 cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="processMoM">
                                {{ $lang === 'id' ? 'Rapikan & Buat MoM' : 'Generate MoM' }}
                            </span>
                            <span wire:loading wire:target="processMoM">
                                {{ $lang === 'id' ? 'Sedang diproses...' : 'Processing...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="space-y-6">
                <div class="space-y-4 pb-1">
                    <div class="flex items-center">
                        <button
                            type="button"
                            wire:click="resetForm"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200/90 text-sm font-bold text-[#002878] shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer active:scale-95 group"
                        >
                            <svg class="w-4 h-4 text-[#002878] transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>{{ $lang === 'id' ? 'Kembali' : 'Back' }}</span>
                        </button>
                    </div>

                    @if ($successMessage)
                        <div class="p-3.5 sm:p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-start gap-3 shadow-2xs">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1 font-normal leading-relaxed">{{ $successMessage }}</div>
                        </div>
                    @endif

                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#002878] tracking-tight">
                            {{ $lang === 'id' ? 'Preview Notulensi' : 'Preview MoM' }}
                        </h1>
                        <p class="text-xs sm:text-sm text-[#002878]/80 font-medium mt-0.5">
                            {{ $lang === 'id' ? 'Periksa dan edit langsung rincian notulensi sebelum diunduh' : 'Review and edit meeting minutes details before downloading' }}
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-[#002878]">
                            {{ $lang === 'id' ? 'Informasi Rapat (Header)' : 'Meeting Information (Header)' }}
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#002878] mb-1">
                                {{ $lang === 'id' ? 'Tanggal Rapat' : 'Meeting Date' }}
                            </label>
                            <input
                                type="date"
                                wire:model="meetingInfo.date"
                                class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-2.5 text-[#002878] font-medium"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#002878] mb-1">
                                {{ $lang === 'id' ? 'Waktu / Jam' : 'Time' }}
                            </label>
                            <input
                                type="text"
                                wire:model="meetingInfo.time"
                                placeholder="14:00:00 PM"
                                class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-2.5 text-[#002878] font-medium"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#002878] mb-1">
                                {{ $lang === 'id' ? 'Lokasi / Platform' : 'Location / Platform' }}
                            </label>
                            <input
                                type="text"
                                wire:model="meetingInfo.location"
                                placeholder="Meeting Room 1 / MS Teams"
                                class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-2.5 text-[#002878] font-medium"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#002878] mb-1">
                                {{ $lang === 'id' ? 'Pemimpin Rapat (Chairman)' : 'Chairman of the Meeting' }}
                            </label>
                            <input
                                type="text"
                                wire:model="meetingInfo.chairman"
                                placeholder="{{ $lang === 'id' ? 'Nama Pemimpin Rapat' : 'Chairman Name' }}"
                                class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-2.5 text-[#002878] font-medium"
                            />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-[#002878]">
                            {{ $lang === 'id' ? 'Daftar Hadir (Attendees)' : 'Attendees List' }}
                        </h3>
                        <button
                            type="button"
                            wire:click="addAttendee"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 text-[#002878] hover:text-[#002878] hover:bg-slate-50 text-xs font-bold transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>{{ $lang === 'id' ? 'Tambah Peserta' : 'Add Attendee' }}</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[#002878] font-bold border-b border-slate-200">
                                    <th class="p-2.5 font-bold w-10 text-center">No</th>
                                    <th class="p-2.5 font-bold">{{ $lang === 'id' ? 'Nama Lengkap' : 'Full Name' }}</th>
                                    <th class="p-2.5 font-bold">{{ $lang === 'id' ? 'Perusahaan (Company)' : 'Company' }}</th>
                                    <th class="p-2.5 font-bold">{{ $lang === 'id' ? 'Divisi / Departemen' : 'Division' }}</th>
                                    <th class="p-2.5 font-bold w-12 text-center">{{ $lang === 'id' ? 'Aksi' : 'Action' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($attendees as $idx => $att)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="p-2.5 text-center text-[#002878]/65 font-bold">{{ $idx + 1 }}</td>
                                        <td class="p-2">
                                            <input
                                                type="text"
                                                wire:model="attendees.{{ $idx }}.name"
                                                class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-2 text-[#002878] font-medium"
                                                placeholder="{{ $lang === 'id' ? 'Nama Peserta' : 'Attendee Name' }}"
                                            />
                                        </td>
                                        <td class="p-2">
                                            <input
                                                type="text"
                                                wire:model="attendees.{{ $idx }}.company"
                                                class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-2 text-[#002878] font-medium"
                                                placeholder="PT Yusen Logistics Interlink Indonesia"
                                            />
                                        </td>
                                        <td class="p-2">
                                            <input
                                                type="text"
                                                wire:model="attendees.{{ $idx }}.division"
                                                class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-2 text-[#002878] font-medium"
                                                placeholder="{{ $lang === 'id' ? 'Divisi' : 'Division' }}"
                                            />
                                        </td>
                                        <td class="p-2 text-center">
                                            <button
                                                type="button"
                                                wire:click="removeAttendee({{ $idx }})"
                                                class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                                title="Hapus"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-4 text-center text-[#002878]/65 text-xs italic">
                                            {{ $lang === 'id' ? 'Belum ada peserta tercatat.' : 'No attendees recorded yet.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-[#002878]">
                            {{ $lang === 'id' ? 'Agenda & Poin Pembahasan' : 'Agenda & Discussion Items' }}
                        </h3>
                        <button
                            type="button"
                            wire:click="addAgendaItem"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 text-[#002878] hover:text-[#002878] hover:bg-slate-50 text-xs font-bold transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>{{ $lang === 'id' ? 'Tambah Topik Agenda' : 'Add Agenda Item' }}</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        @forelse ($agendaItems as $aIdx => $agenda)
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2.5 flex-1">
                                        <span class="px-2.5 py-1 rounded-md bg-[#002878] text-white text-xs font-extrabold">
                                            #{{ $agenda['no'] ?? ($aIdx + 1) }}
                                        </span>
                                        <input
                                            type="text"
                                            wire:model="agendaItems.{{ $aIdx }}.topic"
                                            class="flex-1 text-xs sm:text-sm font-bold rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-2 text-[#002878] bg-white"
                                            placeholder="{{ $lang === 'id' ? 'Judul / Topik Pembahasan' : 'Agenda Topic Title' }}"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="removeAgendaItem({{ $aIdx }})"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-[#002878] mb-1">
                                        {{ $this->currentFormat === 'docx' ? 'Discussion / Progress' : ($lang === 'id' ? 'Hasil Diskusi / Rincian Pembahasan' : 'Discussion Points / Minutes') }}
                                    </label>
                                    <textarea
                                        wire:model="agendaItems.{{ $aIdx }}.discussion"
                                        rows="3"
                                        class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-3 text-[#002878] bg-white font-normal"
                                        placeholder="{{ $this->currentFormat === 'docx' ? 'Rincian pembahasan dan progres...' : ($lang === 'id' ? 'Rincian pembahasan rapat, keputusan, atau catatan tindak lanjut...' : 'Detailed discussion points, decisions, or follow-up notes...') }}"
                                    ></textarea>
                                </div>

                                @if ($this->currentFormat === 'docx')
                                    <div>
                                        <label class="block text-[11px] font-bold text-[#002878] mb-1">
                                            Next Action
                                        </label>
                                        <textarea
                                            wire:model="agendaItems.{{ $aIdx }}.next_action"
                                            rows="2"
                                            class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-2.5 text-[#002878] bg-white font-normal"
                                            placeholder="{{ $lang === 'id' ? 'Langkah tindak lanjut berikutnya & PIC yang ditugaskan...' : 'Next action steps & assigned PIC...' }}"
                                        ></textarea>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-center text-[#002878]/65 text-xs italic py-4">
                                {{ $lang === 'id' ? 'Belum ada agenda rapat yang ditambahkan.' : 'No agenda items added yet.' }}
                            </p>
                        @endforelse

                        @if ($this->currentFormat === 'docx')
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 space-y-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2.5 py-1 rounded-md bg-[#002878] text-white text-xs font-extrabold">
                                        #{{ count($agendaItems) + 1 }}
                                    </span>
                                    <span class="flex-1 text-xs sm:text-sm font-bold text-[#002878] px-1 py-1">
                                        {{ $lang === 'id' ? 'Kesimpulan Rapat (Conclusion)' : 'Meeting Conclusion' }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-[#002878] mb-1">
                                        {{ $lang === 'id' ? 'Hasil Kesimpulan / Rangkuman Penutup' : 'Meeting Conclusion / Closing Summary' }}
                                    </label>
                                    <textarea
                                        wire:model="itDetails.conclusion"
                                        rows="4"
                                        class="w-full text-xs rounded-lg border-slate-200 focus:border-[#002878] focus:ring-[#002878] p-3 text-[#002878] bg-white font-normal"
                                        placeholder="{{ $lang === 'id' ? 'Paragraf kesimpulan akhir rapat...' : 'Summary conclusion of the meeting...' }}"
                                    ></textarea>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end pt-1 pb-4">
                    <button
                        wire:click="downloadMoM"
                        type="button"
                        class="inline-flex items-center gap-2.5 px-7 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm sm:text-base font-bold shadow-md hover:shadow-lg transition-all active:scale-95 cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download MoM</span>
                    </button>
                </div>
            </div>
        @endif
    </main>

    <footer class="mt-auto pb-3.5 text-center text-[11px] text-[#002878]/75/80 select-none shrink-0">
        <p class="font-medium tracking-wide">PT Yusen Logistics Interlink Indonesia &bull; Minutes of Meeting Automation System</p>
    </footer>
</div>
