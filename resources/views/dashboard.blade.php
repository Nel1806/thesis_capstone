<x-layouts.app title="Dashboard">
    <div class="topbar">
        <div>
            <h1>Elementary Teacher Audit</h1>
            <p>SY {{ $import->school_year ?? '2025-2026' }} staffing overview from the uploaded workbook.</p>
        </div>
        <div class="pill">
            {{ $import ? 'Imported '.$import->imported_at : 'No workbook imported yet' }}
        </div>
    </div>

    <section class="grid stats">
        <div class="card pad stat">
            <div class="label">Schools</div>
            <div class="value">{{ number_format($totals->schools ?? 0) }}</div>
            <div class="hint">Elementary campuses</div>
        </div>
        <div class="card pad stat">
            <div class="label">Learners</div>
            <div class="value">{{ number_format($totals->learners ?? 0) }}</div>
            <div class="hint">Total enrollment</div>
        </div>
        <div class="card pad stat">
            <div class="label">Sections</div>
            <div class="value">{{ number_format($totals->sections ?? 0) }}</div>
            <div class="hint">Across grade levels</div>
        </div>
        <div class="card pad stat">
            <div class="label">Teacher Gap</div>
            <div class="value">{{ number_format(($totals->shortage ?? 0) - ($totals->surplus ?? 0)) }}</div>
            <div class="hint">Shortage minus surplus</div>
        </div>
    </section>

    <section class="grid two">
        <div class="card">
            <div class="card-title" style="padding:18px 18px 0">
                <h2>School Summary</h2>
                <span class="muted">{{ $schools->count() }} records</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>School</th>
                            <th class="num">Learners</th>
                            <th class="num">Sections</th>
                            <th class="num">Required</th>
                            <th class="num">Available</th>
                            <th class="num">Gap</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schools as $school)
                            @php($gap = ($school->shortage ?? 0) - ($school->surplus ?? 0))
                            <tr>
                                <td>
                                    <strong>{{ $school->school_name }}</strong>
                                    <div class="muted">{{ $school->school_code }}</div>
                                </td>
                                <td class="num">{{ number_format($school->learners) }}</td>
                                <td class="num">{{ number_format($school->sections) }}</td>
                                <td class="num">{{ number_format($school->required_teachers) }}</td>
                                <td class="num">{{ number_format($school->available_teachers) }}</td>
                                <td class="num"><span class="badge {{ $gap > 0 ? 'danger' : 'ok' }}">{{ number_format($gap) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No audit data yet. Run <strong>php artisan audit:import</strong>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card pad">
            <div class="card-title">
                <h2>Grade Level Load</h2>
                <span class="muted">Learners</span>
            </div>
            @php($maxLearners = max(1, (int) $gradeLevels->max('learners')))
            <div class="bar">
                @foreach ($gradeLevels as $grade)
                    <div class="bar-row">
                        <strong>Grade {{ $grade->grade_level }}</strong>
                        <div class="track"><div class="fill" style="width: {{ min(100, ($grade->learners / $maxLearners) * 100) }}%"></div></div>
                        <span class="num">{{ number_format($grade->learners) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
