<x-layouts.app title="School Audit">
    <div class="topbar">
        <div>
            <h1>School Audit</h1>
            <p>{{ ($level ?? 'elementary') === 'secondary' ? 'Secondary' : 'Elementary' }} &middot; review enrollment, sections, class size, and teacher shortage.</p>
        </div>
    </div>

    <form id="school-audit-filters" class="filters" method="GET" action="{{ route($selfRoute ?? 'schools') }}">
        <label class="filter-field">
            <span>Basic Education</span>
            <select name="level" aria-label="Basic Education" onchange="this.form.submit()">
                <option value="elementary" @selected(($level ?? 'elementary') === 'elementary')>Elementary</option>
                <option value="secondary" @selected(($level ?? 'elementary') === 'secondary')>Secondary</option>
            </select>
        </label>
        <label class="filter-field">
            <span>School Year</span>
            <select name="year" aria-label="School Year" onchange="this.form.submit()">
                @foreach ($schoolYearOptions as $year)
                    <option value="{{ $year }}" @selected($selectedSchoolYear === $year)>{{ $year }}</option>
                @endforeach
            </select>
        </label>
        <label class="filter-field">
            <span>School</span>
            <select name="school" aria-label="School" onchange="this.form.submit()">
                @foreach ($schoolOptions as $school)
                    <option value="{{ $school['code'] }}" @selected($selectedSchool === $school['code'])>
                        {{ $school['name'] }} ({{ $school['code'] }})
                    </option>
                @endforeach
            </select>
        </label>
    </form>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="notice error">{{ $errors->first() }}</div>
    @endif

    @if ($rows->isEmpty())
        <div class="notice">No audit data for SY {{ $selectedSchoolYear }} yet.</div>
    @endif

    <section class="summary-strip">
        <div class="mini-stat">
            <span>Total Enrolled</span>
            <strong>{{ number_format($summary->learners ?? 0) }}</strong>
        </div>
        <div class="mini-stat">
            <span>Sections</span>
            <strong>{{ number_format($summary->sections ?? 0) }}</strong>
        </div>
        <div class="mini-stat">
            <span>Required Teachers</span>
            <strong>{{ number_format($summary->required_teachers ?? 0) }}</strong>
        </div>
        <div class="mini-stat">
            <span>Available Teachers</span>
            <strong>{{ number_format($summary->available_teachers ?? 0) }}</strong>
        </div>
        <div class="mini-stat">
            <span>Need Teachers</span>
            <strong>{{ number_format($summary->shortage ?? 0) }}</strong>
        </div>
    </section>

    <div class="card">
        <div class="card-title" style="padding:18px 18px 0">
            <h2>{{ $selectedSchoolName }} <span class="muted">({{ $selectedSchool }})</span></h2>
            <span class="muted">
                SY {{ $selectedSchoolYear }} &middot;
                @if ($rows->isNotEmpty())
                    {{ $rows->count() }} grade levels &middot; sections and teacher requirement computed from Parameters
                @else
                    no grade-level records yet
                @endif
            </span>
        </div>

        @if ($selectedSchool && $rows->isNotEmpty())
            <form method="POST" action="{{ route($updateRoute ?? 'schools.update', $selectedSchool) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="school_year" value="{{ $selectedSchoolYear }}">
                <input type="hidden" name="school_level" value="{{ $level ?? 'elementary' }}">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th class="num">Enrolled</th>
                                <th class="num">Sections</th>
                                <th class="num">Class Size</th>
                                <th class="num">Teacher Requirement</th>
                                <th class="num">Current Teachers</th>
                                <th class="num">Surplus</th>
                                <th class="num">Need Teachers</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td><strong>{{ config(($gradeConfig ?? 'audit_grades').'.'.$row->grade_level, 'Grade '.$row->grade_level) }}</strong></td>
                                    <td class="num">
                                        <input class="editable" type="number" min="0" name="rows[{{ $row->id }}][learners]" value="{{ old("rows.$row->id.learners", $row->learners) }}">
                                    </td>
                                    <td class="num computed">{{ number_format($row->sections) }}</td>
                                    <td class="num computed">{{ number_format($row->class_size, 2) }}</td>
                                    <td class="num computed">{{ number_format($row->required_teachers) }}</td>
                                    <td class="num">
                                        <input class="editable" type="number" min="0" name="rows[{{ $row->id }}][available_teachers]" value="{{ old("rows.$row->id.available_teachers", $row->available_teachers) }}">
                                    </td>
                                    <td class="num"><span class="badge ok">{{ number_format($row->surplus) }}</span></td>
                                    <td class="num"><span class="badge {{ $row->shortage > 0 ? 'danger' : '' }}">{{ number_format($row->shortage) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="8">No school audit records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px; padding:16px 18px 18px">
                    <a class="button secondary" href="{{ route($selfRoute ?? 'schools', ['school' => $selectedSchool, 'year' => $selectedSchoolYear, 'level' => $level ?? 'elementary']) }}">Cancel</a>
                    <button class="button" type="submit">Save Changes</button>
                </div>
            </form>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th class="num">Enrolled</th>
                            <th class="num">Sections</th>
                            <th class="num">Class Size</th>
                            <th class="num">Teacher Requirement</th>
                            <th class="num">Current Teachers</th>
                            <th class="num">Surplus</th>
                            <th class="num">Need Teachers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="8">No school audit records found.</td></tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.app>
