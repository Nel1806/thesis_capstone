<x-layouts.app title="School Audit">
    <div class="topbar">
        <div>
            <h1>School Audit</h1>
            <p>Review grade-level enrollment, sections, class size, and teacher shortage.</p>
        </div>
    </div>

    <form class="filters" method="GET" action="{{ route('schools') }}">
        <select name="school" aria-label="School">
            @foreach ($schoolOptions as $school)
                <option value="{{ $school['code'] }}" @selected($selectedSchool === $school['code'])>
                    {{ $school['name'] }} ({{ $school['code'] }})
                </option>
            @endforeach
        </select>
        <button class="button" type="submit">View School</button>
    </form>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="notice error">{{ $errors->first() }}</div>
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
            <span class="muted">{{ $rows->count() }} grade levels - source fields editable</span>
        </div>
        <form method="POST" action="{{ route('schools.update', $selectedSchool) }}">
            @csrf
            @method('PUT')
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
                                <td><strong>Grade {{ $row->grade_level }}</strong></td>
                                <td class="num">
                                    <input class="editable" type="number" min="0" name="rows[{{ $row->id }}][learners]" value="{{ old("rows.$row->id.learners", $row->learners) }}">
                                </td>
                                <td class="num">
                                    <input class="editable" type="number" min="1" name="rows[{{ $row->id }}][sections]" value="{{ old("rows.$row->id.sections", $row->sections) }}">
                                </td>
                                <td class="num">
                                    <input class="editable" type="number" min="0" step="0.01" name="rows[{{ $row->id }}][class_size]" value="{{ old("rows.$row->id.class_size", number_format($row->class_size, 2, '.', '')) }}">
                                </td>
                                <td class="num">
                                    <input class="editable" type="number" min="0" name="rows[{{ $row->id }}][required_teachers]" value="{{ old("rows.$row->id.required_teachers", $row->required_teachers) }}">
                                </td>
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
                <a class="button secondary" href="{{ route('schools', ['school' => $selectedSchool]) }}">Cancel</a>
                <button class="button" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</x-layouts.app>
